<?php
/**
 * Vue : statistiques du conducteur
 * Variables : $stats (Trajet::statistiques), $maNote, $nombreAvis, $passagerFrequent (ou null)
 */
$taux = round((float) $stats['taux']);
?>
<div class="titre-page">
    <div>
        <h1>Mes statistiques</h1>
        <p>Votre activité de conducteur sur CoVoie.</p>
    </div>
    <a class="bouton bouton-secondaire" href="index.php?page=mes-trajets">← Mes trajets</a>
</div>

<section class="chiffres">
    <div class="chiffre"><b><?= $stats['publies'] ?></b><span>trajets publiés</span></div>
    <div class="chiffre"><b><?= $stats['realises'] ?></b><span>trajets réalisés</span></div>
    <div class="chiffre"><b><?= $stats['ouverts'] ?> / <?= $stats['complets'] ?></b><span>ouverts / complets</span></div>
    <div class="chiffre">
        <?php if ($maNote === null) { ?>
            <b class="note-vide">–</b><span>pas encore noté</span>
        <?php } else { ?>
            <b class="note"><?= formaterNote($maNote) ?></b><span>sur <?= $nombreAvis ?> avis</span>
        <?php } ?>
    </div>
</section>

<div class="deux-colonnes">
    <div class="carte">
        <h2>Taux de remplissage moyen</h2>
        <p class="discret">Part des places proposées qui ont été réservées, en moyenne sur tous vos trajets.</p>
        <div class="jauge"><div style="width: <?= $taux ?>%"></div></div>
        <p><b><?= $taux ?> %</b> · <?= $stats['places_occupees'] ?> place(s) occupée(s) sur <?= $stats['places_totales'] ?> proposée(s)</p>
    </div>

    <div class="carte">
        <h2>Passager le plus fréquent</h2>
        <?php if ($passagerFrequent === null) { ?>
            <p class="note-vide">Aucun passager sur vos trajets terminés pour l'instant.</p>
        <?php } else { ?>
            <div class="mise-en-avant">
                <span class="avatar"><?= e(initiales($passagerFrequent['nom'])) ?></span>
                <div>
                    <a href="index.php?page=profil&amp;id=<?= $passagerFrequent['id'] ?>"><b><?= e($passagerFrequent['nom']) ?></b></a><br>
                    <small><?= $passagerFrequent['nb'] ?> trajet(s) terminé(s) avec vous</small>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
