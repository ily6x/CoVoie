<?php
/**
 * ------------------------------------------------------------------
 *  CoVoie - fonctions.php
 * ------------------------------------------------------------------
 *  Petites fonctions utilisées partout sur le site, rangées par thème :
 *   1. Session : qui est connecté ?
 *   2. Contrôle d'accès
 *   3. Formulaires : lecture des champs et jeton CSRF
 *   4. Navigation : messages, redirection, affichage des vues
 *   5. Affichage : dates, prix, notes, statuts
 * ------------------------------------------------------------------
 */


/* ==================================================================
   1. SESSION : QUI EST CONNECTÉ ?
   À la connexion, on range id, nom et rôle dans $_SESSION['utilisateur'].
   ================================================================== */

/**
 * Indique si un visiteur est connecté.
 * @return bool
 */
function estConnecte(): bool
{
    return isset($_SESSION['utilisateur']);
}

/**
 * Indique si la personne connectée est l'administrateur.
 * @return bool
 */
function estAdmin(): bool
{
    return estConnecte() && $_SESSION['utilisateur']['role'] === 'admin';
}

/**
 * Renvoie l'identifiant de la personne connectée (0 si visiteur).
 * @return int
 */
function idConnecte(): int
{
    if (estConnecte()) {
        return $_SESSION['utilisateur']['id'];
    }
    return 0;
}

/**
 * Renvoie le nom de la personne connectée ('' si visiteur).
 * @return string
 */
function nomConnecte(): string
{
    if (estConnecte()) {
        return $_SESSION['utilisateur']['nom'];
    }
    return '';
}


/* ==================================================================
   2. CONTRÔLE D'ACCÈS
   À appeler au début des méthodes de contrôleur protégées.
   ================================================================== */

/**
 * Bloque la page si personne n'est connecté : on renvoie vers la connexion.
 * @return int L'identifiant de la personne connectée
 */
function proteger(): int
{
    if (!estConnecte()) {
        ajouterMessage('Connectez-vous pour accéder à cette page.', 'erreur');
        redirection('connexion');
    }
    return idConnecte();
}

/**
 * Bloque la page si la personne connectée n'est pas administrateur.
 */
function protegerAdmin(): void
{
    proteger();
    if (!estAdmin()) {
        afficherErreur(403, 'Cette page est réservée à l\'administrateur.');
    }
}


/* ==================================================================
   3. FORMULAIRES
   ================================================================== */

/**
 * Lit un champ texte envoyé par formulaire (POST) et enlève les espaces autour.
 * @param string $nom Nom du champ (attribut name du input)
 * @return string     La valeur, ou '' si le champ n'existe pas
 */
function champ(string $nom): string
{
    if (isset($_POST[$nom]) && is_string($_POST[$nom])) {
        return trim($_POST[$nom]);
    }
    return '';
}

/**
 * Lit un identifiant dans l'URL (ex : index.php?page=trajet&id=12).
 * Si l'identifiant est absent ou n'est pas un entier positif, on arrête la page.
 * @param string $nom Nom du paramètre dans l'URL
 * @return int
 */
function lireId(string $nom = 'id'): int
{
    $id = 0;
    if (isset($_GET[$nom])) {
        $id = (int) $_GET[$nom];
    }

    if ($id <= 0) {
        afficherErreur(400, 'Identifiant invalide.');
    }
    return $id;
}

/**
 * Renvoie le jeton CSRF de la session (il est créé au premier appel).
 * Ce jeton secret prouve qu'un formulaire a bien été affiché par NOTRE site.
 * @return string
 */
