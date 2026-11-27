<?php
/**
 * Vue partielle : en-tête et onglets du back-office (incluse en haut de chaque page admin)
 * Variable : $onglet (nom de la page active)
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var string $titre */
/** @var string $onglet */

$liens = [
    'admin' => 'Tableau de bord',
    'admin-avis' => 'Modération des avis',
    'admin-utilisateurs' => 'Comptes',
    'admin-trajets' => 'Trajets'
];
?>
<div class="entete-page">
    <div>
        <p class="surtitre">Back-office <span class="badge-admin">ADMIN</span></p>
        <h1><?= e($titre) ?></h1>
        <p>Réservé à l'administrateur : chaque page vérifie le rôle avec protegerAdmin().</p>
    </div>
</div>

<nav class="onglets" aria-label="Back-office">
    <?php foreach ($liens as $page => $texte) { ?>
        <a href="index.php?page=<?= $page ?>"<?= $page === $onglet ? ' class="actif"' : '' ?>><?= $texte ?></a>
    <?php } ?>
</nav>
