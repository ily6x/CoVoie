<?php
/**
 * Vue : page d'accueil
 * Variables : $stats, $nombreMembres, $moyenneGenerale, $prochainsTrajets, $meilleursMembres
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var array $stats */
/** @var int $nombreMembres */
/** @var float|null $moyenneGenerale */
/** @var array $prochainsTrajets */
/** @var array $meilleursMembres */
?>
<!-- Haut de page : titre à gauche, illustration à droite, puis la barre de recherche -->
<section class="accueil-haut">
    <div class="accueil-texte">
        <p class="surtitre">Covoiturage entre étudiants du campus</p>
        <h1>Le trajet jusqu'au campus, <em>à plusieurs.</em></h1>
        <p class="accroche">Des étudiants font déjà votre trajet chaque matin. Trouvez un départ, demandez une place et partagez les frais d'essence.</p>
    </div>
    <img class="accueil-illustration" src="images/illustration-accueil.svg" alt="Une étudiante appuyée contre sa voiture, prête à partir">

    <!-- Formulaire en GET : les critères apparaissent dans l'URL de la page de résultats -->
    <form class="recherche" method="get" action="index.php">
        <input type="hidden" name="page" value="trajets">

        <div class="champ">
            <label for="depart">Départ</label>
            <input type="text" name="depart" id="depart" placeholder="Ex : Boulogne">
        </div>
        <!-- Échange les deux champs (voir js/script.js) -->
        <button type="button" class="bouton-inverser" data-inverser aria-label="Inverser départ et arrivée"><?= icone('inverser', 18) ?></button>
        <div class="champ">
            <label for="arrivee">Arrivée</label>
            <input type="text" name="arrivee" id="arrivee" placeholder="Ex : Campus Paris 15e">
        </div>
        <div class="champ champ-date">
            <label for="date">Date</label>
            <input type="date" name="date" id="date">
        </div>
        <button class="bouton bouton--principal" type="submit"><?= icone('recherche', 18) ?>Rechercher</button>
    </form>
</section>

<!-- Les 6 prochains trajets encore réservables -->
<section class="section">
    <div class="section-titre">
        <h2>Prochains départs</h2>
        <a href="index.php?page=trajets">Voir tous les trajets</a>
    </div>
    <?php if (count($prochainsTrajets) === 0) { ?>
        <div class="vide">
            <img class="vide-illustration" src="images/illustration-vide.svg" alt="">
            <h3>Aucun départ prévu</h3>
            <p>Personne n'a encore publié de trajet à venir.</p>
            <a class="bouton bouton--principal" href="index.php?page=publier">Publier le premier trajet</a>
        </div>
    <?php } else {
        // Paramètres du gabarit : cartes avec le conducteur
        $trajets = $prochainsTrajets;
        $modeConducteur = false;
        require 'vues/gabarit/cartes-trajets.php';
    } ?>
</section>

<!-- Chiffres clés de la plateforme, calculés en SQL (voir AccueilController) -->
<section class="indicateurs" aria-label="Chiffres clés">
    <div class="indicateur"><b><?= $stats['publies'] ?></b><span>trajets publiés</span></div>
    <div class="indicateur"><b><?= $stats['realises'] ?></b><span>trajets réalisés</span></div>
    <div class="indicateur"><b><?= $nombreMembres ?></b><span>étudiants inscrits</span></div>
    <div class="indicateur">
        <b><?= $moyenneGenerale === null ? '-' : number_format($moyenneGenerale, 1, ',', ' ') ?></b>
        <span>note moyenne sur 5</span>
    </div>
</section>

<div class="deux-colonnes">
    <section class="section">
        <div class="section-titre"><h2>Les mieux notés</h2><span class="discret">2 avis minimum</span></div>
        <div class="bloc">
            <ol class="classement">
                <?php foreach ($meilleursMembres as $rang => $membre) { ?>
                    <li>
                        <!-- $rang commence à 0 : on affiche $rang + 1 pour avoir 01, 02, 03... -->
                        <span class="rang"><?= sprintf('%02d', $rang + 1) ?></span>
                        <span class="personne">
                            <?= avatar($membre['nom']) ?>
                            <a class="personne-nom" href="index.php?page=profil&amp;id=<?= $membre['id'] ?>"><?= e($membre['nom']) ?></a>
                        </span>
                        <span class="note"><?= formaterNote((float) $membre['moyenne']) ?></span>
                    </li>
                <?php } ?>
            </ol>
        </div>
    </section>

    <section class="section">
        <div class="section-titre"><h2>Comment ça marche ?</h2></div>
        <div class="bloc">
            <ol class="etapes-accueil">
                <li><span><b>Le conducteur publie</b>son trajet, ses places et un prix indicatif.</span></li>
                <li><span><b>Le passager demande une place</b>et le conducteur accepte ou refuse.</span></li>
                <li><span><b>Après le départ</b>le trajet se clôture et chacun note l'autre : la réputation se construit.</span></li>
            </ol>
        </div>
    </section>
</div>
