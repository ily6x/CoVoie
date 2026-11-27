<?php
/**
 * Vue : recherche de trajets
 * Variables : $depart, $arrivee, $date (critères saisis), $trajets (résultats)
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var string $depart */
/** @var string $arrivee */
/** @var string $date */
/** @var array $trajets */
?>
<div class="titre-page">
    <div>
        <h1>Trouver un trajet</h1>
        <p>Seuls les trajets ouverts, à venir et avec des places libres sont affichés.</p>
    </div>
</div>

<!-- Formulaire en GET : on peut partager ou recharger l'URL des résultats -->
<form class="carte recherche-ligne" method="get" action="index.php">
    <input type="hidden" name="page" value="trajets">

    <div>
        <label for="depart">Départ</label>
        <div class="champ-lieu">
            <i class="point"></i>
            <input type="text" id="depart" name="depart" value="<?= e($depart) ?>" placeholder="Toutes les villes">
        </div>
    </div>
    <div>
        <label for="arrivee">Arrivée</label>
        <div class="champ-lieu">
            <i class="point fin"></i>
            <input type="text" id="arrivee" name="arrivee" value="<?= e($arrivee) ?>" placeholder="Toutes les villes">
        </div>
    </div>
    <div>
        <label for="date">Date</label>
        <input type="date" id="date" name="date" value="<?= e($date) ?>">
    </div>
    <div class="actions">
        <button type="button" class="bouton bouton-contour" data-inverser title="Inverser départ et arrivée">⇄</button>
        <button class="bouton" type="submit">Rechercher</button>
    </div>
</form>

<div class="section">
    <h2><?= count($trajets) ?> trajet(s) trouvé(s)</h2>
    <!-- Le lien "Effacer les filtres" n'apparaît que si au moins un critère est rempli -->
    <?php if ($depart !== '' || $arrivee !== '' || $date !== '') { ?>
        <a href="index.php?page=trajets">Effacer les filtres</a>
    <?php } ?>
</div>

<!-- Aucun résultat : message d'aide, sinon la grille de billets -->
<?php if (count($trajets) === 0) { ?>
    <div class="vide">
        <b>Aucun trajet ne correspond</b>
        Essayez une autre date ou une partie du nom seulement (ex : « Boulogne »).
    </div>
<?php } else { ?>
    <div class="grille-billets">
        <?php foreach ($trajets as $t) {
            require 'vues/gabarit/carte-trajet.php';
        } ?>
    </div>
<?php } ?>
