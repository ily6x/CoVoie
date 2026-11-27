<?php
/**
 * Vue : recherche de trajets (tous les trajets réservables, en cartes)
 * Variables : $depart, $arrivee, $date (critères saisis), $trajets (résultats)
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var string $depart */
/** @var string $arrivee */
/** @var string $date */
/** @var array $trajets */
?>
<div class="entete-page">
    <div>
        <p class="surtitre">Rechercher</p>
        <h1>Trouver un trajet</h1>
        <p>Seuls les trajets ouverts, à venir et avec des places libres sont affichés.</p>
    </div>
</div>

<!-- Formulaire en GET : on peut partager ou recharger l'URL des résultats -->
<form class="recherche recherche--page" method="get" action="index.php">
    <input type="hidden" name="page" value="trajets">

    <div class="champ">
        <label for="depart">Départ</label>
        <input type="text" id="depart" name="depart" value="<?= e($depart) ?>" placeholder="Toutes les villes">
    </div>
    <!-- Échange départ et arrivée (voir js/script.js) -->
    <button type="button" class="bouton-inverser" data-inverser aria-label="Inverser départ et arrivée"><?= icone('inverser', 18) ?></button>
    <div class="champ">
        <label for="arrivee">Arrivée</label>
        <input type="text" id="arrivee" name="arrivee" value="<?= e($arrivee) ?>" placeholder="Toutes les villes">
    </div>
    <div class="champ champ-date">
        <label for="date">Date</label>
        <input type="date" id="date" name="date" value="<?= e($date) ?>">
    </div>
    <button class="bouton bouton--principal" type="submit"><?= icone('recherche', 18) ?>Rechercher</button>
</form>

<div class="section-titre">
    <h2><?= count($trajets) ?> départ<?= count($trajets) > 1 ? 's' : '' ?></h2>
    <!-- Le lien "Effacer les filtres" n'apparaît que si au moins un critère est rempli -->
    <?php if ($depart !== '' || $arrivee !== '' || $date !== '') { ?>
        <a href="index.php?page=trajets">Effacer les filtres</a>
    <?php } ?>
</div>

<?php if (count($trajets) === 0) { ?>
    <!-- Aucun résultat : message d'aide avec une action -->
    <div class="vide">
        <img class="vide-illustration" src="images/illustration-vide.svg" alt="">
        <h3>Aucun départ ne correspond</h3>
        <p>Essayez une autre date, ou seulement une partie du nom (ex : « Boulogne »).</p>
        <a class="bouton bouton--secondaire" href="index.php?page=trajets">Voir tous les départs</a>
    </div>
<?php } else {
    // Paramètres du gabarit : cartes avec le conducteur
    $modeConducteur = false;
    require 'vues/gabarit/cartes-trajets.php';
} ?>
