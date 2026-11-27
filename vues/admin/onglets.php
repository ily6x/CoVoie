<?php
/**
 * Vue partielle : en-tête et onglets du back-office (inclus en haut de chaque page admin)
 * Variable : $onglet (nom de la page active)
 */
$liens = [
    'admin' => 'Tableau de bord',
    'admin-avis' => 'Modération des avis',
    'admin-utilisateurs' => 'Comptes',
    'admin-trajets' => 'Trajets'
];
?>
<div class="titre-page">
    <div>
        <h1><?= e($titre) ?></h1>
        <p>Espace administrateur <span class="badge-admin">ADMIN</span></p>
    </div>
</div>

<nav class="onglets">
    <?php foreach ($liens as $page => $texte) { ?>
        <a href="index.php?page=<?= $page ?>"<?= $page === $onglet ? ' class="actif"' : '' ?>><?= $texte ?></a>
    <?php } ?>
</nav>
