<?php
/**
 * Vue : "Mes avis"
 * Variables : $aNoter (trajets terminés à noter), $note (ma moyenne), $recus, $ecrits
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var float|null $note */
/** @var array $recus */
/** @var array $aNoter */
/** @var array $ecrits */
?>
<div class="titre-page">
    <div>
        <h1>Mes avis</h1>
        <p>
            Ma note moyenne :
            <!-- null = aucun avis reçu (voir Avis::noteMoyenne()) -->
            <?php if ($note === null) { ?>
                <span class="note-vide">pas encore noté</span>
            <?php } else { ?>
                <span class="note"><?= formaterNote($note) ?></span> sur <?= count($recus) ?> avis
            <?php } ?>
        </p>
    </div>
    <a class="bouton bouton-secondaire" href="index.php?page=profil">Voir mon profil public</a>
</div>

<!-- ===== Trajets terminés qui attendent une note ===== -->
<?php if (count($aNoter) > 0) { ?>
    <div class="carte">
        <h2>À noter <span class="statut statut-en-attente"><?= count($aNoter) ?> trajet(s)</span></h2>
        <ul class="liste">
            <?php foreach ($aNoter as $trajet) { ?>
                <li>
                    <div class="infos-personne">
                        <b><?= e($trajet['origine']) ?> → <?= e($trajet['destination']) ?></b>
                        <small>Terminé le <?= formaterDate($trajet['date_heure']) ?></small>
                    </div>
                    <a class="bouton bouton-petit" href="index.php?page=noter&amp;id=<?= $trajet['id'] ?>">Noter</a>
                </li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>

<div class="deux-colonnes colonnes-egales">
    <!-- ===== Avis reçus ===== -->
    <div class="carte">
        <h2>Avis reçus</h2>
        <?php if (count($recus) === 0) { ?>
            <p class="note-vide">Aucun avis reçu pour le moment.</p>
        <?php } ?>
        <?php foreach ($recus as $avis) { ?>
            <div class="avis">
                <div class="avis-tete">
                    <?= avatar($avis['nom_auteur']) ?>
                    <b><?= e($avis['nom_auteur']) ?></b>
                    <span class="etoiles"><?= etoiles((int) $avis['note']) ?></span>
                </div>
                <small class="discret"><?= e($avis['origine']) ?> → <?= e($avis['destination']) ?> - <?= formaterDate($avis['date_avis']) ?></small>
                <?php if ($avis['commentaire'] !== null) { ?>
                    <p><?= e($avis['commentaire']) ?></p>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

    <!-- ===== Avis écrits ===== -->
    <div class="carte">
        <h2>Avis donnés</h2>
        <?php if (count($ecrits) === 0) { ?>
            <p class="note-vide">Vous n'avez encore noté personne.</p>
        <?php } ?>
        <?php foreach ($ecrits as $avis) { ?>
            <div class="avis">
                <div class="avis-tete">
                    <?= avatar($avis['nom_cible']) ?>
                    <b><?= e($avis['nom_cible']) ?></b>
                    <span class="etoiles"><?= etoiles((int) $avis['note']) ?></span>
                </div>
                <small class="discret"><?= e($avis['origine']) ?> → <?= e($avis['destination']) ?> - <?= formaterDate($avis['date_avis']) ?></small>
                <?php if ($avis['commentaire'] !== null) { ?>
                    <p><?= e($avis['commentaire']) ?></p>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>
