<?php require 'views/layouts/header.php'; ?>

<h1>Mes réservations</h1>
<p class="sous-titre">Suivi de chaque demande : En attente, Confirmée ou Refusée.</p>

<?php if (count($reservations) === 0) { ?>
    <div class="vide">
        <p>Vous n'avez envoyé aucune demande.</p>
        <a class="bouton bouton-vert" href="index.php?page=trajets">Rechercher un trajet</a>
    </div>
<?php } else { ?>
    <div class="bloc">
        <table>
            <tr>
                <th>Trajet</th><th>Départ</th><th>Conducteur</th>
                <th>Demande le</th><th>Statut</th><th>Actions</th>
            </tr>
            <?php foreach ($reservations as $reservation) { ?>
                <tr>
                    <td><strong><?php echo escape(placeLabel($reservation->origine)); ?> &rarr; <?php echo escape(placeLabel($reservation->destination)); ?></strong></td>
                    <td><?php echo formatDate($reservation->dateHeure); ?></td>
                    <td>
                        <a href="index.php?page=profil&amp;id=<?php echo $reservation->idConducteur; ?>">
                            <?php echo escape(nameLabel($reservation->nomConducteur)); ?>
                        </a>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($reservation->dateReservation)); ?></td>
                    <td><span class="badge <?php echo $reservation->statusClass(); ?>"><?php echo escape(statusLabel($reservation->statut)); ?></span></td>
                    <td>
                        <a class="bouton bouton-gris bouton-petit"
                           href="index.php?page=trajet&amp;id=<?php echo $reservation->idTrajet; ?>">Voir</a>

                        <?php if ($reservation->statut === 'En attente') { ?>
                            <form class="action-inline" method="POST" action="index.php?page=annuler&amp;id=<?php echo $reservation->id; ?>">
                                <?php echo csrfField(); ?>
                                <button type="submit" class="bouton bouton-rouge bouton-petit">Annuler</button>
                            </form>
                        <?php } ?>

                        <?php
                        // Le bouton "Noter" n'apparait que si la reservation est acceptee
                        // ET que le trajet est termine : c'est le lien entre le module B et le module C.
                        if ($reservation->statut === 'Confirmee' && $reservation->statutTrajet === 'Termine') {
                        ?>
                            <a class="bouton bouton-vert bouton-petit"
                               href="index.php?page=noter&amp;id=<?php echo $reservation->idTrajet; ?>">Noter</a>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
<?php } ?>

<?php require 'views/layouts/footer.php'; ?>
