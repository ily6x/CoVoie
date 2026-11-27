<?php
/**
 * Vue : tableau de bord administrateur
 * Variables : $stats (trajets), $reservations (compteurs), $nombreMembres, $nombreAvis, $moyenneGenerale
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var array $stats */
/** @var array $reservations */
/** @var int $nombreMembres */
/** @var int $nombreAvis */
/** @var float|null $moyenneGenerale */
require 'vues/admin/onglets.php';
?>
<h2>Trajets</h2>
<section class="chiffres">
    <div class="chiffre"><b><?= $stats['publies'] ?></b><span>trajets publiés</span></div>
    <div class="chiffre"><b><?= $stats['ouverts'] ?></b><span>ouverts</span></div>
    <div class="chiffre"><b><?= $stats['complets'] ?></b><span>complets</span></div>
    <div class="chiffre"><b><?= $stats['realises'] ?></b><span>terminés</span></div>
</section>

<h2>Réservations</h2>
<section class="chiffres">
    <div class="chiffre"><b><?= $reservations['total'] ?></b><span>demandes au total</span></div>
    <div class="chiffre"><b><?= $reservations['confirmees'] ?></b><span>confirmées</span></div>
    <div class="chiffre"><b><?= $reservations['en_attente'] ?></b><span>en attente</span></div>
    <div class="chiffre"><b><?= round((float) $stats['taux']) ?> %</b><span>remplissage moyen</span></div>
</section>

<h2>Communauté</h2>
<section class="chiffres">
    <div class="chiffre"><b><?= $nombreMembres ?></b><span>membres inscrits</span></div>
    <div class="chiffre"><b><?= $nombreAvis ?></b><span>avis publiés</span></div>
    <div class="chiffre">
        <b class="note"><?= formaterNote($moyenneGenerale) ?></b><span>note moyenne</span>
    </div>
    <div class="chiffre"><b><?= $stats['places_occupees'] ?></b><span>places partagées</span></div>
</section>
