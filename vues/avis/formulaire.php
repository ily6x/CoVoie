<?php
/**
 * Vue : noter les participants d'un trajet terminé
 * Variables : $trajet (Trajet), $participants (personnes notables, avec 'deja_note')
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var Trajet $trajet */
/** @var array $participants */
?>
<div class="titre-page">
    <div>
        <h1>Noter le trajet</h1>
        <p><?= e($trajet->origine) ?> → <?= e($trajet->destination) ?> · <?= formaterDate($trajet->date_heure) ?></p>
    </div>
    <a class="bouton bouton-secondaire" href="index.php?page=mes-avis">← Mes avis</a>
</div>

<?php if (count($participants) === 0) { ?>
    <div class="vide"><b>Personne à noter</b>Aucun passager n'a été accepté sur ce trajet.</div>
<?php } ?>

<div class="grille-billets">
    <?php foreach ($participants as $participant) { ?>
        <div class="carte">
            <div class="conducteur">
                <span class="avatar"><?= e(initiales($participant['nom'])) ?></span>
                <a class="nom" href="index.php?page=profil&amp;id=<?= $participant['id'] ?>"><?= e($participant['nom']) ?></a>
                <?php if ($participant['deja_note'] > 0) { ?>
                    <span class="statut statut-confirmee">Déjà noté</span>
                <?php } ?>
            </div>

            <?php
            // RÈGLE MÉTIER : une seule note par trajet et par personne
            if ($participant['deja_note'] == 0) { ?>
                <form method="post" action="index.php?page=noter-valider&amp;id=<?= $trajet->id ?>" class="formulaire-avis">
                    <?= champCsrf() ?>
                    <input type="hidden" name="id_cible" value="<?= $participant['id'] ?>">

                    <div class="groupe">
                        <label>Note</label>
                        <div class="choix-note">
                            <?php for ($n = 1; $n <= 5; $n++) {
                                // Un id unique par bouton radio : note-<personne>-<valeur>
                                $idChamp = 'note-' . $participant['id'] . '-' . $n; ?>
                                <input type="radio" name="note" id="<?= $idChamp ?>" value="<?= $n ?>" required<?= $n === 5 ? ' checked' : '' ?>>
                                <label for="<?= $idChamp ?>"><?= $n ?> ★</label>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="groupe">
                        <label for="commentaire-<?= $participant['id'] ?>">Commentaire <span class="discret">(facultatif)</span></label>
                        <textarea id="commentaire-<?= $participant['id'] ?>" name="commentaire" maxlength="500" placeholder="Ponctuel, sympa, conduite prudente…"></textarea>
                    </div>
                    <button class="bouton" type="submit">Envoyer mon avis</button>
                </form>
            <?php } ?>
        </div>
    <?php } ?>
</div>
