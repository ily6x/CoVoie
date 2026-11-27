<?php
/**
 * Vue : liste de tous les trajets
 * Variables : $trajets (tous les trajets avec le nom du conducteur)
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var array $trajets */
require 'vues/admin/onglets.php';
?>
<div class="section-titre">
    <h2><?= count($trajets) ?> trajets</h2>
    <span class="discret">Du plus récent au plus ancien.</span>
</div>

<div class="defilant">
    <table class="donnees">
        <thead>
            <tr><th>Départ</th><th>Itinéraire</th><th>Conducteur</th><th class="nombre">Places</th><th class="nombre">Prix</th><th>Statut</th></tr>
        </thead>
        <tbody>
            <!-- Places : occupées / totales (occupées = totales - restantes) -->
            <?php foreach ($trajets as $t) { ?>
                <tr>
                    <td class="date"><?= formaterDate($t['date_heure']) ?> <?= formaterHeure($t['date_heure']) ?></td>
                    <td><a href="index.php?page=trajet&amp;id=<?= $t['id'] ?>"><?= e($t['origine']) ?> → <?= e($t['destination']) ?></a></td>
                    <td><?= e($t['nom_conducteur']) ?></td>
                    <td class="chiffre nombre"><?= $t['places_totales'] - $t['places_restantes'] ?> / <?= $t['places_totales'] ?></td>
                    <td class="nombre"><span class="prix"><?= formaterPrix((float) $t['prix']) ?></span></td>
                    <td><span class="plaque <?= classeStatut($t['statut']) ?>"><?= libelleStatut($t['statut']) ?></span></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
