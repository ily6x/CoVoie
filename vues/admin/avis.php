<?php
/**
 * Vue : modération des avis
 * Variables : $avis (tous les avis avec le nom de l'auteur et de la cible)
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var array $avis */
require 'vues/admin/onglets.php';
?>
<div class="carte">
    <h2><?= count($avis) ?> avis <span class="discret">La moyenne d'un membre est recalculée dès qu'un avis est supprimé.</span></h2>
    <div class="tableau-defilant">
        <table>
            <tr>
                <th>Date</th>
                <th>Auteur</th>
                <th>Membre noté</th>
                <th>Note</th>
                <th>Commentaire</th>
                <th></th>
            </tr>
            <!-- Une ligne par avis ; le bouton Supprimer envoie un formulaire POST (avec jeton CSRF) -->
            <?php foreach ($avis as $a) { ?>
                <tr>
                    <td><?= formaterDate($a['date_avis']) ?></td>
                    <td><?= e($a['nom_auteur']) ?></td>
                    <td><a href="index.php?page=profil&amp;id=<?= $a['id_cible'] ?>"><?= e($a['nom_cible']) ?></a></td>
                    <td class="etoiles"><?= etoiles((int) $a['note']) ?></td>
                    <td>
                        <?php if ($a['commentaire'] === null) { ?>
                            <span class="note-vide">-</span>
                        <?php } else { ?>
                            <?= e($a['commentaire']) ?>
                        <?php } ?>
                    </td>
                    <td>
                        <form class="en-ligne" method="post" action="index.php?page=admin-supprimer-avis&amp;id=<?= $a['id'] ?>"
                              data-confirmation="Supprimer cet avis ?">
                            <?= champCsrf() ?>
                            <button class="bouton bouton-danger bouton-petit" type="submit">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>
