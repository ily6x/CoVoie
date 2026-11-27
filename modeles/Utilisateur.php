<?php
/**
 * ------------------------------------------------------------------
 *  CoVoie - Modèle Utilisateur  (table : utilisateurs)
 * ------------------------------------------------------------------
 *  Un étudiant inscrit sur la plateforme.
 *  Il n'y a PAS de classe Conducteur ni Passager : le même compte est
 *  conducteur sur les trajets qu'il publie et passager sur ceux qu'il
 *  réserve. Le rôle 'admin' sert seulement à repérer l'administrateur.
 * ------------------------------------------------------------------
 */
class Utilisateur
{
    // Les attributs portent le même nom que les colonnes de la table :
    // PDO peut ainsi remplir l'objet tout seul (PDO::FETCH_CLASS).
    public int $id;
    public string $nom;
    public string $email;
    public string $mot_de_passe;      // empreinte password_hash(), jamais le mot de passe en clair
    public string $role;              // 'membre' ou 'admin'
    public string $date_inscription;

    /**
     * Cherche un utilisateur par son identifiant.
     * @param int $id
     * @return Utilisateur|null null si l'utilisateur n'existe pas
     */
    public static function trouver(int $id): ?Utilisateur
    {
        $resultat = requete('SELECT * FROM utilisateurs WHERE id = ?', [$id]);
        $utilisateur = $resultat->fetchObject('Utilisateur');

        if ($utilisateur === false) {
            return null;
        }
        return $utilisateur;
    }

    /**
     * Vérifie l'email et le mot de passe saisis dans le formulaire de connexion.
     * @return Utilisateur|null L'utilisateur si tout est bon, sinon null
     */
    public static function verifierConnexion(string $email, string $motDePasse): ?Utilisateur
    {
        $resultat = requete('SELECT * FROM utilisateurs WHERE email = ?', [strtolower($email)]);
        $utilisateur = $resultat->fetchObject('Utilisateur');

        // password_verify compare le mot de passe saisi avec l'empreinte enregistrée
        if ($utilisateur !== false && password_verify($motDePasse, $utilisateur->mot_de_passe)) {
            return $utilisateur;
        }
        return null;
    }

    /**
     * Crée un nouveau compte après avoir vérifié les champs.
     * @return array Les erreurs rangées par nom de champ (tableau vide = compte créé)
     */
    public static function inscrire(string $nom, string $email, string $motDePasse, string $confirmation): array
    {
        $erreurs = [];
        $email = strtolower($email);

        // Contrôles de saisie
        if (mb_strlen($nom) < 2 || mb_strlen($nom) > 100) {
            $erreurs['nom'] = 'Le nom doit contenir entre 2 et 100 caractères.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 180) {
            $erreurs['email'] = 'L\'adresse email n\'est pas valide.';
        }
        if (strlen($motDePasse) < 8 || strlen($motDePasse) > 72) {
            $erreurs['mot_de_passe'] = 'Le mot de passe doit contenir entre 8 et 72 caractères.';
        }
        if ($motDePasse !== $confirmation) {
            $erreurs['confirmation'] = 'Les deux mots de passe ne sont pas identiques.';
        }

        // RÈGLE MÉTIER : un seul compte par adresse email
        $dejaPris = requete('SELECT COUNT(*) FROM utilisateurs WHERE email = ?', [$email])->fetchColumn();
        if ($dejaPris > 0) {
            $erreurs['email'] = 'Cette adresse email est déjà utilisée.';
        }

        if (count($erreurs) > 0) {
            return $erreurs;
        }

        // Le mot de passe n'est JAMAIS enregistré en clair : on stocke son empreinte
        requete(
            'INSERT INTO utilisateurs (nom, email, mot_de_passe, role, date_inscription) VALUES (?, ?, ?, ?, ?)',
            [$nom, $email, password_hash($motDePasse, PASSWORD_DEFAULT), 'membre', date('Y-m-d H:i:s')]
        );

        return [];
    }

    /**
     * Nombre de trajets terminés auxquels l'utilisateur a participé
     * (comme conducteur, ou comme passager accepté).
     * @return int
     */
    public function nombreTrajetsRealises(): int
    {
        $sql = "SELECT COUNT(*) FROM trajets t
                WHERE t.statut = 'Termine'
                AND (t.id_conducteur = ?
                     OR t.id IN (SELECT id_trajet FROM reservations
                                 WHERE id_passager = ? AND statut = 'Confirmee'))";

        return (int) requete($sql, [$this->id, $this->id])->fetchColumn();
    }

    /**
     * Liste de tous les comptes avec leur note moyenne (back-office).
     * @return array
     */
    public static function listerTous(): array
    {
        $sql = "SELECT u.id, u.nom, u.email, u.role, u.date_inscription,
                       (SELECT AVG(note) FROM avis WHERE id_cible = u.id) AS note,
                       (SELECT COUNT(*) FROM trajets WHERE id_conducteur = u.id) AS nb_trajets
                FROM utilisateurs u
                ORDER BY u.nom";

        return requete($sql)->fetchAll();
    }

    /**
     * Les membres les mieux notés (au moins 2 avis), pour l'accueil.
     * @param int $limite Nombre de membres à renvoyer
     * @return array
     */
    public static function meilleursMembres(int $limite): array
    {
        $sql = "SELECT u.id, u.nom, AVG(a.note) AS moyenne, COUNT(a.id) AS nb_avis
                FROM utilisateurs u
                JOIN avis a ON a.id_cible = u.id
                GROUP BY u.id, u.nom
                HAVING COUNT(a.id) >= 2
                ORDER BY moyenne DESC, nb_avis DESC
                LIMIT " . (int) $limite;

        return requete($sql)->fetchAll();
    }

    /**
     * Supprime un compte (action de l'administrateur).
     *
     * RÈGLE MÉTIER : les places que ce membre occupait comme passager sur
     * des trajets à venir sont rendues aux conducteurs AVANT la suppression.
     * Ensuite, la suppression en cascade (ON DELETE CASCADE) efface ses
     * trajets, ses réservations et ses avis.
     *
     * @param int $id      Le compte à supprimer
     * @param int $idAdmin L'administrateur qui supprime
     * @return string Message d'erreur, ou '' si tout s'est bien passé
     */
    public static function supprimer(int $id, int $idAdmin): string
    {
        if ($id === $idAdmin) {
            return 'Vous ne pouvez pas supprimer votre propre compte.';
        }
        if (self::trouver($id) === null) {
            return 'Ce compte n\'existe pas.';
        }

        // TRANSACTION : soit les deux étapes réussissent, soit aucune.
        $pdo = connexionBdd();
        $pdo->beginTransaction();
        try {
            // 1. On rend une place sur chaque trajet à venir où il était passager accepté
            $sql = "UPDATE trajets
                    SET places_restantes = places_restantes + 1, statut = 'Ouvert'
                    WHERE statut <> 'Termine'
                    AND id IN (SELECT id_trajet FROM reservations
                               WHERE id_passager = ? AND statut = 'Confirmee')";
            requete($sql, [$id]);

            // 2. On supprime le compte (cascade sur trajets, réservations, avis)
            requete('DELETE FROM utilisateurs WHERE id = ?', [$id]);

            $pdo->commit();
        } catch (PDOException $erreur) {
            $pdo->rollBack();
            return 'La suppression a échoué, rien n\'a été modifié.';
        }

        return '';
    }
}
