<?php
/**
 * Vue : modération des avis
 * Variables : $avis (tous les avis avec le nom de l'auteur et de la cible)
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var array $avis */
require 'vues/admin/onglets.php';
?>
<div class="section-titre">
    <h2><?= count($avis) ?> avis</h2>
    <span class="discret">La moyenne d'un membre est recalculée dès qu'un avis est supprimé.</span>
</div>

<?php if (count($avis) === 0) { ?>
    <div class="vide"><?= icone('etoile', 40) ?><h3>Aucun avis à modérer</h3><p>Les avis apparaîtront ici après les premiers trajets terminés.</p></div>
<?php } else { ?>
    <div class="defilant">
        <table class="donnees">
            <thead>
                <tr><th>Date</th><th>Auteur</th><th>Membre noté</th><th>Note</th><th>Commentaire</th><th><span class="visuellement-cache">Action</span></th></tr>
            </thead>
            <tbody>
                <!-- Une ligne par avis ; le bouton Supprimer envoie un formulaire POST (avec jeton CSRF) -->
                <?php foreach ($avis as $a) { ?>
                    <tr>
                        <td class="date"><?= formaterDate($a['date_avis']) ?></td>
                        <td><?= e($a['nom_auteur']) ?></td>
                        <td><a href="index.php?page=profil&amp;id=<?= $a['id_cible'] ?>"><?= e($a['nom_cible']) ?></a></td>
                        <td>
                            <span class="etoiles" aria-hidden="true"><?= etoiles((int) $a['note']) ?></span>
                            <span class="visuellement-cache"><?= $a['note'] ?> sur 5</span>
                        </td>
                        <td class="commentaire">
                            <?php if ($a['commentaire'] === null) { ?>
                                <span class="note-vide">Sans commentaire</span>
                            <?php } else { ?>
                                <?= e($a['commentaire']) ?>
                            <?php } ?>
                        </td>
                        <td>
                            <form class="en-ligne" method="post" action="index.php?page=admin-supprimer-avis&amp;id=<?= $a['id'] ?>"
                                  data-confirmation="Supprimer cet avis de <?= e($a['nom_auteur']) ?> ? La moyenne de <?= e($a['nom_cible']) ?> sera recalculée.">
                                <?= champCsrf() ?>
                                <button class="bouton bouton--danger bouton--petit" type="submit">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
<?php } ?>
