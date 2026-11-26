<?php
require 'views/layouts/header.php';

// Ce meme formulaire sert a CREER et a MODIFIER un trajet.
// Si $trajet vaut null, on est en creation.
$modification = ($trajet !== null);

if ($modification) {
    $action = 'index.php?page=modifier-valider&id=' . $trajet->id;
} else {
    $action = 'index.php?page=publier-valider';
}
?>

<div class="bloc bloc-formulaire">
    <h1><?php echo $modification ? 'Modifier le trajet' : 'Publier un trajet'; ?></h1>
    <p class="sous-titre">Les places restantes sont initialisées au nombre de places totales.</p>

    <form method="POST" action="<?php echo escape($action); ?>">
        <?php echo csrfField(); ?>
        <div class="deux-champs">
            <p>
                <label for="origine">Ville de départ</label>
                <input type="text" id="origine" name="origine" required
                       value="<?php echo $modification ? escape(placeLabel($trajet->origine)) : ''; ?>"
                       placeholder="Campus Nord">
            </p>
            <p>
                <label for="destination">Ville d'arrivée</label>
                <input type="text" id="destination" name="destination" required
                       value="<?php echo $modification ? escape(placeLabel($trajet->destination)) : ''; ?>"
                       placeholder="Gare Centrale">
            </p>
        </div>

        <div class="deux-champs">
            <p>
                <label for="date_heure">Date et heure du départ</label>
                <input type="datetime-local" id="date_heure" name="date_heure" required
                       value="<?php echo $modification ? date('Y-m-d\TH:i', strtotime($trajet->dateHeure)) : ''; ?>">
            </p>
            <p>
                <label for="places">Nombre de places</label>
                <input type="number" id="places" name="places" min="1" max="8" required
                       value="<?php echo $modification ? $trajet->placesTotales : '3'; ?>">
                <?php if ($modification) { ?>
                    <span class="aide"><?php echo $trajet->occupiedSeats(); ?> place(s) déjà acceptée(s).</span>
                <?php } ?>
            </p>
        </div>

        <p class="champ-court">
            <label for="prix">Prix indicatif (en euros)</label>
            <input type="number" id="prix" name="prix" step="0.5" min="0" max="200" required
                   value="<?php echo $modification ? number_format($trajet->prix, 2, '.', '') : '5'; ?>">
            <span class="aide">Participation aux frais, pas un bénéfice.</span>
        </p>

        <p>
            <button type="submit" class="bouton bouton-vert">
                <?php echo $modification ? 'Enregistrer' : 'Publier le trajet'; ?>
            </button>
            <a class="bouton bouton-gris"
               href="<?php echo $modification ? 'index.php?page=trajet&amp;id=' . $trajet->id : 'index.php?page=mes-trajets'; ?>">Annuler</a>
        </p>
    </form>
</div>

<?php require 'views/layouts/footer.php'; ?>
