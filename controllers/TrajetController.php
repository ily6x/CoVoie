<?php

class TrajetController
{

    public function show(): void
    {
        $trajet = Trajet::find(inputId());
        $jeSuisLeConducteur = $trajet->idConducteur === currentUserId();
        $reservations = [];

        // Les demandes des passagers sont visibles uniquement par le conducteur.
        if ($jeSuisLeConducteur) {
            $reservations = Reservation::getList('r.id_trajet=?', [$trajet->id]);
        }

        render('trips/show', [
            'titre' => 'Détail du trajet',
            'trajet' => $trajet,
            'jeSuisLeConducteur' => $jeSuisLeConducteur,
            'reservations' => $reservations,
            'dejaDemande' => Reservation::hasRequest($trajet->id, currentUserId())
        ]);
    }

    public function newTrip(): void
    {
        requireLogin();
        render('trips/form', ['titre' => 'Publier', 'trajet' => null]);
    }

    public function create(): void
    {
        $idUtilisateur = requireLogin();
        [$origine, $destination, $date, $places, $prix] = Trajet::validate($_POST);
        $id = Trajet::create($idUtilisateur, $origine, $destination, $date, $places, $prix);
        flash('Trajet publié.');
        redirect('trajet&id='.$id);
    }

    public function edit(): void
    {
        $idUtilisateur = requireLogin();
        $trajet = Trajet::find(inputId());
        $trajet->checkDriver($idUtilisateur);
        if ($trajet->hasDeparted() || $trajet->statut === 'Termine') {
            throw new DomainException('Ce trajet ne peut plus être modifié.');
        }
        render('trips/form', ['titre' => 'Modifier', 'trajet' => $trajet]);
    }

    public function update(): void
    {
        Trajet::update(inputId(), requireLogin(), $_POST);
        flash('Trajet modifié.');
        redirect('trajet&id='.inputId());
    }

    public function delete(): void
    {
        Trajet::delete(inputId(), requireLogin());
        flash('Trajet supprimé.');
        redirect('mes-trajets');
    }

    public function cloturer(): void
    {
        Trajet::cloturer(inputId(), requireLogin());
        flash('Trajet clôturé. Vous pouvez noter les participants.');
        redirect('trajet&id='.inputId());
    }

    public function myTrips(): void
    {
        $idUtilisateur = requireLogin();
        render('trips/my-trips', [
            'titre' => 'Mes trajets',
            'trajets' => Trajet::getList('t.id_conducteur=?', [$idUtilisateur]),
            'enAttente' => Reservation::getList("t.id_conducteur=? AND r.statut='En attente' AND t.statut<>'Termine' AND t.date_heure>?", [$idUtilisateur, now()])
        ]);
    }

    public function statistics(): void
    {
        $idUtilisateur = requireLogin();
        $stats = Trajet::statistics($idUtilisateur);
        $passagerFrequent = Database::query("SELECT u.id,u.nom,COUNT(*) AS nb
            FROM reservations r
            JOIN trajets t ON t.id=r.id_trajet
            JOIN utilisateurs u ON u.id=r.id_passager
            WHERE t.id_conducteur=? AND t.statut='Termine' AND r.statut='Confirmee'
            GROUP BY u.id,u.nom
            ORDER BY nb DESC,u.id LIMIT 1", [$idUtilisateur])->fetch();
        render('trips/statistics', [
            'titre' => 'Mes statistiques',
            'stats' => $stats,
            'tauxRemplissage' => (float) $stats['taux'],
            'maNote' => Avis::noteMoyenne($idUtilisateur),
            'passagerFrequent' => $passagerFrequent
        ]);
    }
}
