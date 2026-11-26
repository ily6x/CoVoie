<?php require 'views/layouts/header.php'; ?>

<h1><?php echo escape(placeLabel($trajet->origine)); ?> <span class="fleche">&rarr;</span> <?php echo escape(placeLabel($trajet->destination)); ?></h1>
<p class="sous-titre">
    <span class="badge <?php echo $trajet->statusClass(); ?>"><?php echo escape(statusLabel($trajet->statut)); ?></span>
    <?php echo formatDate($trajet->dateHeure); ?>
</p>

<div class="deux-colonnes">
    <div>
        <div class="bloc">
            <h2>Détails du trajet</h2>
            <table>
                <tr><th>Départ</th><td><?php echo escape(placeLabel($trajet->origine)); ?></td></tr>
                <tr><th>Arrivée</th><td><?php echo escape(placeLabel($trajet->destination)); ?></td></tr>
                <tr><th>Date et heure</th><td><?php echo formatDate($trajet->dateHeure); ?></td></tr>
                <tr><th>Places</th><td><?php echo $trajet->placesRestantes; ?> restante(s) sur <?php echo $trajet->placesTotales; ?></td></tr>
                <tr><th>Prix indicatif</th><td><?php echo formatPrice($trajet->prix); ?></td></tr>
            </table>
        </div>

        <?php
        // CONTROLE D'ACCES : cette partie n'est affichee qu'au conducteur.
        // Le contrôleur a déjà vérifié : ici on ne fait qu'afficher.
        if ($jeSuisLeConducteur) {
        ?>
            <div class="bloc">
                <h2>Demandes de réservation</h2>
                <p class="sous-titre">Accepter une demande retire une place au compteur.</p>

                <?php if (count($reservations) === 0) { ?>
                    <div class="vide">Aucune demande pour le moment.</div>
                <?php } else { ?>
                    <table>
                        <tr>
                            <th>Passager</th>
                            <th>Note moyenne</th>
                            <th>Demande le</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($reservations as $reservation) { ?>
                            <tr>
                                <td>
                                    <a href="index.php?page=profil&amp;id=<?php echo $reservation->idPassager; ?>">
                                        <?php echo escape(nameLabel($reservation->nomPassager)); ?>
                                    </a>
                                </td>
                                <td><?php echo formatRating($reservation->passengerRating()); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($reservation->dateReservation)); ?></td>
                                <td><span class="badge <?php echo $reservation->statusClass(); ?>"><?php echo escape(statusLabel($reservation->statut)); ?></span></td>
                                <td>
                                    <?php if ($reservation->statut === 'En attente' && !$trajet->hasDeparted() && $trajet->statut !== 'Termine') { ?>
                                        <?php if ($trajet->placesRestantes > 0) { ?>
                                            <form class="action-inline" method="POST" action="index.php?page=accepter&amp;id=<?php echo $trajet->id; ?>&amp;réservation=<?php echo $reservation->id; ?>">
                                                <?php echo csrfField(); ?>
                                                <button type="submit" class="bouton bouton-vert bouton-petit">Accepter</button>
                                            </form>
                                        <?php } ?>
                                        <form class="action-inline" method="POST" action="index.php?page=refuser&amp;id=<?php echo $trajet->id; ?>&amp;réservation=<?php echo $reservation->id; ?>">
                                            <?php echo csrfField(); ?>
                                            <button type="submit" class="bouton bouton-rouge bouton-petit">Refuser</button>
                                        </form>
                                    <?php } else { ?>
                                        <span class="note-vide">Traitée</span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

    <div>
        <div class="bloc">
            <h2>Conducteur</h2>
            <p>
                <a href="index.php?page=profil&amp;id=<?php echo $trajet->idConducteur; ?>">
                    <strong><?php echo escape(nameLabel($trajet->nomConducteur)); ?></strong>
                </a>
            </p>
            <p><?php echo formatRating(Avis::noteMoyenne($trajet->idConducteur), Avis::nombreAvis($trajet->idConducteur)); ?></p>
        </div>

        <div class="bloc">
            <?php if (!isLoggedIn()) { ?>
                <h2>Réserver</h2>
                <p>Connectez-vous pour réserver une place.</p>
                <a class="bouton bouton-vert" href="index.php?page=connexion">Se connecter</a>

            <?php } elseif ($jeSuisLeConducteur) { ?>
                <h2>Gérer mon trajet</h2>
                <p>
                    <?php if (!$trajet->hasDeparted() && $trajet->statut !== 'Termine') { ?>
                    <a class="bouton bouton-gris bouton-petit" href="index.php?page=modifier&amp;id=<?php echo $trajet->id; ?>">Modifier</a>
                    <?php } ?>

                    <?php if ($trajet->statut !== 'Termine' && $trajet->hasDeparted()) { ?>
                        <form class="action-inline" method="POST" action="index.php?page=cloturer&amp;id=<?php echo $trajet->id; ?>">
                            <?php echo csrfField(); ?>
                            <button type="submit" class="bouton bouton-vert bouton-petit">Clôturer</button>
                        </form>
                    <?php } ?>

                    <?php if ($trajet->statut === 'Termine') { ?>
                        <a class="bouton bouton-vert bouton-petit" href="index.php?page=noter&amp;id=<?php echo $trajet->id; ?>">Noter les passagers</a>
                    <?php } ?>

                    <form class="action-inline" method="POST" action="index.php?page=supprimer-trajet&amp;id=<?php echo $trajet->id; ?>" onsubmit="return confirm('Supprimer définitivement ce trajet ?');">
                        <?php echo csrfField(); ?>
                        <button type="submit" class="bouton bouton-rouge bouton-petit">Supprimer</button>
                    </form>
                </p>

            <?php } elseif ($dejaDemande) { ?>
                <h2>Demande envoyée</h2>
                <p class="sous-titre">Vous avez déjà demandé une place sur ce trajet.</p>
                <a class="bouton bouton-gris" href="index.php?page=mes-reservations">Voir mes réservations</a>

            <?php } elseif ($trajet->statut !== 'Ouvert' || $trajet->placesRestantes <= 0 || $trajet->hasDeparted()) { ?>
                <h2>Réservation impossible</h2>
                <p class="sous-titre">Ce trajet n'accepté plus de réservation.</p>

            <?php } else { ?>
                <h2>Réserver une place</h2>
                <p class="sous-titre"><?php echo $trajet->placesRestantes; ?> place(s) - <?php echo formatPrice($trajet->prix); ?></p>
                <form class="action-inline" method="POST" action="index.php?page=reserver&amp;id=<?php echo $trajet->id; ?>">
                    <?php echo csrfField(); ?>
                    <button type="submit" class="bouton bouton-vert">Envoyer une demande</button>
                </form>
            <?php } ?>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
