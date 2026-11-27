<?php
/**
 * ------------------------------------------------------------------
 *  CoVoie - index.php  (CONTRÔLEUR FRONTAL)
 * ------------------------------------------------------------------
 *  TOUTES les pages du site passent par ce fichier.
 *  L'URL indique la page voulue : index.php?page=mes-trajets
 *
 *  Étapes :
 *   1. charger les fichiers nécessaires (config, fonctions, classes)
 *   2. lire la page demandée dans l'URL
 *   3. trouver le contrôleur correspondant dans la table des routes
 *   4. appeler la méthode du contrôleur, qui affichera la vue
 *
 *  Architecture MVC :
 *   - Modèle      (modeles/)     : les requêtes SQL et les règles métier
 *   - Vue         (vues/)        : le HTML affiché
 *   - Contrôleur  (controleurs/) : fait le lien entre les deux
 * ------------------------------------------------------------------
 */

// 1. Chargement des fichiers
require_once 'includes/config.php';
require_once 'includes/connexion.php';
require_once 'includes/fonctions.php';
require_once 'includes/routes.php';
/** @var array $routes  Table des pages, définie dans includes/routes.php */

// Filet de sécurité : si une erreur imprévue arrive (ex : une requête SQL qui échoue),
// on affiche la page d'erreur du site au lieu du message brut de PHP (qui montre les chemins des fichiers).
set_exception_handler(function (Throwable $erreur) {
    afficherErreur(500, 'Une erreur inattendue est survenue. Réessayez dans un instant.');
});

require_once 'modeles/Utilisateur.php';
require_once 'modeles/Trajet.php';
require_once 'modeles/Reservation.php';
require_once 'modeles/Avis.php';

require_once 'controleurs/AccueilController.php';
require_once 'controleurs/AuthController.php';
require_once 'controleurs/TrajetController.php';
require_once 'controleurs/ReservationController.php';
require_once 'controleurs/AvisController.php';
require_once 'controleurs/AdminController.php';

// Sécurité : si le compte connecté a été supprimé par l'admin,
// on ferme sa session pour qu'il ne puisse plus rien faire.
if (estConnecte() && Utilisateur::trouver(idConnecte()) === null) {
    $_SESSION = [];
}

// Clôture automatique : les trajets dont l'heure de départ est passée deviennent "Termine"
Trajet::cloturerTrajetsPasses();

// 2. Page demandée (par défaut : l'accueil)
$page = 'accueil';
if (isset($_GET['page']) && is_string($_GET['page'])) {
    $page = $_GET['page'];
}

// 3. La page existe-t-elle dans la table des routes ?
if (!isset($routes[$page])) {
    afficherErreur(404, 'Cette page n\'existe pas.');
}

$nomControleur = $routes[$page][0];   // ex : 'TrajetController'
$nomMethode = $routes[$page][1];      // ex : 'detail'

// 4. On crée le contrôleur et on appelle sa méthode
$controleur = new $nomControleur();
$controleur->$nomMethode();
