<?php
/**
 * Vue : noter les participants d'un trajet terminé
 * Variables : $trajet (Trajet), $participants (personnes notables, avec 'deja_note')
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var array $participants */
/** @var Trajet $trajet */
?>
<a class="retour" href="index.php?page=mes-avis">← Mes avis</a>

<div class="entete-page">
    <div>
        <p class="surtitre">Trajet terminé - n° <?= sprintf('%04d', $trajet->id) ?></p>
        <h1>Noter le trajet</h1>
        <p><?= e($trajet->origine) ?> → <?= e($trajet->destination) ?>, <?= formaterDateLongue($trajet->date_heure) ?> à <?= formaterHeure($trajet->date_heure) ?></p>
    </div>
</div>

<?php if (count($participants) === 0) { ?>
    <div class="vide">
        <img class="vide-illustration" src="images/illustration-vide.svg" alt="">
        <h3>Personne à noter</h3>
        <p>Aucun passager n'a été accepté sur ce trajet.</p>
    </div>
<?php } ?>

<div class="deux-colonnes">
    <!-- Une carte par personne à noter : les passagers pour le conducteur, le conducteur pour un passager -->
    <?php foreach ($participants as $participant) { ?>
        <section class="bloc">
            <div class="avis-tete">
                <span class="personne">
                    <?= avatar($participant['nom']) ?>
                    <a class="personne-nom" href="index.php?page=profil&amp;id=<?= $participant['id'] ?>"><?= e($participant['nom']) ?></a>
                </span>
                <?php if ($participant['deja_note'] > 0) { ?>
                    <span class="plaque statut-confirmee">Déjà noté</span>
                <?php } ?>
            </div>

            <?php
            // RÈGLE MÉTIER : une seule note par trajet et par personne
            if ($participant['deja_note'] == 0) { ?>
                <form method="post" action="index.php?page=noter-valider&amp;id=<?= $trajet->id ?>">
                    <?= champCsrf() ?>
                    <input type="hidden" name="id_cible" value="<?= $participant['id'] ?>">

                    <fieldset>
                        <legend>Note sur 5</legend>
                        <div class="choix-note">
                            <?php for ($n = 1; $n <= 5; $n++) {
                                // Un id unique par bouton radio : note-<personne>-<valeur>
                                $idChamp = 'note-' . $participant['id'] . '-' . $n; ?>
                                <input type="radio" name="note" id="<?= $idChamp ?>" value="<?= $n ?>" required<?= $n === 5 ? ' checked' : '' ?>>
                                <label for="<?= $idChamp ?>"><?= $n ?></label>
                            <?php } ?>
                        </div>
                    </fieldset>

                    <div class="champ">
                        <label for="commentaire-<?= $participant['id'] ?>">Commentaire <span class="discret">(facultatif, 500 caractères maximum)</span></label>
                        <textarea id="commentaire-<?= $participant['id'] ?>" name="commentaire" maxlength="500" placeholder="Ponctuel, sympa, conduite prudente…"></textarea>
                    </div>
                    <button class="bouton bouton--principal" type="submit">Envoyer mon avis</button>
                </form>
            <?php } else { ?>
                <p class="discret sans-marge">Vous avez déjà noté cette personne pour ce trajet.</p>
            <?php } ?>
        </section>
    <?php } ?>
</div>
