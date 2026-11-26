<?php

class AvisController
{

    public function myReviews(): void
    {
        $idUtilisateur = requireLogin();
        $lignes = [];

        // On affiche les trajets terminés auxquels l'utilisateur a participé.
        $condition = "t.statut='Termine' AND (
            t.id_conducteur=? OR EXISTS (
                SELECT 1 FROM reservations r
                WHERE r.id_trajet=t.id
                AND r.id_passager=?
                AND r.statut='Confirmee'
            )
        )";
        $trajets = Trajet::getList($condition, [$idUtilisateur, $idUtilisateur]);

        foreach ($trajets as $trajet) {
            $lignes[] = [
                'trajet' => $trajet,
                'participants' => Avis::participants($trajet, $idUtilisateur)
            ];
        }

        render('reviews/my-reviews', [
            'titre' => 'Mes avis',
            'lignes' => $lignes,
            'avisRecus' => Avis::getList($idUtilisateur)
        ]);
    }

    public function form(): void
    {
        $idUtilisateur = requireLogin();
        $trajet = Trajet::find(inputId());
        render('reviews/form', [
            'titre' => 'Noter',
            'trajet' => $trajet,
            'participants' => Avis::participants($trajet, $idUtilisateur)
        ]);
    }

    public function create(): void
    {
        Avis::create(inputId(), requireLogin(), inputId('id_cible', $_POST), inputId('note', $_POST), inputText($_POST, 'commentaire'));
        flash('Avis publié.');
        redirect('noter&id='.inputId());
    }
}
