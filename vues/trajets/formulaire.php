<?php
/**
 * Vue : formulaire de trajet (utilisé pour PUBLIER et pour MODIFIER)
 * Variables : $trajet (null = publication, objet Trajet = modification),
 *             $valeurs (valeurs des champs), $erreurs (erreurs rangées par nom de champ)
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var Trajet|null $trajet */
/** @var string $titre */
/** @var array $erreurs */
/** @var array $valeurs */

// Le même formulaire sert aux deux actions : seule l'adresse d'envoi change
if ($trajet === null) {
    $action = 'index.php?page=publier-valider';
    $texteBouton = 'Publier le trajet';
} else {
    $action = 'index.php?page=modifier-valider&amp;id=' . $trajet->id;
    $texteBouton = 'Enregistrer les modifications';
}
?>
<?php if ($trajet !== null) { ?>
    <a class="retour" href="index.php?page=trajet&amp;id=<?= $trajet->id ?>">← Retour au trajet</a>
<?php } ?>

<div class="entete-page">
    <div>
        <p class="surtitre">Espace conducteur</p>
        <h1><?= e($titre) ?></h1>
        <p>
            <?php if ($trajet === null) { ?>
                Indiquez votre itinéraire : les passagers pourront demander une place.
            <?php } else { ?>
                Trajet n° <?= sprintf('%04d', $trajet->id) ?> - <?= $trajet->placesOccupees() ?> place(s) déjà réservée(s).
            <?php } ?>
        </p>
    </div>
</div>

<?php
// Erreur qui ne concerne pas un champ précis (ex : trajet déjà parti) : affichée en haut
if (isset($erreurs['general'])) { ?>
    <div class="message message--erreur" role="alert"><?= icone('alerte') ?><p><?= e($erreurs['general']) ?></p></div>
<?php } ?>

<div class="gabarit-formulaire">
    <!-- novalidate : on laisse PHP vérifier, pour afficher nos propres messages sous les champs -->
    <form class="bloc" method="post" action="<?= $action ?>" novalidate>
        <?= champCsrf() ?>

        <fieldset>
            <legend>Itinéraire</legend>
            <div class="arrets">
                <div class="<?= classeChamp($erreurs, 'origine') ?> champ-lieu">
                    <label for="depart">Départ</label>
                    <input type="text" id="depart" name="origine"<?= attributsErreur($erreurs, 'origine') ?> value="<?= e($valeurs['origine']) ?>" maxlength="120" placeholder="Ex : Boulogne Jean Jaurès" required>
                    <?= erreurChamp($erreurs, 'origine') ?>
                </div>
                <div class="<?= classeChamp($erreurs, 'destination') ?> champ-lieu">
                    <label for="arrivee">Arrivée</label>
                    <!-- Échange départ et arrivée (voir js/script.js) -->
                    <button type="button" class="bouton-inverser" data-inverser aria-label="Inverser départ et arrivée"><?= icone('inverser', 16) ?></button>
                    <input type="text" id="arrivee" name="destination"<?= attributsErreur($erreurs, 'destination') ?> value="<?= e($valeurs['destination']) ?>" maxlength="120" placeholder="Ex : Campus Paris 15e" required>
                    <?= erreurChamp($erreurs, 'destination') ?>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Horaire</legend>
            <div class="<?= classeChamp($erreurs, 'date_heure') ?>">
                <label for="date_heure">Date et heure de départ</label>
                <input type="datetime-local" id="date_heure" name="date_heure"<?= attributsErreur($erreurs, 'date_heure') ?> value="<?= e($valeurs['date_heure']) ?>" required>
                <?= erreurChamp($erreurs, 'date_heure') ?>
            </div>
        </fieldset>

        <fieldset>
            <legend>Places et prix</legend>
            <div class="deux-champs">
                <div class="<?= classeChamp($erreurs, 'places') ?>">
                    <label for="places">Places proposées</label>
                    <input type="number" id="places" name="places"<?= attributsErreur($erreurs, 'places') ?> value="<?= e($valeurs['places']) ?>" min="1" max="8" required>
                    <?= erreurChamp($erreurs, 'places') ?>
                </div>
                <div class="<?= classeChamp($erreurs, 'prix') ?>">
                    <label for="prix">Prix par place (€)</label>
                    <input type="number" id="prix" name="prix"<?= attributsErreur($erreurs, 'prix') ?> value="<?= e($valeurs['prix']) ?>" min="0" max="200" step="0.5" required>
                    <?= erreurChamp($erreurs, 'prix') ?>
                </div>
            </div>
        </fieldset>

        <div class="actions">
            <button class="bouton bouton--principal" type="submit"><?= $texteBouton ?></button>
            <?php if ($trajet !== null) { ?>
                <a class="bouton bouton--secondaire" href="index.php?page=trajet&amp;id=<?= $trajet->id ?>">Annuler</a>
            <?php } ?>
        </div>
    </form>

    <!-- Les règles vérifiées par Trajet::valider(), rappelées à l'utilisateur -->
    <aside class="bloc">
        <p class="surtitre">À savoir</p>
        <ul class="avis-liste">
            <li><b>Départ et arrivée différents</b><span class="avis-contexte">Entre 2 et 120 caractères chacun.</span></li>
            <li><b>Un départ dans le futur</b><span class="avis-contexte">Le trajet passe « Terminé » tout seul à cette heure-là.</span></li>
            <li><b>De 1 à 8 places</b><span class="avis-contexte">
                <?php if ($trajet !== null) { ?>
                    Pas moins que les <?= $trajet->placesOccupees() ?> place(s) déjà réservée(s).
                <?php } else { ?>
                    Une place est retirée à chaque demande que vous acceptez.
                <?php } ?>
            </span></li>
            <li><b>Prix de 0 à 200 €</b><span class="avis-contexte">0 € = trajet gratuit.</span></li>
        </ul>
    </aside>
</div>
