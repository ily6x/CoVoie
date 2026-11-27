<?php
/**
 * ------------------------------------------------------------------
 *  Gabarit : onglets de l'espace personnel
 * ------------------------------------------------------------------
 *  Affichés sous le titre des pages "Mes trajets", "Mes statistiques",
 *  "Mes réservations", "Mes avis" et "Mon profil" pour passer de l'une
 *  à l'autre. L'onglet de la page affichée est souligné en orange.
 * ------------------------------------------------------------------
 */
?>
<nav class="onglets" aria-label="Mon espace">
    <a href="index.php?page=mes-trajets"<?= lienActif('mes-trajets') ?>>Mes trajets</a>
    <a href="index.php?page=mes-statistiques"<?= lienActif('mes-statistiques') ?>>Statistiques</a>
    <a href="index.php?page=mes-reservations"<?= lienActif('mes-reservations') ?>>Mes réservations</a>
    <a href="index.php?page=mes-avis"<?= lienActif('mes-avis') ?>>Mes avis</a>
    <a href="index.php?page=profil"<?= lienActif('profil') ?>>Mon profil</a>
</nav>
