<?php
/**
 * ------------------------------------------------------------------
 *  CoVoie - Modèle Reservation  (table : reservations)   MODULE B
 * ------------------------------------------------------------------
 *  La demande d'un passager pour une place sur un trajet.
 *
 *  Cycle de vie (colonne statut) :
 *      En attente  --(le conducteur accepte)-->  Confirmee
 *      En attente  --(le conducteur refuse)--->  Refusee
 *
 *  Seule une réservation CONFIRMÉE consomme une place du trajet.
 *  Le passager peut annuler sa demande tant qu'elle est "En attente".
 * ------------------------------------------------------------------
 */
class Reservation
{
    public int $id;
    public string $statut;            // 'En attente', 'Confirmee' ou 'Refusee'
    public string $date_reservation;
    public int $id_trajet;
    public int $id_passager;

    /**
     * Statut de la demande d'un passager sur un trajet.
     * @return string|null Le statut, ou null s'il n'a jamais demandé
     */
    public static function statutDemande(int $idTrajet, int $idPassager): ?string
    {
        $sql = 'SELECT statut FROM reservations WHERE id_trajet = ? AND id_passager = ?';
        $statut = requete($sql, [$idTrajet, $idPassager])->fetchColumn();

        if ($statut === false) {
            return null;
        }
        return $statut;
    }

    /**
     * Un passager demande une place sur un trajet.
     *
     * RÈGLES MÉTIER :
     *  - on ne réserve pas son propre trajet ;
     *  - le trajet doit être ouvert, avoir une place libre et ne pas être parti ;
     *  - UNE SEULE demande par passager et par trajet. C'est vérifié ici
     *    ET garanti par la base (clé UNIQUE une_seule_demande).
     *
     * @return string Message d'erreur, ou '' si la demande est enregistrée
     */
    public static function demander(Trajet $trajet, int $idPassager): string
    {
        if ($trajet->id_conducteur === $idPassager) {
            return 'Vous ne pouvez pas réserver votre propre trajet.';
        }
        if ($trajet->statut !== 'Ouvert' || $trajet->places_restantes < 1) {
            return 'Ce trajet n\'a plus de place disponible.';
        }
        if ($trajet->estParti()) {
            return 'Ce trajet est déjà parti.';
        }
        if (self::statutDemande($trajet->id, $idPassager) !== null) {
            return 'Vous avez déjà fait une demande pour ce trajet.';
        }

        $sql = "INSERT INTO reservations (statut, date_reservation, id_trajet, id_passager)
                VALUES ('En attente', ?, ?, ?)";
        requete($sql, [date('Y-m-d H:i:s'), $trajet->id, $idPassager]);

        return '';
    }

    /**
     * Le conducteur accepte ou refuse une demande.
     *
     * Si on accepte, DEUX modifications doivent réussir ensemble :
     *   1. retirer une place au trajet   (Trajet::reserverPlace)
     *   2. passer la demande en "Confirmee"
     * On utilise donc une TRANSACTION : en cas de problème, rollBack()
     * annule tout et la base reste cohérente.
     *
     * @param Trajet $trajet        Le trajet concerné
     * @param int    $idReservation La demande à traiter
     * @param int    $idConducteur  La personne connectée
     * @param bool   $accepter      true = accepter, false = refuser
     * @return string Message d'erreur, ou '' si c'est fait
     */
    public static function traiter(Trajet $trajet, int $idReservation, int $idConducteur, bool $accepter): string
    {
        // CONTRÔLE D'ACCÈS : seul le conducteur du trajet décide
        if ($trajet->id_conducteur !== $idConducteur) {
            return 'Seul le conducteur de ce trajet peut traiter les demandes.';
        }
        if (!$trajet->estModifiable()) {
            return 'Ce trajet est parti ou terminé.';
        }

        // La demande doit exister, concerner CE trajet et être encore en attente
        $sql = "SELECT * FROM reservations WHERE id = ? AND id_trajet = ? AND statut = 'En attente'";
        $reservation = requete($sql, [$idReservation, $trajet->id])->fetchObject('Reservation');
        if ($reservation === false) {
            return 'Cette demande n\'existe pas ou a déjà été traitée.';
        }

        // Cas simple : le refus ne touche pas aux places
        if (!$accepter) {
            requete("UPDATE reservations SET statut = 'Refusee' WHERE id = ?", [$reservation->id]);
            return '';
        }

        // Cas de l'acceptation : transaction
        $pdo = connexionBdd();
        $pdo->beginTransaction();
        try {
            if (!$trajet->reserverPlace($reservation->id_passager)) {
                $pdo->rollBack();
                return 'Il ne reste plus de place sur ce trajet.';
            }
            requete("UPDATE reservations SET statut = 'Confirmee' WHERE id = ?", [$reservation->id]);
            $pdo->commit();
        } catch (PDOException $erreur) {
            $pdo->rollBack();
            return 'Une erreur est survenue, rien n\'a été modifié.';
        }

        return '';
    }

