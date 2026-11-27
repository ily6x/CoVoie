<?php
/**
 * ------------------------------------------------------------------
 *  Gabarit : header (haut de page) commun à toutes les pages
 * ------------------------------------------------------------------
 *  Bandeau blanc : logo, liens de navigation, boutons de compte.
 *  Le menu change selon que le visiteur est connecté, et s'il est admin.
 *  Sur téléphone, les liens passent dans une barre d'onglets en bas.
 * ------------------------------------------------------------------
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var string $titre */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titre) ?> - <?= NOM_APPLICATION ?></title>
    <link rel="icon" href="images/logo.svg" type="image/svg+xml">
    <!-- ?v=... change à chaque modification du fichier : le navigateur recharge alors le CSS au lieu de garder l'ancien en cache -->
    <!-- Préchargement des polices principales : le texte ne s'affiche pas d'abord en Arial -->
    <link rel="preload" href="fonts/poppins-latin-700-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="fonts/nunito-sans-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime('css/style.css') ?>">
</head>
<body<?= estConnecte() ? ' class="connecte"' : '' ?>>

<header class="header">
    <div class="conteneur header-interieur">
        <a class="marque" href="index.php">
            <img src="images/logo.svg" alt="">
            <span>Co<em>Voie</em></span>
        </a>

        <nav class="navigation" aria-label="Navigation principale">
            <a href="index.php?page=trajets"<?= lienActif('trajets') ?>>Rechercher</a>

            <?php if (estConnecte()) { ?>
                <a href="index.php?page=mes-trajets"<?= lienActif('mes-trajets') ?>>Mes trajets</a>
                <a href="index.php?page=mes-reservations"<?= lienActif('mes-reservations') ?>>Mes réservations</a>
                <a href="index.php?page=mes-avis"<?= lienActif('mes-avis') ?>>Mes avis</a>
                <?php if (estAdmin()) { ?>
                    <a href="index.php?page=admin"<?= lienActif('admin') ?>>Admin</a>
                <?php } ?>
            <?php } ?>
        </nav>

        <div class="header-actions">
            <?php if (estConnecte()) { ?>
                <!-- Action principale du site : publier un trajet (bouton orange) -->
                <a class="bouton bouton--principal bouton--petit" href="index.php?page=publier" aria-label="Publier un trajet">
                    <?= icone('plus', 18) ?><span>Publier un trajet</span>
                </a>
                <a class="moi" href="index.php?page=profil" title="Mon profil">
                    <?= avatar(nomConnecte(), 'avatar--petit') ?>
                    <span><?= e(nomConnecte()) ?></span>
                </a>
                <!-- La déconnexion modifie la session : formulaire POST + jeton CSRF -->
                <form method="post" action="index.php?page=deconnexion">
                    <?= champCsrf() ?>
                    <button class="lien-header" type="submit">Déconnexion</button>
                </form>
            <?php } else { ?>
                <a class="lien-header" href="index.php?page=connexion">Connexion</a>
                <a class="bouton bouton--sombre bouton--petit" href="index.php?page=inscription">Créer un compte</a>
            <?php } ?>
        </div>
    </div>
</header>

<?php
// Barre d'onglets en bas de l'écran : affichée seulement sur téléphone (voir le CSS, section 7)
if (estConnecte()) { ?>
    <nav class="barre-mobile" aria-label="Navigation mobile">
        <a href="index.php?page=trajets"<?= lienActif('trajets') ?>><?= icone('departs') ?>Départs</a>
        <a href="index.php?page=mes-trajets"<?= lienActif('mes-trajets') ?>><?= icone('voiture') ?>Trajets</a>
        <a href="index.php?page=mes-reservations"<?= lienActif('mes-reservations') ?>><?= icone('billet') ?>Réservations</a>
        <a href="index.php?page=mes-avis"<?= lienActif('mes-avis') ?>><?= icone('etoile') ?>Avis</a>
        <?php if (estAdmin()) { ?>
            <a href="index.php?page=admin"<?= lienActif('admin') ?>><?= icone('admin') ?>Admin</a>
        <?php } else { ?>
            <a href="index.php?page=profil"<?= lienActif('profil') ?>><?= icone('profil') ?>Profil</a>
        <?php } ?>
    </nav>
<?php } ?>

<?php
// Messages "flash" laissés par l'action précédente (succès en vert, erreur en rouge)
$messages = lireMessages();
if (count($messages) > 0) { ?>
    <div class="conteneur messages">
        <?php foreach ($messages as $message) {
            // role="alert" pour une erreur : le lecteur d'écran l'annonce tout de suite
            $role = 'status';
            $icone = 'valide';
            if ($message['type'] === 'erreur') {
                $role = 'alert';
                $icone = 'alerte';
            } ?>
            <div class="message message--<?= e($message['type']) ?>" role="<?= $role ?>">
                <?= icone($icone) ?>
                <p><?= e($message['texte']) ?></p>
                <button class="message-fermer" type="button" aria-label="Fermer le message" data-fermer><?= icone('fermer', 16) ?></button>
            </div>
        <?php } ?>
    </div>
<?php } ?>

<main class="conteneur">
