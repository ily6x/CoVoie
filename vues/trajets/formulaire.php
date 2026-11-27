<?php
/**
 * Vue : formulaire de trajet (utilisé pour PUBLIER et pour MODIFIER)
 * Variables : $trajet (null = publication, objet Trajet = modification),
 *             $valeurs (valeurs des champs), $erreurs (liste de messages)
 */

// Le même formulaire sert aux deux actions : seule l'adresse d'envoi change
if ($trajet === null) {
    $action = 'index.php?page=publier-valider';
    $texteBouton = 'Publier le trajet';
} else {
    $action = 'index.php?page=modifier-valider&amp;id=' . $trajet->id;
    $texteBouton = 'Enregistrer les modifications';
}
?>
<div class="carte formulaire-centre formulaire-large">
    <h1><?= e($titre) ?></h1>
    <p class="sous-titre">
        <?php if ($trajet === null) { ?>
            Indiquez votre itinéraire : les passagers pourront demander une place.
        <?php } else { ?>
            <?= $trajet->placesOccupees() ?> place(s) déjà réservée(s) : le nombre de places ne peut pas descendre en dessous.
        <?php } ?>
    </p>

    <?php if (count($erreurs) > 0) { ?>
        <div class="message message-erreur">
            Le trajet n'a pas été enregistré :
            <ul>
                <?php foreach ($erreurs as $erreur) { ?>
                    <li><?= e($erreur) ?></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>

    <form method="post" action="<?= $action ?>">
        <?= champCsrf() ?>

        <div class="groupe">
            <label for="depart">Départ</label>
            <div class="champ-lieu">
                <i class="point"></i>
                <input type="text" id="depart" name="origine" value="<?= e($valeurs['origine']) ?>" maxlength="120" placeholder="Ex : Boulogne Jean Jaurès" required>
            </div>
        </div>
        <div class="groupe">
            <label for="arrivee">Arrivée</label>
            <div class="champ-lieu">
                <i class="point fin"></i>
                <input type="text" id="arrivee" name="destination" value="<?= e($valeurs['destination']) ?>" maxlength="120" placeholder="Ex : Campus Paris 15e" required>
            </div>
        </div>
        <div class="groupe">
            <label for="date_heure">Date et heure de départ</label>
            <input type="datetime-local" id="date_heure" name="date_heure" value="<?= e($valeurs['date_heure']) ?>" required>
        </div>
        <div class="deux-champs">
            <div class="groupe">
                <label for="places">Places proposées</label>
                <input type="number" id="places" name="places" value="<?= e($valeurs['places']) ?>" min="1" max="8" required>
            </div>
            <div class="groupe">
                <label for="prix">Prix par place (€)</label>
                <input type="number" id="prix" name="prix" value="<?= e($valeurs['prix']) ?>" min="0" max="200" step="0.5" required>
                <p class="aide">0 = trajet gratuit</p>
            </div>
        </div>

        <div class="actions">
            <button class="bouton" type="submit"><?= $texteBouton ?></button>
            <?php if ($trajet !== null) { ?>
                <a class="bouton bouton-contour" href="index.php?page=trajet&amp;id=<?= $trajet->id ?>">Annuler</a>
            <?php } ?>
        </div>
    </form>
</div>