function jetonCsrf(): string
{
    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/**
 * Écrit le champ caché à placer dans CHAQUE formulaire POST.
 * @return string Le code HTML du champ caché
 */
function champCsrf(): string
{
    return '<input type="hidden" name="csrf" value="' . jetonCsrf() . '">';
}

/**
 * À appeler au début de toute action qui MODIFIE la base de données.
 * Vérifie que :
 *  1. la page a été appelée par un formulaire POST (pas par un simple lien)
 *  2. le jeton CSRF envoyé est identique à celui de la session
 */
function verifierFormulaire(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        afficherErreur(405, 'Cette action doit être envoyée par un formulaire.');
    }

    // hash_equals compare deux chaînes de façon sûre
    if (!hash_equals(jetonCsrf(), champ('csrf'))) {
        afficherErreur(403, 'Formulaire expiré. Rechargez la page puis recommencez.');
    }
}


/* ==================================================================
   4. NAVIGATION
   ================================================================== */

/**
 * Enregistre un message à afficher sur la PROCHAINE page (message "flash").
 * @param string $texte Le message
 * @param string $type  'succes' (vert) ou 'erreur' (rouge)
 */
function ajouterMessage(string $texte, string $type = 'succes'): void
{
    $_SESSION['messages'][] = ['texte' => $texte, 'type' => $type];
}

/**
 * Récupère les messages en attente puis les efface (ils ne s'affichent qu'une fois).
 * @return array
 */
function lireMessages(): array
{
    $messages = [];
    if (isset($_SESSION['messages'])) {
        $messages = $_SESSION['messages'];
    }
    unset($_SESSION['messages']);

    return $messages;
}

/**
 * Redirige le navigateur vers une autre page du site, puis arrête le script.
 * Après un formulaire POST, on redirige toujours : un F5 ne renverra pas le formulaire.
 * @param string $page Ex : 'mes-trajets' ou 'trajet&id=12'
 */
function redirection(string $page): void
{
    header('Location: index.php?page=' . $page);
    exit;
}

/**
 * Affiche une page complète : header + vue demandée + footer.
 * @param string $vue     Chemin de la vue dans le dossier vues/, sans .php (ex : 'trajets/detail')
 * @param array  $donnees Variables transmises à la vue (ex : ['trajet' => $trajet])
 */
function afficherVue(string $vue, array $donnees = []): void
{
    // extract() transforme ['trajet' => $t] en une variable $trajet utilisable dans la vue
    extract($donnees);

    require 'vues/gabarit/header.php';
    require 'vues/' . $vue . '.php';
    require 'vues/gabarit/footer.php';
}

/**
 * Affiche une page d'erreur puis arrête le script.
 * @param int    $code    Code HTTP (400, 403, 404, 405...)
 * @param string $message Explication pour l'utilisateur
 */
function afficherErreur(int $code, string $message): void
{
    http_response_code($code);
    afficherVue('erreur', [
        'titre' => 'Erreur ' . $code,
        'code' => $code,
        'message' => $message
    ]);
    exit;
}


/* ==================================================================
   5. AFFICHAGE
   ================================================================== */

/**
 * Protège un texte avant de l'afficher dans le HTML (faille XSS).
 * Exemple : "<script>" devient "&lt;script&gt;" et s'affiche sans s'exécuter.
 * @param mixed $texte
 * @return string
 */
function e($texte): string
{
    return htmlspecialchars((string) $texte, ENT_QUOTES, 'UTF-8');
}

/**
 * Date de la base (2026-12-03 08:15:00) -> "03/12/2026".
 */
function formaterDate(string $date): string
{
    return date('d/m/Y', strtotime($date));
}

/**
 * Date de la base -> "08:15".
 */
function formaterHeure(string $date): string
{
    return date('H:i', strtotime($date));
}

/**
 * Date de la base -> "jeu. 03 déc." (affichage court sur les billets).
 */
function formaterJour(string $date): string
{
    $jours = ['dim.', 'lun.', 'mar.', 'mer.', 'jeu.', 'ven.', 'sam.'];
    $mois = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];

    $horodatage = strtotime($date);
    $jour = $jours[(int) date('w', $horodatage)];
    $nomMois = $mois[(int) date('n', $horodatage) - 1];

    return $jour . ' ' . date('d', $horodatage) . ' ' . $nomMois;
}