    /**
     * Le passager annule sa demande.
     * RÈGLE MÉTIER : seulement la sienne, et seulement si elle est "En attente"
     * (une place confirmée a été promise au conducteur).
     * @return bool true si une demande a bien été supprimée
     */
    public static function annuler(int $idReservation, int $idPassager): bool
    {
        $sql = "DELETE FROM reservations WHERE id = ? AND id_passager = ? AND statut = 'En attente'";
        $resultat = requete($sql, [$idReservation, $idPassager]);

        return $resultat->rowCount() === 1;
    }

    /**
     * Les demandes reçues pour un trajet, avec le nom et la note du passager.
     * (affiché au conducteur sur la page du trajet)
     */
    public static function listerParTrajet(int $idTrajet): array
    {
        $sql = "SELECT r.*, u.nom AS nom_passager,
                       (SELECT AVG(note) FROM avis WHERE id_cible = r.id_passager) AS note_passager
                FROM reservations r
                JOIN utilisateurs u ON u.id = r.id_passager
                WHERE r.id_trajet = ?
                ORDER BY r.date_reservation";

        return requete($sql, [$idTrajet])->fetchAll();
    }

    /**
     * Toutes les réservations d'un passager, avec le trajet et son conducteur.
     * (page "Mes réservations")
     */
    public static function listerParPassager(int $idPassager): array
    {
        $sql = "SELECT r.id, r.statut, r.date_reservation,
                       t.id AS id_trajet, t.origine, t.destination, t.date_heure, t.prix,
                       t.statut AS statut_trajet, u.nom AS nom_conducteur, t.id_conducteur
                FROM reservations r
                JOIN trajets t ON t.id = r.id_trajet
                JOIN utilisateurs u ON u.id = t.id_conducteur
                WHERE r.id_passager = ?
                ORDER BY t.date_heure DESC";

        return requete($sql, [$idPassager])->fetchAll();
    }

    /**
     * Les demandes en attente sur les trajets À VENIR d'un conducteur.
     * (encart "À traiter" de la page "Mes trajets")
     */
    public static function listerEnAttentePourConducteur(int $idConducteur): array
    {
        $sql = "SELECT r.id, r.date_reservation, r.id_passager, u.nom AS nom_passager,
                       t.id AS id_trajet, t.origine, t.destination, t.date_heure, t.places_restantes
                FROM reservations r
                JOIN trajets t ON t.id = r.id_trajet
                JOIN utilisateurs u ON u.id = r.id_passager
                WHERE t.id_conducteur = ? AND r.statut = 'En attente'
                AND t.statut <> 'Termine' AND t.date_heure > ?
                ORDER BY t.date_heure";

        return requete($sql, [$idConducteur, date('Y-m-d H:i:s')])->fetchAll();
    }

    /**
     * Nombre de réservations par statut (back-office).
     * @return array total, confirmees, en_attente, refusees
     */
    public static function compter(): array
    {
        $sql = "SELECT COUNT(*) AS total,
                       SUM(CASE WHEN statut = 'Confirmee'  THEN 1 ELSE 0 END) AS confirmees,
                       SUM(CASE WHEN statut = 'En attente' THEN 1 ELSE 0 END) AS en_attente,
                       SUM(CASE WHEN statut = 'Refusee'    THEN 1 ELSE 0 END) AS refusees
                FROM reservations";

        return requete($sql)->fetch();
    }
}
