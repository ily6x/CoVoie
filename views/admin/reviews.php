<?php require 'views/layouts/header.php'; ?>

<h1>Modération des avis</h1>
<p class="sous-titre">
    Supprimer un avis recalcule automatiquement la note moyenne de la personne notée,
    parce que la moyenne n'est jamais stockee : elle est calculee a chaque affichage.
</p>

<?php if (count($avis) === 0) { ?>
    <div class="vide">Aucun avis sur la plateforme.</div>
<?php } else { ?>
    <div class="bloc">
        <table>
            <tr>
                <th>Auteur</th><th>Personne notée</th><th>Trajet</th>
                <th>Note</th><th>Commentaire</th><th>Date</th><th></th>
            </tr>
            <?php foreach ($avis as $unAvis) { ?>
                <tr>
                    <td>
                        <a href="index.php?page=profil&amp;id=<?php echo $unAvis->idAuteur; ?>">
                            <?php echo escape(nameLabel($unAvis->nomAuteur)); ?>
                        </a>
                    </td>
                    <td>
                        <a href="index.php?page=profil&amp;id=<?php echo $unAvis->idCible; ?>">
                            <?php echo escape(nameLabel($unAvis->nomCible)); ?>
                        </a>
                    </td>
                    <td><?php echo escape(placeLabel($unAvis->origine)); ?> &rarr; <?php echo escape(placeLabel($unAvis->destination)); ?></td>
                    <td><span class="etoiles"><?php echo $unAvis->stars(); ?></span></td>
                    <td><?php echo escape(commentLabel($unAvis->commentaire)); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($unAvis->date)); ?></td>
                    <td>
                        <form class="action-inline" method="POST" action="index.php?page=admin-supprimer-avis&amp;id=<?php echo $unAvis->id; ?>" onsubmit="return confirm('Supprimer cet avis ?');">
                            <?php echo csrfField(); ?>
                            <button type="submit" class="bouton bouton-rouge bouton-petit">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
<?php } ?>

<?php require 'views/layouts/footer.php'; ?>
