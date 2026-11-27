<?php
/**
 * Vue : "Mes réservations" (espace passager)
 * Variables : $reservations (mes demandes avec le trajet et son conducteur)
 */
?>
<div class="titre-page">
    <div>
        <h1>Mes réservations</h1>
        <p>Vos demandes de place et leur état.</p>
    </div>
    <a class="bouton" href="index.php?page=trajets">Trouver un trajet</a>
</div>

<?php if (count($reservations) === 0) { ?>
    <div class="vide"><b>Aucune réservation</b>Cherchez un trajet et demandez une place.</div>
<?php } else { ?>
    <div class="grille-billets">
        <?php foreach ($reservations as $r) { ?>
            <article class="billet">
                <div class="billet-haut">
                    <div class="conducteur">
                        <span class="avatar"><?= e(initiales($r['nom_conducteur'])) ?></span>
                        <div>
                            <span class="nom"><?= e($r['nom_conducteur']) ?></span>
                            <small>Conducteur</small>
                        </div>
                        <span class="statut <?= classeStatut($r['statut']) ?>"><?= libelleStatut($r['statut']) ?></span>
                    </div>
                    <div class="horaire">
                        <div class="lieu">
                            <b><?= formaterHeure($r['date_heure']) ?></b>
                            <span><?= e($r['origine']) ?></span>
                        </div>
                        <div class="trait"></div>
                        <div class="lieu arrivee">
                            <b><?= formaterJour($r['date_heure']) ?></b>
                            <span><?= e($r['destination']) ?></span>
                        </div>
                    </div>
                </div>

                <div class="decoupe"></div>

                <div class="billet-bas">
                    <span class="tampon"><?= formaterPrix((float) $r['prix']) ?></span>
                    <div class="actions">
                        <?php if ($r['statut'] === 'En attente') { ?>
                            <!-- Seule une demande en attente peut être annulée -->
                            <form class="en-ligne" method="post" action="index.php?page=annuler&amp;id=<?= $r['id'] ?>"
                                  data-confirmation="Annuler cette demande de réservation ?">
                                <?= champCsrf() ?>
                                <button class="bouton bouton-danger bouton-petit" type="submit">Annuler</button>
                            </form>
                        <?php } ?>
                        <?php if ($r['statut'] === 'Confirmee' && $r['statut_trajet'] === 'Termine') { ?>
                            <a class="bouton bouton-petit" href="index.php?page=noter&amp;id=<?= $r['id_trajet'] ?>">Noter</a>
                        <?php } ?>
                        <a class="bouton bouton-secondaire bouton-petit" href="index.php?page=trajet&amp;id=<?= $r['id_trajet'] ?>">Voir</a>
                    </div>
                </div>
            </article>
        <?php } ?>
    </div>
<?php } ?>
