<?php

class HomeController
{

    public function index(): void
    {
        $stats = Trajet::statistics();
        $chiffres = [
            'total_trajets' => $stats['publies'],
            'trajets_realises' => $stats['realises'],
            'total_membres' => Database::query('SELECT COUNT(*) FROM utilisateurs')->fetchColumn(),
            'note_moyenne' => Database::query('SELECT AVG(note) FROM avis')->fetchColumn()
        ];
        $meilleurs = Database::query('SELECT u.id,u.nom,AVG(a.note) AS moyenne
            FROM utilisateurs u
            JOIN avis a ON a.id_cible=u.id
            GROUP BY u.id,u.nom
            ORDER BY moyenne DESC,u.id LIMIT 5')->fetchAll();
        render('home/index', [
            'titre' => 'Accueil',
            'chiffres' => $chiffres,
            'meilleurs' => $meilleurs,
            'trajets' => array_slice(Trajet::search(), 0, 5)
        ]);
    }

    public function profile(): void
    {
        $utilisateur = Utilisateur::find(inputId());
        if (!$utilisateur) {
            throw new HttpException('Profil introuvable.', 404);
        }
        render('profile/show', [
            'titre' => 'Profil',
            'utilisateur' => $utilisateur,
            'noteMoyenne' => $utilisateur->noteMoyenne(),
            'nombreAvis' => $utilisateur->nombreAvis(),
            'trajetsRealises' => $utilisateur->completedTripCount(),
            'avis' => Avis::getList($utilisateur->id)
        ]);
    }
}
