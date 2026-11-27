<?php
/**
 * Vue : "Mes trajets" (espace conducteur)
 * Variables : $demandes (demandes en attente sur mes trajets à venir), $trajets (tous mes trajets)
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var array $demandes */
/** @var array $trajets */
?>
<div class="titre-page">
    <div>
        <h1>Mes trajets</h1>
        <p>Les trajets que vous conduisez et les demandes à traiter.</p>
    </div>
    <div class="actions">
        <a class="bouton bouton-secondaire" href="index.php?page=mes-statistiques">Mes statistiques</a>
        <a class="bouton" href="index.php?page=publier">+ Publier un trajet</a>
    </div>
</div>

<!-- ===== Demandes en attente ===== -->
<!-- Cet encart n'apparaît que s'il y a des demandes en attente sur des trajets à venir -->
<?php if (count($demandes) > 0) { ?>
    <div class="carte">
        <h2>À traiter <span class="statut statut-en-attente"><?= count($demandes) ?> demande(s)</span></h2>
        <ul class="liste">
            <?php foreach ($demandes as $demande) { ?>
                <li>
                    <?= avatar($demande['nom_passager']) ?>
                    <div class="infos-personne">
                        <b><a href="index.php?page=profil&amp;id=<?= $demande['id_passager'] ?>"><?= e($demande['nom_passager']) ?></a></b>
                        <small>
                            <a href="index.php?page=trajet&amp;id=<?= $demande['id_trajet'] ?>"><?= e($demande['origine']) ?> → <?= e($demande['destination']) ?></a>
                            - <?= formaterJour($demande['date_heure']) ?> à <?= formaterHeure($demande['date_heure']) ?>
                            - <?= $demande['places_restantes'] ?> place(s) libre(s)
                        </small>
                    </div>
                    <div class="actions">
                        <?php if ($demande['places_restantes'] > 0) { ?>
                            <form class="en-ligne" method="post" action="index.php?page=accepter&amp;id=<?= $demande['id_trajet'] ?>&amp;reservation=<?= $demande['id'] ?>">
                                <?= champCsrf() ?>
                                <!-- Après l'action, on revient sur cette page -->
                                <input type="hidden" name="retour" value="mes-trajets">
                                <button class="bouton bouton-petit" type="submit">Accepter</button>
                            </form>
                        <?php } ?>
                        <form class="en-ligne" method="post" action="index.php?page=refuser&amp;id=<?= $demande['id_trajet'] ?>&amp;reservation=<?= $demande['id'] ?>">
                            <?= champCsrf() ?>
                            <input type="hidden" name="retour" value="mes-trajets">
                            <button class="bouton bouton-danger bouton-petit" type="submit">Refuser</button>
                        </form>
                    </div>
                </li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>

<!-- ===== Tous mes trajets ===== -->
<div class="section"><h2>Trajets publiés</h2><span class="discret"><?= count($trajets) ?> trajet(s)</span></div>

<?php if (count($trajets) === 0) { ?>
    <div class="vide"><b>Vous n'avez publié aucun trajet</b><a href="index.php?page=publier">Publier mon premier trajet</a></div>
<?php } else { ?>
    <div class="grille-billets">
        <?php foreach ($trajets as $t) {
            require 'vues/gabarit/carte-trajet.php';
        } ?>
    </div>
<?php } ?>
