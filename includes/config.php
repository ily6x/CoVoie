<?php
/**
 * ------------------------------------------------------------------
 *  CoVoie - config.php
 * ------------------------------------------------------------------
 *  Réglages généraux de l'application : nom du site, fuseau horaire,
 *  identifiants de la base de données et démarrage de la session.
 *
 *  C'est le SEUL fichier à modifier si la base change de serveur.
 * ------------------------------------------------------------------
 */

// Nom affiché dans l'en-tête et dans l'onglet du navigateur
define('NOM_APPLICATION', 'CoVoie');
define('SLOGAN_APPLICATION', 'Covoiturage entre étudiants du campus');

// Toutes les dates du site sont à l'heure de Paris
date_default_timezone_set('Europe/Paris');

// Connexion MySQL (valeurs par défaut de WAMP)
define('BDD_HOTE', '127.0.0.1');
define('BDD_PORT', '3306');
define('BDD_NOM', 'covoiturage');
define('BDD_UTILISATEUR', 'root');
define('BDD_MOT_DE_PASSE', '');

// Sécurité du cookie de session :
//  - httponly : le JavaScript ne peut pas lire le cookie
//  - samesite Lax : le cookie n'est pas envoyé par un formulaire venant d'un autre site
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();
