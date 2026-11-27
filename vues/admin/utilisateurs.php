<?php
/**
 * Vue : liste des comptes
 * Variables : $utilisateurs (tous les comptes avec leur note et leur nombre de trajets)
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var array $utilisateurs */
require 'vues/admin/onglets.php';
?>
<div class="carte">
    <h2><?= count($utilisateurs) ?> comptes</h2>
    <div class="tableau-defilant">
        <table>
            <tr>
                <th>Membre</th>
                <th>Email</th>
                <th>Inscrit le</th>
                <th class="nombre">Trajets</th>
                <th>Note</th>
                <th></th>
            </tr>
            <?php foreach ($utilisateurs as $u) { ?>
                <tr>
                    <td>
                        <a href="index.php?page=profil&amp;id=<?= $u['id'] ?>"><?= e($u['nom']) ?></a>
                        <?php if ($u['role'] === 'admin') { ?><span class="badge-admin">ADMIN</span><?php } ?>
                    </td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= formaterDate($u['date_inscription']) ?></td>
                    <td class="nombre"><?= $u['nb_trajets'] ?></td>
                    <td>
                        <?php if ($u['note'] === null) { ?>
                            <span class="note-vide">Pas encore noté</span>
                        <?php } else { ?>
                            <span class="note"><?= formaterNote((float) $u['note']) ?></span>
                        <?php } ?>
                    </td>
                    <td>
                        <?php
                        // L'administrateur ne peut pas supprimer son propre compte
                        if ($u['id'] != idConnecte()) { ?>
                            <form class="en-ligne" method="post" action="index.php?page=admin-supprimer-compte&amp;id=<?= $u['id'] ?>"
                                  data-confirmation="Supprimer le compte de <?= e($u['nom']) ?> ? Ses trajets, réservations et avis seront aussi supprimés.">
                                <?= champCsrf() ?>
                                <button class="bouton bouton-danger bouton-petit" type="submit">Supprimer</button>
                            </form>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>