/**
 * 3.5 -> "3,50 €" ; 0 -> "Gratuit".
 */
function formaterPrix(float $prix): string
{
    if ($prix == 0) {
        return 'Gratuit';
    }
    return number_format($prix, 2, ',', ' ') . ' €';
}

/**
 * Affiche une note moyenne : "★ 4,75" ou "Pas encore noté" si aucun avis.
 * @param float|null $note Résultat de Avis::noteMoyenne()
 * @return string
 */
function formaterNote(?float $note): string
{
    if ($note === null) {
        return 'Pas encore noté';
    }
    return '★ ' . number_format($note, 2, ',', ' ');
}

/**
 * "Bilel Allali" -> "BA" (lettres affichées dans la pastille avatar).
 */
function initiales(string $nom): string
{
    $initiales = '';
    foreach (explode(' ', $nom) as $mot) {
        if ($mot !== '') {
            $initiales .= mb_strtoupper(mb_substr($mot, 0, 1));
        }
    }
    return mb_substr($initiales, 0, 2);
}

/**
 * Pastille ronde avec les initiales d'un membre.
 * La couleur est choisie à partir du nom : un même membre a toujours la même couleur.
 * @param string $nom    Le nom du membre (ex : "Bilel Allali")
 * @param string $classe Classe CSS en plus (ex : 'avatar-grand')
 * @return string Le code HTML de la pastille
 */
function avatar(string $nom, string $classe = ''): string
{
    // crc32 transforme le nom en nombre ; % 5 donne une couleur entre 0 et 4
    $couleur = abs(crc32($nom)) % 5;

    return '<span class="avatar avatar-couleur-' . $couleur . ' ' . $classe . '">' . e(initiales($nom)) . '</span>';
}

/**
 * Renvoie ' class="actif"' si le lien du menu correspond à la page affichée.
 * @param string $page Nom de la page du lien (ex : 'mes-trajets')
 */
function lienActif(string $page): string
{
    $pageCourante = 'accueil';
    if (isset($_GET['page']) && is_string($_GET['page'])) {
        $pageCourante = $_GET['page'];
    }

    // Actif si c'est la même page, ou une sous-page (ex : 'admin-avis' pour le lien 'admin')
    if ($pageCourante === $page || strpos($pageCourante, $page . '-') === 0) {
        return ' class="actif"';
    }
    return '';
}

/**
 * Affiche une note sur 5 en étoiles : 4 -> ★★★★☆
 */
function etoiles(int $note): string
{
    return str_repeat('★', $note) . '<span class="eteinte">' . str_repeat('★', 5 - $note) . '</span>';
}

/**
 * Les statuts sont enregistrés SANS accent dans la base (valeurs techniques).
 * Cette fonction donne le texte à afficher : 'Termine' -> 'Terminé'.
 */
function libelleStatut(string $statut): string
{
    $libelles = [
        'Ouvert' => 'Ouvert',
        'Complet' => 'Complet',
        'Termine' => 'Terminé',
        'En attente' => 'En attente',
        'Confirmee' => 'Confirmée',
        'Refusee' => 'Refusée'
    ];

    if (isset($libelles[$statut])) {
        return $libelles[$statut];
    }
    return $statut;
}

/**
 * Classe CSS de la pastille de statut : 'En attente' -> 'statut-en-attente'.
 */
function classeStatut(string $statut): string
{
    return 'statut-' . strtolower(str_replace(' ', '-', $statut));
}

/**
 * Date complète en toutes lettres : "mercredi 16 décembre 2026".
 */
function formaterDateLongue(string $date): string
{
    $jours = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    $mois = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

    $horodatage = strtotime($date);
    return $jours[(int) date('w', $horodatage)] . ' ' . date('j', $horodatage) . ' '
        . $mois[(int) date('n', $horodatage) - 1] . ' ' . date('Y', $horodatage);
}

