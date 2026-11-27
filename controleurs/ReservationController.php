<?php
/**
 * ------------------------------------------------------------------
 *  CoVoie - ReservationController                      MODULE B
 * ------------------------------------------------------------------
 *  Recherche de trajets, demandes de réservation (passager),
 *  acceptation / refus (conducteur) et annulation.
 * ------------------------------------------------------------------
 */
class ReservationController
{
    /**
     * Charge le trajet dont l'id est dans l'URL (erreur 404 s'il n'existe pas).
     * @return Trajet
     */
    private function chargerTrajet(): Trajet
    {
        $trajet = Trajet::trouver(lireId());
        if ($trajet === null) {
            afficherErreur(404, 'Ce trajet n\'existe pas.');
        }
        return $trajet;
    }

    /**
     * Page "Trouver un trajet" (page publique).
     * Les critères arrivent dans l'URL (formulaire en méthode GET) :
     * index.php?page=trajets&depart=Boulogne&arrivee=&date=
     */
    public function rechercher(): void
    {
        $depart = '';
        $arrivee = '';
        $date = '';
        if (isset($_GET['depart'])) {
            $depart = trim((string) $_GET['depart']);
        }
        if (isset($_GET['arrivee'])) {
            $arrivee = trim((string) $_GET['arrivee']);
        }
        if (isset($_GET['date'])) {
            $date = trim((string) $_GET['date']);
        }

        afficherVue('reservations/recherche', [
            'titre' => 'Trouver un trajet',
            'depart' => $depart,
            'arrivee' => $arrivee,
            'date' => $date,
            'trajets' => Trajet::rechercher($depart, $arrivee, $date)
        ]);
    }

    /**
     * Le passager envoie une demande de place.  (POST)
     */
    public function reserver(): void
    {
        $idConnecte = proteger();
        verifierFormulaire();
        $trajet = $this->chargerTrajet();

        $erreur = Reservation::demander($trajet, $idConnecte);

        if ($erreur !== '') {
            ajouterMessage($erreur, 'erreur');
        } else {
            ajouterMessage('Demande envoyée ! Le conducteur doit maintenant l\'accepter.');
        }
        redirection('trajet&id=' . $trajet->id);
    }

    /**
     * Le conducteur accepte une demande.  (POST)
     * URL : index.php?page=accepter&id=<trajet>&reservation=<demande>
     */
    public function accepter(): void
    {
        $this->traiter(true);
    }

    /**
     * Le conducteur refuse une demande.  (POST)
     */
    public function refuser(): void
    {
        $this->traiter(false);
    }

    /**
     * Code commun à accepter() et refuser().
     * @param bool $accepter true pour accepter, false pour refuser
     */
    private function traiter(bool $accepter): void
    {
        $idConnecte = proteger();
        verifierFormulaire();
        $trajet = $this->chargerTrajet();

        $erreur = Reservation::traiter($trajet, lireId('reservation'), $idConnecte, $accepter);

        if ($erreur !== '') {
            ajouterMessage($erreur, 'erreur');
        } elseif ($accepter) {
            ajouterMessage('Demande acceptée : une place a été retirée du trajet.');
        } else {
            ajouterMessage('Demande refusée.');
        }

        // On revient sur la page d'où vient le clic (détail du trajet ou "Mes trajets")
        if (champ('retour') === 'mes-trajets') {
            redirection('mes-trajets');
        }
        redirection('trajet&id=' . $trajet->id);
    }

    /**
     * Le passager annule une demande encore en attente.  (POST)
     */
    public function annuler(): void
    {
        $idConnecte = proteger();
        verifierFormulaire();

        if (Reservation::annuler(lireId(), $idConnecte)) {
            ajouterMessage('Demande annulée.');
        } else {
            ajouterMessage('Seule une demande en attente peut être annulée.', 'erreur');
        }
        redirection('mes-reservations');
    }

    /**
     * Page "Mes réservations" du passager.
     */
    public function mesReservations(): void
    {
        $idConnecte = proteger();

        afficherVue('reservations/mes-reservations', [
            'titre' => 'Mes réservations',
            'reservations' => Reservation::listerParPassager($idConnecte)
        ]);
    }
}
