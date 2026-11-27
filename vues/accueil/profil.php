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
<!-- En-tête du profil : avatar, nom, date d'inscription et chiffres -->
<div class="carte">
    <div class="profil">
        <?= avatar($membre->nom, 'avatar-grand') ?>
        <div>
            <h1>
                <?= e($membre->nom) ?>
                <?php if ($membre->role === 'admin') { ?><span class="badge-admin">ADMIN</span><?php } ?>
            </h1>
            <p class="discret">Membre depuis le <?= formaterDate($membre->date_inscription) ?></p>
        </div>

        <div class="profil-chiffres">
            <div>
                <!-- RÈGLE MÉTIER : sans avis, Avis::noteMoyenne() renvoie null -> on affiche "Pas encore noté" et pas 0 -->
                <?php if ($note === null) { ?>
                    <b class="note-vide">-</b><small>Pas encore noté</small>
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

    <!-- Liste des avis reçus, du plus récent au plus ancien -->
    <?php foreach ($avisRecus as $avis) { ?>
        <div class="avis">
            <div class="avis-tete">
                <?= avatar($avis['nom_auteur']) ?>
                <a href="index.php?page=profil&amp;id=<?= $avis['id_auteur'] ?>"><?= e($avis['nom_auteur']) ?></a>
                <small>- <?= e($avis['origine']) ?> → <?= e($avis['destination']) ?> - <?= formaterDate($avis['date_avis']) ?></small>
                <span class="etoiles"><?= etoiles((int) $avis['note']) ?></span>
            </div>
            <!-- Le commentaire est facultatif : il peut être NULL dans la base -->
            <?php if ($avis['commentaire'] !== null) { ?>
                <p><?= e($avis['commentaire']) ?></p>
            <?php } ?>
        </div>
    <?php } ?>
</div>
