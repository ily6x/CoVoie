<?php
/**
 * ------------------------------------------------------------------
 *  CoVoie - routes.php
 * ------------------------------------------------------------------
 *  La liste de TOUTES les pages du site.
 *
 *  Chaque ligne associe le paramètre "page" de l'URL à un contrôleur
 *  et à une de ses méthodes :
 *
 *      index.php?page=trajet&id=12
 *          -> new TrajetController()
 *          -> ->detail()
 *
 *  Les actions marquées (POST) modifient la base : elles ne marchent
 *  qu'avec un formulaire et un jeton CSRF (voir verifierFormulaire()).
 * ------------------------------------------------------------------
 */

$routes = [

    // ---------- Pages publiques ----------
    'accueil'             => ['AccueilController', 'index'],
    'profil'              => ['AccueilController', 'profil'],
    'trajet'              => ['TrajetController', 'detail'],

    // ---------- Compte ----------
    'connexion'           => ['AuthController', 'formulaireConnexion'],
    'connexion-valider'   => ['AuthController', 'connecter'],            // (POST)
    'inscription'         => ['AuthController', 'formulaireInscription'],
    'inscription-valider' => ['AuthController', 'inscrire'],             // (POST)
    'deconnexion'         => ['AuthController', 'deconnecter'],          // (POST)

    // ---------- Module A : gestion des trajets (conducteur) ----------
    'publier'             => ['TrajetController', 'formulairePublier'],
    'publier-valider'     => ['TrajetController', 'publier'],            // (POST)
    'modifier'            => ['TrajetController', 'formulaireModifier'],
    'modifier-valider'    => ['TrajetController', 'modifier'],           // (POST)
    'supprimer-trajet'    => ['TrajetController', 'supprimer'],          // (POST)
    'mes-trajets'         => ['TrajetController', 'mesTrajets'],
    'mes-statistiques'    => ['TrajetController', 'statistiques'],

    // ---------- Module B : recherche et réservations (passager) ----------
    'trajets'             => ['ReservationController', 'rechercher'],   // page publique
    'reserver'            => ['ReservationController', 'reserver'],      // (POST)
    'accepter'            => ['ReservationController', 'accepter'],      // (POST)
    'refuser'             => ['ReservationController', 'refuser'],       // (POST)
    'annuler'             => ['ReservationController', 'annuler'],       // (POST)
    'mes-reservations'    => ['ReservationController', 'mesReservations'],

    // ---------- Module C : avis et réputation ----------
    'noter'               => ['AvisController', 'formulaire'],
    'noter-valider'       => ['AvisController', 'enregistrer'],          // (POST)
    'mes-avis'            => ['AvisController', 'mesAvis'],

    // ---------- Back-office administrateur ----------
    'admin'               => ['AdminController', 'tableauDeBord'],
    'admin-avis'          => ['AdminController', 'avis'],
    'admin-utilisateurs'  => ['AdminController', 'utilisateurs'],
    'admin-trajets'       => ['AdminController', 'trajets'],
    'admin-supprimer-avis'   => ['AdminController', 'supprimerAvis'],    // (POST)
    'admin-supprimer-compte' => ['AdminController', 'supprimerCompte']   // (POST)
];
