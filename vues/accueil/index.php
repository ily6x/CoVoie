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
<!-- Haut de page : titre, formulaire de recherche et illustration -->
<section class="hero">
    <div>
        <h1>Partagez la route <em>vers le campus.</em></h1>
        <p class="intro">Des étudiants font déjà votre trajet. Réservez une place, partagez les frais et arrivez à l'heure en cours.</p>

        <!-- Formulaire en GET : les critères apparaissent dans l'URL de la page de résultats -->
        <form class="recherche" method="get" action="index.php">
            <input type="hidden" name="page" value="trajets">

            <div class="recherche-lieux">
                <div class="champ-lieu">
                    <i class="point"></i>
                    <input type="text" name="depart" id="depart" placeholder="Départ (ex : Boulogne)" aria-label="Départ">
                </div>
                <div class="champ-lieu">
                    <i class="point fin"></i>
                    <input type="text" name="arrivee" id="arrivee" placeholder="Arrivée (ex : Campus Paris 15e)" aria-label="Arrivée">
                </div>
                <!-- Échange les deux champs (voir js/script.js) -->
                <button type="button" class="bouton-inverser" data-inverser title="Inverser départ et arrivée">⇅</button>
            </div>

            <div class="recherche-bas">
                <input type="date" name="date" aria-label="Date du trajet">
                <button class="bouton" type="submit">Rechercher</button>
            </div>
        </form>
    </div>

    <img src="images/illustration-accueil.svg" alt="Une voiture partagée roule vers le campus">
</section>

<!-- Chiffres clés de la plateforme -->
<section class="chiffres">
    <div class="chiffre"><b><?= $stats['publies'] ?></b><span>trajets publiés</span></div>
    <div class="chiffre"><b><?= $stats['realises'] ?></b><span>trajets réalisés</span></div>
    <div class="chiffre"><b><?= $nombreMembres ?></b><span>étudiants inscrits</span></div>
    <div class="chiffre">
        <b><?= $moyenneGenerale === null ? '-' : number_format($moyenneGenerale, 1, ',', ' ') . ' / 5' ?></b>
        <span>note moyenne</span>
    </div>
</section>

<div class="deux-colonnes">
    <section>
        <div class="section">
            <h2>Prochains départs</h2>
            <a href="index.php?page=trajets">Tous les trajets →</a>
        </div>

        <!-- S'il n'y a aucun trajet à venir, on affiche un message à la place de la grille -->
        <?php if (count($prochainsTrajets) === 0) { ?>
            <div class="vide"><b>Aucun départ prévu</b>Soyez le premier à publier un trajet.</div>
        <?php } else { ?>
            <div class="grille-billets">
                <!-- Chaque trajet est affiché avec le même gabarit de billet (vues/gabarit/carte-trajet.php) -->
                <?php foreach ($prochainsTrajets as $t) {
                    require 'vues/gabarit/carte-trajet.php';
                } ?>
            </div>
        <?php } ?>
    </section>

    <aside>
        <div class="carte">
            <h2>Les mieux notés</h2>
            <ol class="classement">
                <!-- $rang commence à 0 : on affiche $rang + 1 pour avoir 1, 2, 3... -->
                <?php foreach ($meilleursMembres as $rang => $membre) { ?>
                    <li>
                        <span class="rang"><?= $rang + 1 ?></span>
                        <?= avatar($membre['nom']) ?>
                        <a class="nom" href="index.php?page=profil&amp;id=<?= $membre['id'] ?>"><?= e($membre['nom']) ?></a>
                        <span class="note"><?= formaterNote((float) $membre['moyenne']) ?></span>
                    </li>
                <?php } ?>
            </ol>
        </div>

        <div class="carte">
            <h2>Comment ça marche ?</h2>
            <ol class="etapes-accueil">
                <li><b>Le conducteur publie</b>son trajet, ses places et un prix indicatif.</li>
                <li><b>Le passager demande une place</b>et le conducteur accepte ou refuse.</li>
                <li><b>Après le trajet</b>chacun note l'autre : la réputation se construit.</li>
            </ol>
        </div>
    </aside>
</div>
