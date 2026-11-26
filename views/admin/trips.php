<?php require 'views/layouts/header.php'; ?>

<h1>Tous les trajets</h1>
<p class="sous-titre">Vue globale de la plateforme.</p>

<div class="bloc">
    <table>
        <tr>
            <th>Itinéraire</th><th>Conducteur</th><th>Départ</th>
            <th>Remplissage</th><th>Statut</th><th></th>
        </tr>
        <?php foreach ($trajets as $trajet) { ?>
            <tr>
                <td><strong><?php echo escape(placeLabel($trajet->origine)); ?> &rarr; <?php echo escape(placeLabel($trajet->destination)); ?></strong></td>
                <td>
                    <a href="index.php?page=profil&amp;id=<?php echo $trajet->idConducteur; ?>">
                        <?php echo escape(nameLabel($trajet->nomConducteur)); ?>
                    </a>
                </td>
                <td><?php echo formatDate($trajet->dateHeure); ?></td>
                <td><?php echo $trajet->occupiedSeats(); ?> / <?php echo $trajet->placesTotales; ?></td>
                <td><span class="badge <?php echo $trajet->statusClass(); ?>"><?php echo escape(statusLabel($trajet->statut)); ?></span></td>
                <td>
                    <a class="bouton bouton-gris bouton-petit"
                       href="index.php?page=trajet&amp;id=<?php echo $trajet->id; ?>">Voir</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

<?php require 'views/layouts/footer.php'; ?>