/**
 * Sens du trajet par rapport au campus, affiché sur la page détail d'un trajet.
 * On regarde simplement si le mot "campus" apparaît dans l'arrivée ou le départ.
 * @return string 'Vers campus', 'Depuis campus' ou 'Hors campus'
 */
function sensTrajet(string $origine, string $destination): string
{
    if (stripos($destination, 'campus') !== false) {
        return 'Vers campus';
    }
    if (stripos($origine, 'campus') !== false) {
        return 'Depuis campus';
    }
    return 'Hors campus';
}

/**
 * Affiche le message d'erreur d'un champ de formulaire, s'il y en a un.
 * Les erreurs sont rangées par nom de champ : ['origine' => 'Le lieu de départ...'].
 * @param array  $erreurs Les erreurs renvoyées par le modèle
 * @param string $champ   Le nom du champ (ex : 'origine')
 * @return string Le HTML du message, ou '' si le champ est correct
 */
function erreurChamp(array $erreurs, string $champ): string
{
    if (!isset($erreurs[$champ])) {
        return '';
    }
    return '<p class="erreur-champ" id="erreur-' . $champ . '">' . icone('alerte', 16) . e($erreurs[$champ]) . '</p>';
}

/**
 * Classe CSS d'un bloc de champ : "champ", ou "champ champ--erreur" si le champ a une erreur
 * (le bord du champ devient alors rouge).
 */
function classeChamp(array $erreurs, string $champ): string
{
    if (isset($erreurs[$champ])) {
        return 'champ champ--erreur';
    }
    return 'champ';
}

/**
 * Attributs d'accessibilité d'un champ en erreur : le lecteur d'écran annonce
 * que le champ est invalide et lit le message d'erreur associé.
 */
function attributsErreur(array $erreurs, string $champ): string
{
    if (isset($erreurs[$champ])) {
        return ' aria-invalid="true" aria-describedby="erreur-' . $champ . '"';
    }
    return '';
}

/**
 * Petites icônes dessinées en SVG (trait de 2 px, bouts arrondis).
 * Elles sont écrites directement dans la page : aucun fichier à charger.
 * @param string $nom    Nom de l'icône (ex : 'voiture')
 * @param int    $taille Taille en pixels
 * @return string Le code SVG
 */
function icone(string $nom, int $taille = 20): string
{
    $dessins = [
        'departs'      => '<path d="M4 6h16M4 12h16M4 18h10"/>',
        'voiture'      => '<path d="M5 11l2-5h10l2 5M3 11h18v6H3zM6 17v2M18 17v2"/><path d="M7 14h1M16 14h1"/>',
        'billet'       => '<path d="M3 7h18v3a2 2 0 0 0 0 4v3H3v-3a2 2 0 0 0 0-4z"/><path d="M14 7v10" stroke-dasharray="2 2"/>',
        'etoile'       => '<path d="M12 3l2.6 5.6 6 .7-4.5 4.1 1.2 6L12 16.6 6.7 19.4l1.2-6L3.4 9.3l6-.7z"/>',
        'profil'       => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
        'plus'         => '<path d="M12 5v14M5 12h14"/>',
        'admin'        => '<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/>',
        'inverser'     => '<path d="M8 4v16M8 4L4 8M8 4l4 4M16 20V4M16 20l-4-4M16 20l4-4"/>',
        'alerte'       => '<circle cx="12" cy="12" r="9"/><path d="M12 7v6M12 16v1"/>',
        'valide'       => '<circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/>',
        'fermer'       => '<path d="M6 6l12 12M18 6L6 18"/>',
        'recherche'    => '<circle cx="11" cy="11" r="7"/><path d="M16 16l5 5"/>',
    ];

    $dessin = '';
    if (isset($dessins[$nom])) {
        $dessin = $dessins[$nom];
    }

    return '<svg width="' . $taille . '" height="' . $taille . '" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
        . ' stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $dessin . '</svg>';
}
