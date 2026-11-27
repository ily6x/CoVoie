<?php
/**
 * ------------------------------------------------------------------
 *  CoVoie - AccueilController
 * ------------------------------------------------------------------
 *  Pages publiques générales : l'accueil et le profil d'un membre.
 * ------------------------------------------------------------------
 */
class AccueilController
{
    /**
     * Page d'accueil : formulaire de recherche, chiffres clés,
     * prochains départs et membres les mieux notés.
     */
    public function index(): void
    {
        $stats = Trajet::statistiques(null);
        $nombreMembres = (int) requete('SELECT COUNT(*) FROM utilisateurs')->fetchColumn();

        afficherVue('accueil/index', [
            'titre' => 'Accueil',
            'stats' => $stats,
            'nombreMembres' => $nombreMembres,
            'moyenneGenerale' => Avis::moyenneGenerale(),
            'prochainsTrajets' => Trajet::listerProchains(6),
            'meilleursMembres' => Utilisateur::meilleursMembres(5)
        ]);
    }

    /**
     * Profil public d'un membre : sa note moyenne et les avis reçus.
     * URL : index.php?page=profil&id=2   (sans id : mon propre profil)
     */
    public function profil(): void
    {
        // Sans id dans l'URL, on affiche le profil de la personne connectée
        if (!isset($_GET['id'])) {
            $id = proteger();
        } else {
            $id = lireId();
        }

        $membre = Utilisateur::trouver($id);
        if ($membre === null) {
            afficherErreur(404, 'Ce membre n\'existe pas.');
        }

        afficherVue('accueil/profil', [
            'titre' => $membre->nom,
            'membre' => $membre,
            'note' => Avis::noteMoyenne($membre->id),
            'nombreAvis' => Avis::nombreAvis($membre->id),
            'trajetsRealises' => $membre->nombreTrajetsRealises(),
            'avisRecus' => Avis::listerRecus($membre->id)
        ]);
    }
}
