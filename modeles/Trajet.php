<?php
/**
 * ------------------------------------------------------------------
 *  CoVoie - Modèle Trajet  (table : trajets)          MODULE A
 * ------------------------------------------------------------------
 *  Un trajet publié par un conducteur.
 *
 *  Cycle de vie (colonne statut) :
 *      Ouvert  --(plus de place)-->  Complet  --(clôture)-->  Termine
 *
 *  - places_restantes baisse de 1 à chaque réservation ACCEPTÉE
 *    (voir reserverPlace()).
 *  - Un trajet "Termine" ne peut plus être modifié ni réservé, mais ses
 *    participants peuvent se noter (module C).
 *
 *  Convention : les méthodes "lister..." renvoient des TABLEAUX
 *  (pour les listes affichées), trouver() renvoie un OBJET Trajet
 *  (pour travailler sur un trajet précis).
 * ------------------------------------------------------------------
 */
class Trajet
{
    public int $id;
    public string $origine;
    public string $destination;
    public string $date_heure;
    public int $places_totales;
    public int $places_restantes;
    public string $statut;            // 'Ouvert', 'Complet' ou 'Termine'
    public float $prix;
    public int $id_conducteur;

    // Début de requête commun à toutes les listes : le trajet + le nom et
    // la note moyenne de son conducteur.
    const SELECT_LISTE = "SELECT t.*, u.nom AS nom_conducteur,
                            (SELECT AVG(note) FROM avis WHERE id_cible = t.id_conducteur) AS note_conducteur
                          FROM trajets t
                          JOIN utilisateurs u ON u.id = t.id_conducteur";


    /* ==============================================================
       LECTURE
       ============================================================== */

    /**
     * Cherche un trajet par son identifiant.
     * @return Trajet|null null si le trajet n'existe pas
     */
    public static function trouver(int $id): ?Trajet
    {
        $resultat = requete('SELECT * FROM trajets WHERE id = ?', [$id]);
        $trajet = $resultat->fetchObject('Trajet');

        if ($trajet === false) {
            return null;
        }
        return $trajet;
    }

    /**
     * Recherche des trajets RÉSERVABLES (page "Trouver un trajet").
     *
     * RÈGLE MÉTIER : on ne propose que les trajets ouverts, avec au moins
     * une place libre et qui ne sont pas encore partis.
     * Chaque critère vide est simplement ignoré.
     *
     * @param string $depart  Une partie du lieu de départ (ex : "Boulogne")
     * @param string $arrivee Une partie du lieu d'arrivée
     * @param string $date    Jour au format AAAA-MM-JJ, ou ''
     * @return array
     */
    public static function rechercher(string $depart, string $arrivee, string $date): array
    {
        $sql = self::SELECT_LISTE . " WHERE t.statut = 'Ouvert' AND t.places_restantes > 0 AND t.date_heure > ?";
        $parametres = [date('Y-m-d H:i:s')];

        // On ajoute un morceau de requête pour chaque critère rempli
        if ($depart !== '') {
            $sql .= ' AND t.origine LIKE ?';
            $parametres[] = '%' . $depart . '%';
        }
        if ($arrivee !== '') {
            $sql .= ' AND t.destination LIKE ?';
            $parametres[] = '%' . $arrivee . '%';
        }
        if ($date !== '') {
            $sql .= ' AND DATE(t.date_heure) = ?';
            $parametres[] = $date;
        }

        $sql .= ' ORDER BY t.date_heure';   // le plus proche en premier

        return requete($sql, $parametres)->fetchAll();
    }

    /**
     * Les prochains trajets réservables (affichés sur l'accueil).
     */
    public static function listerProchains(int $limite): array
    {
        $sql = self::SELECT_LISTE . " WHERE t.statut = 'Ouvert' AND t.places_restantes > 0 AND t.date_heure > ?
                ORDER BY t.date_heure LIMIT " . (int) $limite;

        return requete($sql, [date('Y-m-d H:i:s')])->fetchAll();
    }

    /**
     * Tous les trajets publiés par un conducteur (page "Mes trajets").
     */
    public static function listerParConducteur(int $idConducteur): array
    {
        $sql = self::SELECT_LISTE . ' WHERE t.id_conducteur = ? ORDER BY t.date_heure DESC';

        return requete($sql, [$idConducteur])->fetchAll();
    }

    /**
     * Tous les trajets de la plateforme (back-office).
     */
    public static function listerTous(): array
    {
        return requete(self::SELECT_LISTE . ' ORDER BY t.date_heure DESC')->fetchAll();
    }

    /**
     * Indique si l'heure de départ est passée.
     */
    public function estParti(): bool
    {
        return strtotime($this->date_heure) <= time();
    }

    /**
     * Nombre de places déjà occupées par des passagers acceptés.
     */
    public function placesOccupees(): int
    {
        return $this->places_totales - $this->places_restantes;
    }

    /**
     * Indique si le trajet peut encore être modifié par son conducteur.
     */
    public function estModifiable(): bool
    {
        return !$this->estParti() && $this->statut !== 'Termine';
    }


