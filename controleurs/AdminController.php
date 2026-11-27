<?php
/**
 * ------------------------------------------------------------------
 *  CoVoie - AdminController  (BACK-OFFICE)
 * ------------------------------------------------------------------
 *  Réservé au compte dont le rôle est 'admin'.
 *  CHAQUE méthode commence par protegerAdmin() : un membre qui tape
 *  l'URL à la main obtient une erreur 403.
 * ------------------------------------------------------------------
 */
class AdminController
{
    /**
     * Tableau de bord : chiffres globaux de la plateforme.
     */
    public function tableauDeBord(): void
    {
        protegerAdmin();

        afficherVue('admin/tableau-de-bord', [
            'titre' => 'Administration',
            'onglet' => 'admin',
            'stats' => Trajet::statistiques(null),
            'reservations' => Reservation::compter(),
            'nombreMembres' => (int) requete('SELECT COUNT(*) FROM utilisateurs')->fetchColumn(),
            'nombreAvis' => (int) requete('SELECT COUNT(*) FROM avis')->fetchColumn(),
            'moyenneGenerale' => Avis::moyenneGenerale()
        ]);
    }

    /**
     * Modération : liste de tous les avis.
     */
    public function avis(): void
    {
        protegerAdmin();

        afficherVue('admin/avis', [
            'titre' => 'Modération des avis',
            'onglet' => 'admin-avis',
            'avis' => Avis::listerTous()
        ]);
    }

    /**
     * Liste de tous les comptes.
     */
    public function utilisateurs(): void
    {
        protegerAdmin();

        afficherVue('admin/utilisateurs', [
            'titre' => 'Comptes',
            'onglet' => 'admin-utilisateurs',
            'utilisateurs' => Utilisateur::listerTous()
        ]);
    }

    /**
     * Liste de tous les trajets.
     */
    public function trajets(): void
    {
        protegerAdmin();

        afficherVue('admin/trajets', [
            'titre' => 'Tous les trajets',
            'onglet' => 'admin-trajets',
            'trajets' => Trajet::listerTous()
        ]);
    }

    /**
     * Supprime un avis abusif.  (POST)
     */
    public function supprimerAvis(): void
    {
        protegerAdmin();
        verifierFormulaire();

        if (Avis::supprimer(lireId())) {
            ajouterMessage('Avis supprimé. La note moyenne du membre est recalculée automatiquement.');
        } else {
            ajouterMessage('Cet avis n\'existe plus.', 'erreur');
        }
        redirection('admin-avis');
    }

    /**
     * Supprime un compte membre.  (POST)
     */
    public function supprimerCompte(): void
    {
        protegerAdmin();
        verifierFormulaire();

        $erreur = Utilisateur::supprimer(lireId(), idConnecte());

        if ($erreur !== '') {
            ajouterMessage($erreur, 'erreur');
        } else {
            ajouterMessage('Compte supprimé, ainsi que ses trajets, réservations et avis.');
        }
        redirection('admin-utilisateurs');
    }
}
