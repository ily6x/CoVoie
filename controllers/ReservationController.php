<?php

class ReservationController
{

    public function search(): void
    {
        $origine = inputText($_GET, 'origine');
        $destination = inputText($_GET, 'destination');
        $date = inputText($_GET, 'date');
        render('reservations/search', [
            'titre' => 'Recherche',
            'origine' => $origine,
            'destination' => $destination,
            'date' => $date,
            'trajets' => Trajet::search($origine, $destination, $date)
        ]);
    }

    public function request(): void
    {
        Reservation::create(inputId(), requireLogin());
        flash('Demande envoyée au conducteur.');
        redirect('mes-reservations');
    }

    public function accept(): void
    {
        Reservation::traiter(inputId(), inputId('reservation'), requireLogin(), true);
        flash('Demande confirmée.');
        redirect('trajet&id='.inputId());
    }

    public function reject(): void
    {
        Reservation::traiter(inputId(), inputId('reservation'), requireLogin(), false);
        flash('Demande refusée.');
        redirect('trajet&id='.inputId());
    }

    public function cancel(): void
    {
        Reservation::cancel(inputId(), requireLogin());
        flash('Demande annulée.');
        redirect('mes-reservations');
    }

    public function myReservations(): void
    {
        render('reservations/my-reservations', [
            'titre' => 'Mes réservations',
            'reservations' => Reservation::getList('r.id_passager=?', [requireLogin()])
        ]);
    }
}
