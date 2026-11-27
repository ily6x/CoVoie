<?php
/**
 * Vue : profil public d'un membre
 * Variables : $membre (Utilisateur), $note, $nombreAvis, $trajetsRealises, $avisRecus
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var Utilisateur $membre */
/** @var float|null $note */
/** @var int $nombreAvis */
/** @var int $trajetsRealises */
/** @var array $avisRecus */
?>
<div class="carte">
    <div class="profil">
        <span class="avatar avatar-grand"><?= e(initiales($membre->nom)) ?></span>
        <div>
            <h1>
                <?= e($membre->nom) ?>
                <?php if ($membre->role === 'admin') { ?><span class="badge-admin">ADMIN</span><?php } ?>
            </h1>
            <p class="discret">Membre depuis le <?= formaterDate($membre->date_inscription) ?></p>
        </div>

        <div class="profil-chiffres">
            <div>
                <?php if ($note === null) { ?>
                    <b class="note-vide">–</b><small>Pas encore noté</small>
                <?php } else { ?>
                    <b class="note"><?= formaterNote($note) ?></b><small>note moyenne</small>
                <?php } ?>
            </div>
            <div><b><?= $nombreAvis ?></b><small>avis reçus</small></div>
            <div><b><?= $trajetsRealises ?></b><small>trajets réalisés</small></div>
        </div>
    </div>
</div>

<div class="carte">
    <h2>Avis reçus</h2>

    <?php if (count($avisRecus) === 0) { ?>
        <div class="vide"><b>Aucun avis pour le moment</b>La note apparaîtra après un premier trajet terminé.</div>
    <?php } ?>

    <?php foreach ($avisRecus as $avis) { ?>
        <div class="avis">
            <div class="avis-tete">
                <span class="avatar"><?= e(initiales($avis['nom_auteur'])) ?></span>
                <a href="index.php?page=profil&amp;id=<?= $avis['id_auteur'] ?>"><?= e($avis['nom_auteur']) ?></a>
                <small>· <?= e($avis['origine']) ?> → <?= e($avis['destination']) ?> · <?= formaterDate($avis['date_avis']) ?></small>
                <span class="etoiles"><?= etoiles((int) $avis['note']) ?></span>
            </div>
            <?php if ($avis['commentaire'] !== null) { ?>
                <p><?= e($avis['commentaire']) ?></p>
            <?php } ?>
        </div>
    <?php } ?>
</div>
