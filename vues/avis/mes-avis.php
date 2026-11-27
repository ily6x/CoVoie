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
<div class="entete-page">
    <div>
        <p class="surtitre">Avis et réputation</p>
        <h1>Mes avis</h1>
        <p>
            <?php
            // null = aucun avis reçu (voir Avis::noteMoyenne())
            if ($note === null) { ?>
                Vous n'avez pas encore reçu d'avis : votre profil affiche « Pas encore noté ».
            <?php } else { ?>
                Votre note moyenne est de <b class="note"><?= formaterNote($note) ?></b> sur <?= count($recus) ?> avis reçus.
            <?php } ?>
        </p>
    </div>
</div>

<?php require 'vues/gabarit/onglets-espace.php'; ?>

<!-- ===== Trajets terminés qui attendent encore une note ===== -->
<?php if (count($aNoter) > 0) { ?>
    <section class="section">
        <div class="section-titre">
            <h2>À noter</h2>
            <span class="plaque statut-en-attente"><?= count($aNoter) ?> trajet<?= count($aNoter) > 1 ? 's' : '' ?></span>
        </div>
        <div class="defilant">
            <table class="donnees">
                <thead><tr><th>Trajet</th><th>Terminé le</th><th><span class="visuellement-cache">Action</span></th></tr></thead>
                <tbody>
                    <?php foreach ($aNoter as $trajet) { ?>
                        <tr>
                            <td><b><?= e($trajet['origine']) ?> → <?= e($trajet['destination']) ?></b></td>
                            <td class="date"><?= formaterDate($trajet['date_heure']) ?></td>
                            <td><a class="bouton bouton--principal bouton--petit" href="index.php?page=noter&amp;id=<?= $trajet['id'] ?>">Noter</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </section>
<?php } ?>

<div class="deux-colonnes">
    <!-- ===== Avis reçus ===== -->
    <section class="section">
        <h2>Avis reçus</h2>
        <?php if (count($recus) === 0) { ?>
            <div class="vide"><?= icone('etoile', 40) ?><h3>Aucun avis reçu</h3><p>Ils arrivent après vos trajets terminés.</p></div>
        <?php } else { ?>
            <div class="bloc">
                <ul class="avis-liste">
                    <?php foreach ($recus as $avis) { ?>
                        <li>
                            <div class="avis-tete">
                                <span class="personne"><?= avatar($avis['nom_auteur'], 'avatar--petit') ?><b><?= e($avis['nom_auteur']) ?></b></span>
                                <span class="etoiles" aria-hidden="true"><?= etoiles((int) $avis['note']) ?></span>
                                <span class="visuellement-cache"><?= $avis['note'] ?> sur 5</span>
                            </div>
                            <span class="avis-contexte"><?= e($avis['origine']) ?> → <?= e($avis['destination']) ?> - <?= formaterDate($avis['date_avis']) ?></span>
                            <?php if ($avis['commentaire'] !== null) { ?>
                                <p><?= e($avis['commentaire']) ?></p>
                            <?php } ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>
    </section>

    <!-- ===== Avis donnés ===== -->
    <section class="section">
        <h2>Avis donnés</h2>
        <?php if (count($ecrits) === 0) { ?>
            <div class="vide"><?= icone('etoile', 40) ?><h3>Vous n'avez noté personne</h3><p>Notez vos trajets terminés depuis l'encart « À noter ».</p></div>
        <?php } else { ?>
            <div class="bloc">
                <ul class="avis-liste">
                    <?php foreach ($ecrits as $avis) { ?>
                        <li>
                            <div class="avis-tete">
                                <span class="personne"><?= avatar($avis['nom_cible'], 'avatar--petit') ?><b><?= e($avis['nom_cible']) ?></b></span>
                                <span class="etoiles" aria-hidden="true"><?= etoiles((int) $avis['note']) ?></span>
                                <span class="visuellement-cache"><?= $avis['note'] ?> sur 5</span>
                            </div>
                            <span class="avis-contexte"><?= e($avis['origine']) ?> → <?= e($avis['destination']) ?> - <?= formaterDate($avis['date_avis']) ?></span>
                            <?php if ($avis['commentaire'] !== null) { ?>
                                <p><?= e($avis['commentaire']) ?></p>
                            <?php } ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>
    </section>
</div>
