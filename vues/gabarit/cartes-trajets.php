<?php
/**
 * ------------------------------------------------------------------
 *  Gabarit : CARTES DE TRAJETS (le composant principal du site)
 * ------------------------------------------------------------------
 *  Affiche une liste de trajets sous forme de cartes :
 *   - en haut : l'heure, le jour et le prix ;
 *   - au milieu : le parcours départ -> arrivée ;
 *   - en bas : le conducteur (ou le statut) et les places libres.
 *  Réutilisé par l'accueil, la recherche et "Mes trajets".
 *
 *  Variables à définir AVANT le require :
 *   - $trajets        : lignes renvoyées par Trajet::lister...() ou Trajet::rechercher()
 *   - $modeConducteur : true sur "Mes trajets" -> statut du trajet au lieu du conducteur
 * ------------------------------------------------------------------
 */
/** @var array $trajets */
/** @var bool $modeConducteur */
?>
<div class="grille-trajets">
    <?php foreach ($trajets as $t) {
        $placesPrises = $t['places_totales'] - $t['places_restantes']; ?>

        <article class="trajet">
            <div class="trajet-haut">
                <p class="trajet-horaire">
                    <span class="trajet-heure"><?= formaterHeure($t['date_heure']) ?></span>
                    <span class="trajet-jour"><?= e(formaterJour($t['date_heure'])) ?></span>
                </p>
                <span class="prix"><?= formaterPrix((float) $t['prix']) ?></span>
            </div>

            <!-- Parcours : un rond vide (départ) relié à un rond orange (arrivée) -->
            <ol class="parcours">
                <li>
                    <!-- Ce lien s'étend sur toute la carte (CSS) : on peut cliquer n'importe où -->
                    <a class="trajet-lien" href="index.php?page=trajet&amp;id=<?= $t['id'] ?>"><?= e($t['origine']) ?><span class="visuellement-cache"> vers <?= e($t['destination']) ?></span></a>
                </li>
                <li aria-hidden="true"><?= e($t['destination']) ?></li>
            </ol>

            <div class="trajet-bas">
                <?php if ($modeConducteur) { ?>
                    <span class="plaque <?= classeStatut($t['statut']) ?>"><?= libelleStatut($t['statut']) ?></span>
                <?php } else { ?>
                    <span class="personne">
                        <?= avatar($t['nom_conducteur'], 'avatar--petit') ?>
                        <span>
                            <?= e($t['nom_conducteur']) ?>
                            <?php if ($t['note_conducteur'] === null) { ?>
                                <small class="note-vide">Pas encore noté</small>
                            <?php } else { ?>
                                <small class="note"><?= formaterNote((float) $t['note_conducteur']) ?></small>
                            <?php } ?>
                        </span>
                    </span>
                <?php } ?>

                <!-- Un siège par place : turquoise = libre, gris = déjà pris -->
                <span class="sieges" title="<?= $t['places_restantes'] ?> place(s) libre(s) sur <?= $t['places_totales'] ?>">
                    <?php for ($i = 1; $i <= $t['places_totales']; $i++) { ?>
                        <i class="siege<?= $i <= $placesPrises ? ' pris' : '' ?>"></i>
                    <?php } ?>
                    <span class="sieges-texte"><?= $t['places_restantes'] ?> libre<?= $t['places_restantes'] > 1 ? 's' : '' ?></span>
                </span>
            </div>
        </article>
    <?php } ?>
</div>
