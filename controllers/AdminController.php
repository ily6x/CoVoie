<?php

class AdminController
{

    public function index(): void
    {
        requireAdmin();
        $trajets = Trajet::statistics();
        $autres = [
            'membres' => Database::query('SELECT COUNT(*) FROM utilisateurs')->fetchColumn(),
            'reservations' => Database::query('SELECT COUNT(*) FROM reservations')->fetchColumn(),
            'avis' => Database::query('SELECT COUNT(*) FROM avis')->fetchColumn(),
            'confirmees' => Database::query("SELECT COUNT(*) FROM reservations WHERE statut='Confirmee'")->fetchColumn(),
            'en_attente' => Database::query("SELECT COUNT(*) FROM reservations WHERE statut='En attente'")->fetchColumn(),
            'note_moyenne' => Database::query('SELECT AVG(note) FROM avis')->fetchColumn()
        ];
        render('admin/dashboard', [
            'titre' => 'Administration',
            'trajets' => $trajets,
            'autres' => $autres,
            'tauxRemplissage' => (float) $trajets['taux']
        ]);
    }

    public function reviews(): void
    {
        requireAdmin();
        render('admin/reviews', ['titre' => 'Modération', 'avis' => Avis::getList()]);
    }

    public function users(): void
    {
        requireAdmin();
        render('admin/users', ['titre' => 'Comptes', 'utilisateurs' => Utilisateur::all()]);
    }

    public function trips(): void
    {
        requireAdmin();
        render('admin/trips', ['titre' => 'Tous les trajets', 'trajets' => Trajet::getList()]);
    }

    public function deleteReview(): void
    {
        requireAdmin();
        Database::query('DELETE FROM avis WHERE id=?', [inputId()]);
        flash('Avis supprimé.');
        redirect('admin-avis');
    }

    public function deleteAccount(): void
    {
        requireAdmin();
        Utilisateur::delete(inputId(), currentUserId());
        flash('Compte supprimé.');
        redirect('admin-utilisateurs');
    }
}
