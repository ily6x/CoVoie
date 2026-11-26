<?php
/**
 * EN-TETE COMMUN A TOUTES LES PAGES.
 * Inclus au debut de chaque vue (cours "La Modularite et les Includes").
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($titre); ?> - <?php echo NOM_APPLICATION; ?></title>
    <link rel="stylesheet" href="css/style.css?v=urban-2">
    <script src="js/design.js?v=urban-2" defer></script>
</head>
<body class="<?php echo ($titre === 'Accueil') ? 'page-accueil' : 'page-interieure'; ?>">

<header>
    <div class="conteneur entete">
        <a class="logo" href="index.php?page=accueil">
            <span class="logo-carre">CC</span>
            CoVoiturage <strong>Campus</strong>
        </a>

        <nav>
            <a href="index.php?page=trajets">Rechercher</a>
            <?php if (empty($serviceIndisponible) && isLoggedIn()) { ?>
                <a href="index.php?page=publier">Publier</a>
                <a href="index.php?page=mes-trajets">Mes trajets</a>
                <a href="index.php?page=mes-reservations">Mes réservations</a>
                <a href="index.php?page=mes-avis">Avis</a>
                <?php if (isAdmin()) { ?>
                    <a class="lien-admin" href="index.php?page=admin">Admin</a>
                <?php } ?>
            <?php } ?>
        </nav>

        <div class="entete-compte">
            <?php if (empty($serviceIndisponible) && isLoggedIn()) { ?>
                <a href="index.php?page=profil&amp;id=<?php echo currentUserId(); ?>">
                    <?php echo escape(nameLabel(currentUserName())); ?>
                </a>
                <form class="action-inline" method="POST" action="index.php?page=deconnexion">
                    <?php echo csrfField(); ?>
                    <button type="submit" class="bouton bouton-gris">Déconnexion</button>
                </form>
            <?php } else { ?>
                <a class="bouton bouton-gris" href="index.php?page=connexion">Connexion</a>
                <a class="bouton bouton-vert" href="index.php?page=inscription">Inscription</a>
            <?php } ?>
        </div>
    </div>
</header>

<main class="conteneur">

<?php
// --- Affichage des messages de succès ou d'erreur
$messages = getFlashMessages();

foreach ($messages as $message) {
    echo '<div class="message message-' . $message['type'] . '">'
       . escape($message['texte'])
       . '</div>';
}
?>
