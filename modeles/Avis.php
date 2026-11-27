<?php
/**
 * ------------------------------------------------------------------
 *  CoVoie - Modèle Avis  (table : avis)                  MODULE C
 * ------------------------------------------------------------------
 *  Une note de 1 à 5 (et un commentaire facultatif) laissée après un
 *  trajet terminé. Un avis relie trois choses :
 *      - l'AUTEUR  (celui qui note)
 *      - la CIBLE  (celui qui est noté)
 *      - le TRAJET (sur lequel ils ont voyagé ensemble)
 *
 *  La note moyenne n'est PAS enregistrée dans la base : elle est
 *  recalculée à chaque affichage avec AVG(). Si l'admin supprime un
 *  avis, la moyenne se met donc à jour toute seule.
 * ------------------------------------------------------------------
 */
class Avis
{
    public int $id;
    public int $note;                 // de 1 à 5
    public ?string $commentaire;      // facultatif (peut être NULL)
    public string $date_avis;
    public int $id_trajet;
    public int $id_auteur;
    public int $id_cible;

    /**
     * Note moyenne reçue par un utilisateur  (méthode demandée par le cahier des charges).
     * @param int $idUtilisateur
     * @return float|null La moyenne, ou null s'il n'a encore reçu aucun avis
     *                    (on affiche alors "Pas encore noté" et pas "0").
     */
    public static function noteMoyenne(int $idUtilisateur): ?float
    {
        $moyenne = requete('SELECT AVG(note) FROM avis WHERE id_cible = ?', [$idUtilisateur])->fetchColumn();

        // AVG() renvoie NULL quand il n'y a aucune ligne
        if ($moyenne === null) {
            return null;
        }
        return (float) $moyenne;
    }

    /**
     * Nombre d'avis reçus par un utilisateur.
     */
    public static function nombreAvis(int $idUtilisateur): int
    {
        return (int) requete('SELECT COUNT(*) FROM avis WHERE id_cible = ?', [$idUtilisateur])->fetchColumn();
    }

    /**
     * Note moyenne de TOUTE la plateforme (accueil et back-office).
     */
    public static function moyenneGenerale(): ?float
    {
        $moyenne = requete('SELECT AVG(note) FROM avis')->fetchColumn();
        if ($moyenne === null) {
            return null;
        }
        return (float) $moyenne;
    }

    /**
     * Les personnes que l'auteur peut noter pour ce trajet.
     *
     * RÈGLES MÉTIER :
     *  - le trajet doit être "Termine" ;
     *  - le CONDUCTEUR note ses passagers acceptés ;
     *  - un PASSAGER accepté note le conducteur ;
     *  - quelqu'un qui n'a pas participé ne peut noter personne.
     *
     * Chaque personne renvoyée a une clé 'deja_note' (0 ou 1).
     *
     * @return array|null La liste, ou null si l'auteur n'a pas participé
     */
    public static function participantsANoter(Trajet $trajet, int $idAuteur): ?array
    {
        if ($trajet->statut !== 'Termine') {
            return null;
        }

        // Cas 1 : l'auteur est le conducteur -> il note ses passagers confirmés
        if ($trajet->id_conducteur === $idAuteur) {
            $sql = "SELECT u.id, u.nom,
                           (SELECT COUNT(*) FROM avis a WHERE a.id_trajet = ? AND a.id_auteur = ? AND a.id_cible = u.id) AS deja_note
                    FROM reservations r
                    JOIN utilisateurs u ON u.id = r.id_passager
                    WHERE r.id_trajet = ? AND r.statut = 'Confirmee'
                    ORDER BY u.nom";
            return requete($sql, [$trajet->id, $idAuteur, $trajet->id])->fetchAll();
        }

        // Cas 2 : l'auteur est un passager confirmé -> il note le conducteur
        if (Reservation::statutDemande($trajet->id, $idAuteur) === 'Confirmee') {
            $sql = "SELECT u.id, u.nom,
                           (SELECT COUNT(*) FROM avis a WHERE a.id_trajet = ? AND a.id_auteur = ? AND a.id_cible = u.id) AS deja_note
                    FROM utilisateurs u
                    WHERE u.id = ?";
            return requete($sql, [$trajet->id, $idAuteur, $trajet->id_conducteur])->fetchAll();
        }

        // Cas 3 : il n'a pas participé à ce trajet
        return null;
    }

