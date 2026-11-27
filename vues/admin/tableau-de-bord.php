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

$taux = round((float) $stats['taux']);

// Parts en % pour les barres empilées (max(1, ...) évite une division par zéro)
$totalTrajets = max(1, (int) $stats['publies']);
$totalResa = max(1, (int) $reservations['total']);
?>
<!-- Statistiques globales demandées par le sujet : trajets réalisés et taux de remplissage moyen -->
<section class="indicateurs" aria-label="Chiffres clés">
    <div class="indicateur"><b><?= $stats['publies'] ?></b><span>trajets publiés</span></div>
    <div class="indicateur"><b><?= $stats['realises'] ?></b><span>trajets réalisés</span></div>
    <div class="indicateur"><b><?= $nombreMembres ?></b><span>membres inscrits</span></div>
    <div class="indicateur"><b><?= $nombreAvis ?></b><span>avis publiés</span></div>
</section>

<div class="deux-colonnes">
    <section class="section">
        <h2>Trajets par statut</h2>
        <div class="bloc">
            <div class="barre-empilee" role="img" aria-label="<?= $stats['ouverts'] ?> ouverts, <?= $stats['complets'] ?> complets, <?= $stats['realises'] ?> terminés">
                <span class="fond-ouvert" style="width: <?= round(100 * $stats['ouverts'] / $totalTrajets) ?>%"></span>
                <span class="fond-complet" style="width: <?= round(100 * $stats['complets'] / $totalTrajets) ?>%"></span>
                <span class="fond-termine" style="width: <?= round(100 * $stats['realises'] / $totalTrajets) ?>%"></span>
            </div>
            <ul class="legende">
                <li><i class="fond-ouvert"></i>Ouverts : <b class="mono"><?= $stats['ouverts'] ?></b></li>
                <li><i class="fond-complet"></i>Complets : <b class="mono"><?= $stats['complets'] ?></b></li>
                <li><i class="fond-termine"></i>Terminés : <b class="mono"><?= $stats['realises'] ?></b></li>
            </ul>
        </div>
    </section>

    <section class="section">
        <h2>Réservations par statut</h2>
        <div class="bloc">
            <div class="barre-empilee" role="img" aria-label="<?= $reservations['confirmees'] ?> confirmées, <?= $reservations['en_attente'] ?> en attente, <?= $reservations['refusees'] ?> refusées">
                <span class="fond-ouvert" style="width: <?= round(100 * $reservations['confirmees'] / $totalResa) ?>%"></span>
                <span class="fond-attente" style="width: <?= round(100 * $reservations['en_attente'] / $totalResa) ?>%"></span>
                <span class="fond-refuse" style="width: <?= round(100 * $reservations['refusees'] / $totalResa) ?>%"></span>
            </div>
            <ul class="legende">
                <li><i class="fond-ouvert"></i>Confirmées : <b class="mono"><?= $reservations['confirmees'] ?></b></li>
                <li><i class="fond-attente"></i>En attente : <b class="mono"><?= $reservations['en_attente'] ?></b></li>
                <li><i class="fond-refuse"></i>Refusées : <b class="mono"><?= $reservations['refusees'] ?></b></li>
            </ul>
        </div>
    </section>
</div>

<div class="deux-colonnes">
    <section class="section">
        <h2>Taux de remplissage moyen</h2>
        <div class="bloc">
            <span class="gros-chiffre"><?= $taux ?> %</span>
            <!-- La largeur de la barre orange est le taux de remplissage moyen de tous les trajets -->
            <div class="jauge"><span style="width: <?= $taux ?>%"></span></div>
            <p class="discret sans-marge"><?= $stats['places_occupees'] ?> place(s) partagée(s) sur <?= $stats['places_totales'] ?> proposée(s).</p>
        </div>
    </section>

    <section class="section">
        <h2>Réputation de la plateforme</h2>
        <div class="bloc">
            <span class="gros-chiffre"><?= $moyenneGenerale === null ? '-' : number_format($moyenneGenerale, 2, ',', ' ') ?></span>
            <p class="discret sans-marge">Note moyenne sur 5, calculée sur les <?= $nombreAvis ?> avis publiés. Elle change dès qu'un avis est supprimé.</p>
        </div>
    </section>
</div>
