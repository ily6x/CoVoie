<?php
/**
 * ------------------------------------------------------------------
 *  Gabarit : en-tête commun à toutes les pages
 * ------------------------------------------------------------------
 *  Variables utilisées : $titre (titre de l'onglet du navigateur)
 *  Le menu change selon que le visiteur est connecté, et s'il est admin.
 * ------------------------------------------------------------------
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titre) ?> · <?= NOM_APPLICATION ?></title>
    <link rel="icon" href="images/logo.svg" type="image/svg+xml">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="entete">
    <div class="conteneur">
        <a class="marque" href="index.php">
            <img src="images/logo.svg" alt="">
            <span>Co<em>Voie</em></span>
        </a>

        <!-- Bouton visible seulement sur téléphone : ouvre / ferme le menu -->
        <button class="bouton-menu" type="button" aria-label="Ouvrir le menu" aria-controls="menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

        <nav class="menu" id="menu">
            <a href="index.php?page=trajets"<?= lienActif('trajets') ?>>Trouver un trajet</a>

            <?php if (estConnecte()) { ?>
                <a href="index.php?page=publier"<?= lienActif('publier') ?>>Publier</a>
                <a href="index.php?page=mes-trajets"<?= lienActif('mes-trajets') ?>>Mes trajets</a>
                <a href="index.php?page=mes-reservations"<?= lienActif('mes-reservations') ?>>Mes réservations</a>
                <a href="index.php?page=mes-avis"<?= lienActif('mes-avis') ?>>Mes avis</a>
                <?php if (estAdmin()) { ?>
                    <a href="index.php?page=admin"<?= lienActif('admin') ?>>Admin</a>
                <?php } ?>
            <?php } ?>

            <div class="droite">
                <?php if (estConnecte()) { ?>
                    <a class="moi" href="index.php?page=profil">
                        <span class="avatar"><?= e(initiales(nomConnecte())) ?></span>
                        <?= e(nomConnecte()) ?>
                    </a>
                    <!-- La déconnexion modifie la session : formulaire POST + jeton CSRF -->
                    <form method="post" action="index.php?page=deconnexion">
                        <?= champCsrf() ?>
                        <button class="bouton bouton-contour bouton-petit" type="submit">Déconnexion</button>
                    </form>
                <?php } else { ?>
                    <a href="index.php?page=connexion"<?= lienActif('connexion') ?>>Connexion</a>
                    <a class="bouton bouton-petit" href="index.php?page=inscription">Créer un compte</a>
                <?php } ?>
            </div>
        </nav>
    </div>
</header>

<main class="conteneur">

<?php
// Messages "flash" laissés par l'action précédente (succès en vert, erreur en rouge)
foreach (lireMessages() as $message) { ?>
    <div class="message message-<?= e($message['type']) ?>"><?= e($message['texte']) ?></div>
<?php } ?>
