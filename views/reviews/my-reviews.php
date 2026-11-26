<?php require 'views/layouts/header.php'; ?>

<h1>Avis et réputation</h1>
<p class="sous-titre">La notation se débloque dès qu'un trajet passe au statut "Terminé".</p>

<h2>Trajets terminés à noter</h2>

<?php if (count($lignes) === 0) { ?>
    <div class="vide">Vous n'avez aucun trajet terminé pour le moment.</div>
<?php } else { ?>
    <?php foreach ($lignes as $ligne) { ?>
        <?php
        $unTrajet     = $ligne['trajet'];
        $participants = $ligne['participants'];
        ?>
        <div class="bloc">
            <h2>
                <?php echo escape(placeLabel($unTrajet->origine)); ?> &rarr; <?php echo escape(placeLabel($unTrajet->destination)); ?>
                <span class="badge badge-termine"><?php echo formatDate($unTrajet->dateHeure); ?></span>
            </h2>

            <?php if (count($participants) === 0) { ?>
                <p class="note-vide">Personne à noter sur ce trajet.</p>
            <?php } else { ?>
                <table>
                    <tr><th>Personne</th><th>Rôle</th><th>Sa note actuelle</th><th></th></tr>
                    <?php foreach ($participants as $participant) { ?>
                        <tr>
                            <td>
                                <a href="index.php?page=profil&amp;id=<?php echo $participant['utilisateur']->id; ?>">
                                    <?php echo escape(nameLabel($participant['utilisateur']->nom)); ?>
                                </a>
                            </td>
                            <td><?php echo $participant['role']; ?></td>
                            <td><?php echo formatRating($participant['utilisateur']->noteMoyenne(), $participant['utilisateur']->nombreAvis()); ?></td>
                            <td>
                                <?php if ($participant['dejaNote']) { ?>
                                    <span class="badge badge-confirmee">Déjà noté</span>
                                <?php } else { ?>
                                    <a class="bouton bouton-vert bouton-petit"
                                       href="index.php?page=noter&amp;id=<?php echo $unTrajet->id; ?>">Noter</a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } ?>
        </div>
    <?php } ?>
<?php } ?>

<h2>Les avis que j'ai reçus</h2>

<?php if (count($avisRecus) === 0) { ?>
    <div class="vide">Vous n'avez pas encore reçu d'avis.</div>
<?php } else { ?>
    <?php foreach ($avisRecus as $unAvis) { ?>
        <div class="bloc avis">
            <div class="avis-entete">
                <strong><?php echo escape(nameLabel($unAvis->nomAuteur)); ?></strong>
                <span>
                    <span class="etoiles"><?php echo $unAvis->stars(); ?></span>
                    <span class="note-vide"><?php echo date('d/m/Y', strtotime($unAvis->date)); ?></span>
                </span>
            </div>
            <?php if ($unAvis->commentaire !== '' && $unAvis->commentaire !== null) { ?>
                <p><?php echo nl2br(escape(commentLabel($unAvis->commentaire))); ?></p>
            <?php } ?>
        </div>
    <?php } ?>
<?php } ?>

<?php require 'views/layouts/footer.php'; ?>
