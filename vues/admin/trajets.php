<?php
/**
 * Vue : liste de tous les trajets
 * Variables : $trajets (tous les trajets avec le nom du conducteur)
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var array $trajets */
require 'vues/admin/onglets.php';
?>
<div class="carte">
    <h2><?= count($trajets) ?> trajets</h2>
    <div class="tableau-defilant">
        <table>
            <tr>
                <th>Date</th>
                <th>Itinéraire</th>
                <th>Conducteur</th>
                <th class="nombre">Places</th>
                <th class="nombre">Prix</th>
                <th>Statut</th>
            </tr>
            <!-- Places : occupées / totales (occupées = totales - restantes) -->
            <?php foreach ($trajets as $t) { ?>
                <tr>
                    <td><?= formaterDate($t['date_heure']) ?> <span class="discret"><?= formaterHeure($t['date_heure']) ?></span></td>
                    <td><a href="index.php?page=trajet&amp;id=<?= $t['id'] ?>"><?= e($t['origine']) ?> → <?= e($t['destination']) ?></a></td>
                    <td><?= e($t['nom_conducteur']) ?></td>
                    <td class="nombre"><?= $t['places_totales'] - $t['places_restantes'] ?> / <?= $t['places_totales'] ?></td>
                    <td class="nombre"><?= formaterPrix((float) $t['prix']) ?></td>
                    <td><span class="statut <?= classeStatut($t['statut']) ?>"><?= libelleStatut($t['statut']) ?></span></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>
