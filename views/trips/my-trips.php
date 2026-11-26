<?php require 'views/layouts/header.php'; ?>

<h1>Mes trajets</h1>
<p class="sous-titre">Historique des trajets que vous avez publiés.</p>

<p>
    <a class="bouton bouton-vert" href="index.php?page=publier">Publier un trajet</a>
    <a class="bouton bouton-gris" href="index.php?page=mes-statistiques">Mes statistiques</a>
</p>

<?php if (count($enAttente) > 0) { ?>
    <div class="bloc">
        <h2><?php echo count($enAttente); ?> demande(s) attendent votre réponse</h2>
        <table>
            <tr><th>Passager</th><th>Trajet</th><th>Départ</th><th></th></tr>
            <?php foreach ($enAttente as $demande) { ?>
                <tr>
                    <td>
                        <a href="index.php?page=profil&amp;id=<?php echo $demande->idPassager; ?>">
                            <?php echo escape(nameLabel($demande->nomPassager)); ?>
                        </a>
                        <?php echo formatRating($demande->passengerRating()); ?>
                    </td>
                    <td><?php echo escape(placeLabel($demande->origine)); ?> &rarr; <?php echo escape(placeLabel($demande->destination)); ?></td>
                    <td><?php echo formatDate($demande->dateHeure); ?></td>
                    <td>
                        <a class="bouton bouton-vert bouton-petit"
                           href="index.php?page=trajet&amp;id=<?php echo $demande->idTrajet; ?>">Traiter</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
<?php } ?>

<?php if (count($trajets) === 0) { ?>
    <div class="vide">Vous n'avez publié aucun trajet pour le moment.</div>
<?php } else { ?>
    <div class="bloc">
        <table>
            <tr>
                <th>Itinéraire</th><th>Départ</th><th>Places</th>
                <th>Prix</th><th>Statut</th><th>Actions</th>
            </tr>
            <?php foreach ($trajets as $trajet) { ?>
                <tr>
                    <td><strong><?php echo escape(placeLabel($trajet->origine)); ?> &rarr; <?php echo escape(placeLabel($trajet->destination)); ?></strong></td>
                    <td><?php echo formatDate($trajet->dateHeure); ?></td>
                    <td><?php echo $trajet->occupiedSeats(); ?> / <?php echo $trajet->placesTotales; ?></td>
                    <td><?php echo formatPrice($trajet->prix); ?></td>
                    <td><span class="badge <?php echo $trajet->statusClass(); ?>"><?php echo escape(statusLabel($trajet->statut)); ?></span></td>
                    <td>
                        <a class="bouton bouton-gris bouton-petit" href="index.php?page=trajet&amp;id=<?php echo $trajet->id; ?>">Voir</a>
                        <?php if ($trajet->statut !== 'Termine' && $trajet->hasDeparted()) { ?>
                            <form class="action-inline" method="POST" action="index.php?page=cloturer&amp;id=<?php echo $trajet->id; ?>">
                                <?php echo csrfField(); ?>
                                <button type="submit" class="bouton bouton-vert bouton-petit">Clôturer</button>
                            </form>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
<?php } ?>

<?php require 'views/layouts/footer.php'; ?>
