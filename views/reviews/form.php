<?php require 'views/layouts/header.php'; ?>

<h1>Noter les participants</h1>
<p class="sous-titre">
    <?php echo escape(placeLabel($trajet->origine)); ?> &rarr; <?php echo escape(placeLabel($trajet->destination)); ?>
    - <?php echo formatDate($trajet->dateHeure); ?>
</p>

<?php if (count($participants) === 0) { ?>
    <div class="vide">Personne à noter sur ce trajet.</div>
<?php } else { ?>
    <?php foreach ($participants as $participant) { ?>
        <div class="bloc bloc-formulaire">
            <h2>
                <?php echo escape(nameLabel($participant['utilisateur']->nom)); ?>
                <span class="badge badge-termine"><?php echo $participant['role']; ?></span>
            </h2>

            <?php if ($participant['dejaNote']) { ?>
                <p class="note-vide">
                    Vous avez déjà noté cette personne pour ce trajet.
                    La règle est : une seule note par trajet.
                </p>
            <?php } else { ?>
                <form method="POST" action="index.php?page=noter-valider&amp;id=<?php echo $trajet->id; ?>">
                    <?php echo csrfField(); ?>
                    <!-- On transmet l'identifiant de la personne notee dans un champ cache -->
                    <input type="hidden" name="id_cible" value="<?php echo $participant['utilisateur']->id; ?>">

                    <p class="champ-court">
                        <label for="note<?php echo $participant['utilisateur']->id; ?>">Note</label>
                        <select id="note<?php echo $participant['utilisateur']->id; ?>" name="note" required>
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Très bien</option>
                            <option value="3">3 - Correct</option>
                            <option value="2">2 - Décevant</option>
                            <option value="1">1 - Mauvais</option>
                        </select>
                    </p>

                    <p>
                        <label for="com<?php echo $participant['utilisateur']->id; ?>">Commentaire (facultatif)</label>
                        <textarea id="com<?php echo $participant['utilisateur']->id; ?>" name="commentaire"
                                  maxlength="500" rows="3"
                                  placeholder="Ponctualité, ambiance, conduite..."></textarea>
                        <span class="aide">500 caractères maximum.</span>
                    </p>

                    <p><button type="submit" class="bouton bouton-vert">Publier mon avis</button></p>
                </form>
            <?php } ?>
        </div>
    <?php } ?>
<?php } ?>

<p><a class="bouton bouton-gris" href="index.php?page=mes-avis">Retour</a></p>

<?php require 'views/layouts/footer.php'; ?>
