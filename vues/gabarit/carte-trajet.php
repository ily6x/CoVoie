<?php
/**
 * ------------------------------------------------------------------
 *  Gabarit : un trajet affiché sous forme de BILLET
 * ------------------------------------------------------------------
 *  Réutilisé par l'accueil, la recherche et "Mes trajets".
 *  Variable attendue : $t, une ligne renvoyée par Trajet::lister...()
 *  (avec nom_conducteur et note_conducteur).
 * ------------------------------------------------------------------
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var array $t */
$placesPrises = $t['places_totales'] - $t['places_restantes'];
?>
<article class="billet">
    <div class="billet-haut">
        <div class="conducteur">
            <?= avatar($t['nom_conducteur']) ?>
            <div>
                <span class="nom"><?= e($t['nom_conducteur']) ?></span>
                <?php if ($t['note_conducteur'] === null) { ?>
                    <small class="note-vide">Pas encore noté</small>
                <?php } else { ?>
                    <small><span class="note"><?= formaterNote((float) $t['note_conducteur']) ?></span></small>
                <?php } ?>
            </div>
            <span class="statut <?= classeStatut($t['statut']) ?>"><?= libelleStatut($t['statut']) ?></span>
        </div>

        <div class="horaire">
            <div class="lieu">
                <b><?= formaterHeure($t['date_heure']) ?></b>
                <span title="<?= e($t['origine']) ?>"><?= e($t['origine']) ?></span>
            </div>
            <div class="trait"></div>
            <div class="lieu arrivee">
                <b><?= formaterJour($t['date_heure']) ?></b>
                <span title="<?= e($t['destination']) ?>"><?= e($t['destination']) ?></span>
            </div>
        </div>
    </div>

    <!-- Découpe du billet, avec le numéro du trajet sur 4 chiffres (ex : 0026) -->
    <div class="decoupe"><span>Billet n° <?= sprintf('%04d', $t['id']) ?></span></div>

    <div class="billet-bas">
        <!-- Un petit carré par place : gris = déjà prise, vert = encore libre -->
        <div class="sieges" title="<?= $t['places_restantes'] ?> place(s) libre(s)">
            <?php for ($i = 1; $i <= $t['places_totales']; $i++) { ?>
                <i class="siege<?= $i <= $placesPrises ? ' pris' : '' ?>"></i>
            <?php } ?>
            <span><?= $t['places_restantes'] ?> libre<?= $t['places_restantes'] > 1 ? 's' : '' ?></span>
        </div>
        <div class="actions">
            <span class="tampon"><?= formaterPrix((float) $t['prix']) ?></span>
            <a class="bouton bouton-petit" href="index.php?page=trajet&amp;id=<?= $t['id'] ?>">Voir</a>
        </div>
    </div>
</article>
