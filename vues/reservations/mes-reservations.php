<?php
/**
 * Vue : "Mes réservations" (espace passager)
 * Variables : $reservations (mes demandes avec le trajet et son conducteur)
 *
 * Chaque réservation est affichée comme un BILLET : c'est ce que le passager possède.
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var array $reservations */
?>
<div class="entete-page">
    <div>
        <p class="surtitre">Espace passager</p>
        <h1>Mes réservations</h1>
        <p>Vos demandes de place et leur état : en attente, confirmée ou refusée.</p>
    </div>
    <div class="entete-actions">
        <a class="bouton bouton--secondaire" href="index.php?page=trajets"><?= icone('recherche', 18) ?>Trouver un trajet</a>
    </div>
</div>

<?php require 'vues/gabarit/onglets-espace.php'; ?>

<?php if (count($reservations) === 0) { ?>
    <div class="vide">
        <img class="vide-illustration" src="images/illustration-vide.svg" alt="">
        <h3>Aucune réservation</h3>
        <p>Cherchez un départ dans le tableau et demandez une place au conducteur.</p>
        <a class="bouton bouton--principal" href="index.php?page=trajets">Voir les départs</a>
    </div>
<?php } else { ?>
    <div class="billets">
        <?php foreach ($reservations as $r) { ?>
            <article class="billet">
                <!-- Talon du billet : heure et jour du départ -->
                <div class="billet-talon">
                    <span class="billet-heure"><?= formaterHeure($r['date_heure']) ?></span>
                    <span class="billet-jour"><?= formaterJour($r['date_heure']) ?></span>
                </div>

                <div class="billet-corps">
                    <div class="billet-haut">
                        <span class="plaque <?= classeStatut($r['statut']) ?>"><?= libelleStatut($r['statut']) ?></span>
                        <span class="billet-numero">N° R-<?= sprintf('%04d', $r['id']) ?></span>
                    </div>

                    <p class="billet-itineraire"><?= e($r['origine']) ?> <span class="fleche" aria-hidden="true">→</span><span class="visuellement-cache"> vers </span> <?= e($r['destination']) ?></p>

                    <span class="personne">
                        <?= avatar($r['nom_conducteur'], 'avatar--petit') ?>
                        <span><?= e($r['nom_conducteur']) ?> <small>Conducteur</small></span>
                    </span>

                    <div class="billet-pied">
                        <span class="prix"><?= formaterPrix((float) $r['prix']) ?></span>
                        <div class="actions">
                            <?php
                            // Seule une demande EN ATTENTE peut être annulée (confirmation demandée par le JS)
                            if ($r['statut'] === 'En attente') { ?>
                                <form class="en-ligne" method="post" action="index.php?page=annuler&amp;id=<?= $r['id'] ?>"
                                      data-confirmation="Annuler cette demande de réservation ?">
                                    <?= champCsrf() ?>
                                    <button class="bouton bouton--danger bouton--petit" type="submit">Annuler</button>
                                </form>
                            <?php } ?>
                            <?php
                            // Après un trajet terminé, le passager confirmé peut noter le conducteur
                            if ($r['statut'] === 'Confirmee' && $r['statut_trajet'] === 'Termine') { ?>
                                <a class="bouton bouton--principal bouton--petit" href="index.php?page=noter&amp;id=<?= $r['id_trajet'] ?>">Noter</a>
                            <?php } ?>
                            <a class="bouton bouton--secondaire bouton--petit" href="index.php?page=trajet&amp;id=<?= $r['id_trajet'] ?>">Voir le trajet</a>
                        </div>
                    </div>
                </div>
            </article>
        <?php } ?>
    </div>
<?php } ?>