    /* ==============================================================
       CRÉATION / MODIFICATION / SUPPRESSION
       ============================================================== */

    /**
     * Vérifie les champs du formulaire de trajet (publication ou modification).
     * @param string $origine
     * @param string $destination
     * @param string $dateHeure Valeur du champ datetime-local : "2026-12-03T08:15"
     * @param int    $places
     * @param float  $prix
     * @return array Les erreurs rangées par nom de champ, ex : ['prix' => '...'] (vide si tout est correct)
     */
    public static function valider(string $origine, string $destination, string $dateHeure, int $places, float $prix): array
    {
        $erreurs = [];

        if (mb_strlen($origine) < 2 || mb_strlen($origine) > 120) {
            $erreurs['origine'] = 'Le lieu de départ doit contenir entre 2 et 120 caractères.';
        }
        if (mb_strlen($destination) < 2 || mb_strlen($destination) > 120) {
            $erreurs['destination'] = 'Le lieu d\'arrivée doit contenir entre 2 et 120 caractères.';
        }
        // RÈGLE MÉTIER : on ne part pas pour arriver au même endroit
        if (mb_strtolower($origine) === mb_strtolower($destination)) {
            $erreurs['destination'] = 'Le départ et l\'arrivée doivent être différents.';
        }

        // RÈGLE MÉTIER : on ne publie pas un trajet dans le passé
        $date = DateTime::createFromFormat('Y-m-d\TH:i', $dateHeure);
        // createFromFormat "corrige" une date fausse (31 février -> 3 mars) : on refuse si elle a été modifiée
        if ($date === false || $date->format('Y-m-d\TH:i') !== $dateHeure) {
            $erreurs['date_heure'] = 'Indiquez une date et une heure de départ valides.';
        } elseif ($date->getTimestamp() <= time()) {
            $erreurs['date_heure'] = 'La date de départ doit être dans le futur.';
        }

        if ($places < 1 || $places > 8) {
            $erreurs['places'] = 'Le nombre de places doit être compris entre 1 et 8.';
        }
        if ($prix < 0 || $prix > 200) {
            $erreurs['prix'] = 'Le prix doit être compris entre 0 et 200 €.';
        }

        return $erreurs;
    }

    /**
     * Enregistre un nouveau trajet. Au départ, toutes les places sont libres.
     * @return int L'identifiant du trajet créé
     */
    public static function creer(int $idConducteur, string $origine, string $destination, string $dateHeure, int $places, float $prix): int
    {
        $sql = "INSERT INTO trajets (origine, destination, date_heure, places_totales, places_restantes, statut, prix, id_conducteur)
                VALUES (?, ?, ?, ?, ?, 'Ouvert', ?, ?)";

        // datetime-local envoie "2026-12-03T08:15" : on remplace le T pour MySQL
        $dateMysql = str_replace('T', ' ', $dateHeure) . ':00';

        requete($sql, [$origine, $destination, $dateMysql, $places, $places, $prix, $idConducteur]);

        return (int) connexionBdd()->lastInsertId();
    }

    /**
     * Modifie le trajet (par son conducteur).
     *
     * RÈGLE MÉTIER : on ne peut pas descendre sous le nombre de places
     * déjà promises aux passagers acceptés.
     *
     * @return string Message d'erreur, ou '' si la modification est faite
     */
    public function modifier(string $origine, string $destination, string $dateHeure, int $places, float $prix): string
    {
        if (!$this->estModifiable()) {
            return 'Ce trajet est parti ou terminé : il ne peut plus être modifié.';
        }
        if ($places < $this->placesOccupees()) {
            return 'Impossible : ' . $this->placesOccupees() . ' place(s) sont déjà réservées.';
        }

        // On recalcule les places libres et le statut
        $placesRestantes = $places - $this->placesOccupees();
        $statut = 'Ouvert';
        if ($placesRestantes === 0) {
            $statut = 'Complet';
        }

        $sql = 'UPDATE trajets
                SET origine = ?, destination = ?, date_heure = ?, places_totales = ?, places_restantes = ?, statut = ?, prix = ?
                WHERE id = ?';
        $dateMysql = str_replace('T', ' ', $dateHeure) . ':00';

        requete($sql, [$origine, $destination, $dateMysql, $places, $placesRestantes, $statut, $prix, $this->id]);

        return '';
    }

    /**
     * Supprime le trajet. Ses réservations et avis partent avec lui
     * grâce au ON DELETE CASCADE de la base.
     */
    public function supprimer(): void
    {
        requete('DELETE FROM trajets WHERE id = ?', [$this->id]);
    }

