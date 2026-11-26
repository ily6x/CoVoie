<?php
/**
 * Petit bloc reutilisable affichant un trajet.
 * On l'inclut dans une boucle : la variable $trajet doit exister.
 */
?>
<a class="carte-trajet" href="index.php?page=trajet&amp;id=<?php echo $trajet->id; ?>">
    <div class="trajet-villes-bloc">
        <div class="trajet-villes">
            <?php echo escape(placeLabel($trajet->origine)); ?>
            <span class="fleche">&rarr;</span>
            <?php echo escape(placeLabel($trajet->destination)); ?>
            <span class="badge <?php echo $trajet->statusClass(); ?>"><?php echo escape(statusLabel($trajet->statut)); ?></span>
        </div>
        <div class="trajet-infos">
            <span><?php echo formatDate($trajet->dateHeure); ?></span>
            <span>Conducteur : <?php echo escape(nameLabel($trajet->nomConducteur)); ?></span>
            <span><?php echo formatRating(Avis::noteMoyenne($trajet->idConducteur)); ?></span>
        </div>
    </div>

    <div class="trajet-places">
        <strong><?php echo $trajet->placesRestantes; ?></strong>
        place<?php echo ($trajet->placesRestantes > 1) ? 's' : ''; ?>
    </div>

    <div class="trajet-prix"><?php echo formatPrice($trajet->prix); ?></div>
</a>