    /**
     * Enregistre un avis après avoir vérifié les règles.
     * @return string Message d'erreur, ou '' si l'avis est enregistré
     */
    public static function enregistrer(Trajet $trajet, int $idAuteur, int $idCible, int $note, string $commentaire): string
    {
        if ($note < 1 || $note > 5) {
            return 'La note doit être comprise entre 1 et 5.';
        }
        if (mb_strlen($commentaire) > 500) {
            return 'Le commentaire ne doit pas dépasser 500 caractères.';
        }

        // La cible doit faire partie des personnes que l'auteur a le droit de noter
        $participants = self::participantsANoter($trajet, $idAuteur);
        if ($participants === null) {
            return 'Vous n\'avez pas participé à ce trajet.';
        }

        $cibleAutorisee = false;
        foreach ($participants as $participant) {
            if ($participant['id'] == $idCible) {
                $cibleAutorisee = true;
                // RÈGLE MÉTIER : une seule note par trajet et par personne notée
                // (également garantie par la clé UNIQUE un_seul_avis de la base)
                if ($participant['deja_note'] > 0) {
                    return 'Vous avez déjà noté cette personne pour ce trajet.';
                }
            }
        }
        if (!$cibleAutorisee) {
            return 'Vous ne pouvez pas noter cette personne pour ce trajet.';
        }

        // Commentaire vide -> NULL dans la base
        if ($commentaire === '') {
            $commentaire = null;
        }

        $sql = 'INSERT INTO avis (note, commentaire, date_avis, id_trajet, id_auteur, id_cible) VALUES (?, ?, ?, ?, ?, ?)';
        requete($sql, [$note, $commentaire, date('Y-m-d H:i:s'), $trajet->id, $idAuteur, $idCible]);

        return '';
    }

    /**
     * Les trajets terminés où l'utilisateur a encore quelqu'un à noter.
     * (encart "À noter" de la page "Mes avis")
     */
    public static function trajetsANoter(int $idUtilisateur): array
    {
        $sql = "SELECT t.id, t.origine, t.destination, t.date_heure
                FROM trajets t
                WHERE t.statut = 'Termine'
                AND (
                    -- conducteur : il reste un passager confirmé pas encore noté
                    (t.id_conducteur = ?
                     AND EXISTS (SELECT 1 FROM reservations r
                                 WHERE r.id_trajet = t.id AND r.statut = 'Confirmee'
                                 AND NOT EXISTS (SELECT 1 FROM avis a WHERE a.id_trajet = t.id
                                                 AND a.id_auteur = ? AND a.id_cible = r.id_passager)))
                    OR
                    -- passager confirmé : il n'a pas encore noté le conducteur
                    (EXISTS (SELECT 1 FROM reservations r
                             WHERE r.id_trajet = t.id AND r.id_passager = ? AND r.statut = 'Confirmee')
                     AND NOT EXISTS (SELECT 1 FROM avis a WHERE a.id_trajet = t.id
                                     AND a.id_auteur = ? AND a.id_cible = t.id_conducteur))
                )
                ORDER BY t.date_heure DESC";

        $id = $idUtilisateur;
        return requete($sql, [$id, $id, $id, $id])->fetchAll();
    }

    /**
     * Les avis REÇUS par un utilisateur (profil, "Mes avis").
     */
    public static function listerRecus(int $idCible): array
    {
        $sql = "SELECT a.*, u.nom AS nom_auteur, t.origine, t.destination
                FROM avis a
                JOIN utilisateurs u ON u.id = a.id_auteur
                JOIN trajets t ON t.id = a.id_trajet
                WHERE a.id_cible = ?
                ORDER BY a.date_avis DESC";

        return requete($sql, [$idCible])->fetchAll();
    }

    /**
     * Les avis ÉCRITS par un utilisateur ("Mes avis").
     */
    public static function listerEcrits(int $idAuteur): array
    {
        $sql = "SELECT a.*, u.nom AS nom_cible, t.origine, t.destination
                FROM avis a
                JOIN utilisateurs u ON u.id = a.id_cible
                JOIN trajets t ON t.id = a.id_trajet
                WHERE a.id_auteur = ?
                ORDER BY a.date_avis DESC";

        return requete($sql, [$idAuteur])->fetchAll();
    }

    /**
     * Tous les avis de la plateforme (modération par l'administrateur).
     */
    public static function listerTous(): array
    {
        $sql = "SELECT a.*, auteur.nom AS nom_auteur, cible.nom AS nom_cible
                FROM avis a
                JOIN utilisateurs auteur ON auteur.id = a.id_auteur
                JOIN utilisateurs cible ON cible.id = a.id_cible
                ORDER BY a.date_avis DESC";

        return requete($sql)->fetchAll();
    }

    /**
     * Supprime un avis (modération). La moyenne de la cible change toute seule.
     * @return bool true si un avis a bien été supprimé
     */
    public static function supprimer(int $id): bool
    {
        return requete('DELETE FROM avis WHERE id = ?', [$id])->rowCount() === 1;
    }
}