    /**
     * CLÔTURE AUTOMATIQUE : appelée par index.php au début de chaque page.
     *
     * RÈGLES MÉTIER :
     *  - un trajet "Ouvert" ou "Complet" dont l'heure de départ est passée
     *    devient "Termine" ;
     *  - ses demandes restées "En attente" sont refusées (le trajet est parti) ;
     *  - une fois "Termine", les participants peuvent se noter.
     *
     * Les demandes sont refusées AVANT de changer le statut du trajet,
     * car la première requête repère les trajets encore non terminés.
     *
     * shortcut: 2 UPDATE à chaque page, suffisant pour un site de campus ;
     * sur un gros site on lancerait ce code par une tâche planifiée (cron).
     */
    public static function cloturerTrajetsPasses(): void
    {
        $maintenant = date('Y-m-d H:i:s');

        requete("UPDATE reservations SET statut = 'Refusee'
                 WHERE statut = 'En attente'
                 AND id_trajet IN (SELECT id FROM trajets WHERE statut <> 'Termine' AND date_heure <= ?)", [$maintenant]);

        requete("UPDATE trajets SET statut = 'Termine' WHERE statut <> 'Termine' AND date_heure <= ?", [$maintenant]);
    }


    /* ==============================================================
       RÉSERVATION D'UNE PLACE  (méthode demandée par le cahier des charges)
       ============================================================== */

    /**
     * Retire une place au trajet pour le passager dont la demande est acceptée.
     * Appelée par Reservation::traiter() quand le conducteur clique "Accepter".
     *
     * RÈGLES MÉTIER vérifiées :
     *  1. le conducteur ne peut pas être passager de son propre trajet ;
     *  2. le trajet doit être "Ouvert" et pas encore parti ;
     *  3. le passager doit avoir une demande "En attente" ;
     *  4. il doit rester au moins une place.
     * Quand la dernière place est prise, le trajet passe en "Complet".
     *
     * @param int $idPassager Le passager accepté
     * @return bool true si la place a bien été retirée
     */
    public function reserverPlace(int $idPassager): bool
    {
        // Règle 1
        if ($idPassager === $this->id_conducteur) {
            return false;
        }
        // Règle 2
        if ($this->statut !== 'Ouvert' || $this->estParti()) {
            return false;
        }
        // Règle 3
        $sql = "SELECT COUNT(*) FROM reservations WHERE id_trajet = ? AND id_passager = ? AND statut = 'En attente'";
        $demande = (int) requete($sql, [$this->id, $idPassager])->fetchColumn();
        if ($demande === 0) {
            return false;
        }

        // Règle 4 : la condition "places_restantes > 0" est DANS la requête.
        // Si deux acceptations arrivent en même temps, la seconde ne modifie
        // aucune ligne : impossible de descendre sous zéro.
        $sql = 'UPDATE trajets SET places_restantes = places_restantes - 1 WHERE id = ? AND places_restantes > 0';
        $modification = requete($sql, [$this->id]);
        if ($modification->rowCount() === 0) {
            return false;
        }

        // Dernière place prise -> le trajet devient Complet
        requete("UPDATE trajets SET statut = 'Complet' WHERE id = ? AND places_restantes = 0", [$this->id]);

        $this->places_restantes = $this->places_restantes - 1;
        if ($this->places_restantes === 0) {
            $this->statut = 'Complet';
        }
        return true;
    }


    /* ==============================================================
       STATISTIQUES
       ============================================================== */

    /**
     * Chiffres sur les trajets d'un conducteur, ou de toute la plateforme.
     * @param int|null $idConducteur null = toute la plateforme (accueil, admin)
     * @return array publies, realises, ouverts, complets, places_occupees, places_totales, taux
     */
    public static function statistiques(?int $idConducteur): array
    {
        $sql = "SELECT COUNT(*) AS publies,
                       SUM(CASE WHEN statut = 'Termine' THEN 1 ELSE 0 END) AS realises,
                       SUM(CASE WHEN statut = 'Ouvert'  THEN 1 ELSE 0 END) AS ouverts,
                       SUM(CASE WHEN statut = 'Complet' THEN 1 ELSE 0 END) AS complets,
                       SUM(places_totales - places_restantes) AS places_occupees,
                       SUM(places_totales) AS places_totales,
                       AVG(100.0 * (places_totales - places_restantes) / places_totales) AS taux
                FROM trajets";
        $parametres = [];

        if ($idConducteur !== null) {
            $sql .= ' WHERE id_conducteur = ?';
            $parametres[] = $idConducteur;
        }

        $stats = requete($sql, $parametres)->fetch();

        // Sans aucun trajet, SUM et AVG renvoient NULL : on remplace par 0
        foreach ($stats as $cle => $valeur) {
            if ($valeur === null) {
                $stats[$cle] = 0;
            }
        }
        return $stats;
    }

    /**
     * Le passager qui a le plus voyagé avec ce conducteur
     * (trajets terminés, réservations confirmées).
     * @return array|null ['id', 'nom', 'nb'] ou null si aucun passager
     */
    public static function passagerLePlusFrequent(int $idConducteur): ?array
    {
        $sql = "SELECT u.id, u.nom, COUNT(*) AS nb
                FROM reservations r
                JOIN trajets t ON t.id = r.id_trajet
                JOIN utilisateurs u ON u.id = r.id_passager
                WHERE t.id_conducteur = ? AND t.statut = 'Termine' AND r.statut = 'Confirmee'
                GROUP BY u.id, u.nom
                ORDER BY nb DESC, u.nom
                LIMIT 1";

        $passager = requete($sql, [$idConducteur])->fetch();
        if ($passager === false) {
            return null;
        }
        return $passager;
    }
}
