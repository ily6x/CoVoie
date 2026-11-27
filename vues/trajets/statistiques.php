<?php
/**
 * Vue : statistiques du conducteur
 * Variables : $stats (Trajet::statistiques), $maNote, $nombreAvis, $passagerFrequent (ou null)
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var array $stats */
/** @var float|null $maNote */
/** @var int $nombreAvis */
/** @var array|null $passagerFrequent */

$taux = round((float) $stats['taux']);

// Répartition des trajets par statut, en % de la largeur de la barre
$total = max(1, (int) $stats['publies']);   // max(1, ...) évite une division par zéro
$partOuverts = round(100 * $stats['ouverts'] / $total);
$partComplets = round(100 * $stats['complets'] / $total);
$partTermines = round(100 * $stats['realises'] / $total);
?>
<div class="entete-page">
    <div>
        <p class="surtitre">Espace conducteur</p>
        <h1>Mes statistiques</h1>
        <p>Votre activité de conducteur sur CoVoie.</p>
    </div>
</div>

<?php require 'vues/gabarit/onglets-espace.php'; ?>

<!-- Les chiffres clés, calculés par Trajet::statistiques() -->
<section class="indicateurs" aria-label="Chiffres clés">
    <div class="indicateur"><b><?= $stats['publies'] ?></b><span>trajets publiés</span></div>
    <div class="indicateur"><b><?= $stats['realises'] ?></b><span>trajets réalisés</span></div>
    <div class="indicateur"><b><?= $stats['places_occupees'] ?></b><span>places partagées</span></div>
    <div class="indicateur">
        <?php if ($maNote === null) { ?>
            <b>-</b><span>pas encore noté</span>
        <?php } else { ?>
            <b><?= number_format($maNote, 2, ',', ' ') ?></b><span>note moyenne, <?= $nombreAvis ?> avis</span>
        <?php } ?>
    </div>
</section>

<div class="deux-colonnes">
    <section class="section">
        <h2>Mes trajets par statut</h2>
        <div class="bloc">
            <!-- Barre empilée : la largeur de chaque morceau = sa part en % -->
            <div class="barre-empilee" role="img" aria-label="<?= $stats['ouverts'] ?> ouverts, <?= $stats['complets'] ?> complets, <?= $stats['realises'] ?> terminés">
                <span class="fond-ouvert" style="width: <?= $partOuverts ?>%"></span>
                <span class="fond-complet" style="width: <?= $partComplets ?>%"></span>
                <span class="fond-termine" style="width: <?= $partTermines ?>%"></span>
            </div>
            <ul class="legende">
                <li><i class="fond-ouvert"></i>Ouverts : <b class="mono"><?= $stats['ouverts'] ?></b></li>
                <li><i class="fond-complet"></i>Complets : <b class="mono"><?= $stats['complets'] ?></b></li>
                <li><i class="fond-termine"></i>Terminés : <b class="mono"><?= $stats['realises'] ?></b></li>
            </ul>
        </div>
    </section>

    <section class="section">
        <h2>Taux de remplissage</h2>
        <div class="bloc">
            <span class="gros-chiffre"><?= $taux ?> %</span>
            <!-- La largeur de la barre orange est le taux de remplissage moyen -->
            <div class="jauge"><span style="width: <?= $taux ?>%"></span></div>
            <p class="discret sans-marge"><?= $stats['places_occupees'] ?> place(s) occupée(s) sur <?= $stats['places_totales'] ?> proposée(s), en moyenne sur tous vos trajets.</p>
        </div>
    </section>
</div>

<section class="section">
    <h2>Passager le plus fréquent</h2>
    <div class="bloc">
        <?php
        // Trajet::passagerLePlusFrequent() renvoie null si personne n'a encore voyagé avec ce conducteur
        if ($passagerFrequent === null) { ?>
            <p class="note-vide sans-marge">Aucun passager sur vos trajets terminés pour l'instant.</p>
        <?php } else { ?>
            <div class="personne">
                <?= avatar($passagerFrequent['nom']) ?>
                <span>
                    <a class="personne-nom" href="index.php?page=profil&amp;id=<?= $passagerFrequent['id'] ?>"><?= e($passagerFrequent['nom']) ?></a>
                    <small><?= $passagerFrequent['nb'] ?> trajet(s) terminé(s) avec vous</small>
                </span>
            </div>
        <?php } ?>
    </div>
</section>
