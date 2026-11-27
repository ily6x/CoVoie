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

// Est-ce mon propre profil ? (on affiche alors les onglets de mon espace et la déconnexion)
$monProfil = ($membre->id === idConnecte());
?>
<div class="entete-page">
    <div class="profil">
        <?= avatar($membre->nom, 'avatar--grand') ?>
        <div>
            <p class="surtitre"><?= $monProfil ? 'Mon profil public' : 'Profil public' ?></p>
            <h1>
                <?= e($membre->nom) ?>
                <?php if ($membre->role === 'admin') { ?><span class="badge-admin">ADMIN</span><?php } ?>
            </h1>
            <p>Membre depuis le <?= formaterDate($membre->date_inscription) ?></p>
        </div>
    </div>
    <?php if ($monProfil) { ?>
        <!-- Sur téléphone, la déconnexion du header est masquée : on la propose aussi ici -->
        <form class="entete-actions" method="post" action="index.php?page=deconnexion">
            <?= champCsrf() ?>
            <button class="bouton bouton--secondaire" type="submit">Se déconnecter</button>
        </form>
    <?php } ?>
</div>

<?php if ($monProfil) {
    require 'vues/gabarit/onglets-espace.php';
} ?>

<!-- Réputation : RÈGLE MÉTIER, sans avis noteMoyenne() renvoie null -> "Pas encore noté" et pas 0 -->
<section class="indicateurs" aria-label="Réputation">
    <div class="indicateur">
        <?php if ($note === null) { ?>
            <b>-</b><span>Pas encore noté</span>
        <?php } else { ?>
            <b><?= number_format($note, 2, ',', ' ') ?></b><span>note moyenne sur 5</span>
        <?php } ?>
    </div>
    <div class="indicateur"><b><?= $nombreAvis ?></b><span>avis reçus</span></div>
    <div class="indicateur"><b><?= $trajetsRealises ?></b><span>trajets réalisés</span></div>
    <div class="indicateur"><b><?= formaterDate($membre->date_inscription) ?></b><span>date d'inscription</span></div>
</section>

<section class="section">
    <h2>Avis reçus</h2>

    <?php if (count($avisRecus) === 0) { ?>
        <div class="vide">
            <img class="vide-illustration" src="images/illustration-vide.svg" alt="">
            <h3>Aucun avis pour le moment</h3>
            <p>La note apparaîtra après un premier trajet terminé.</p>
        </div>
    <?php } else { ?>
        <div class="bloc">
            <ul class="avis-liste">
                <?php foreach ($avisRecus as $avis) { ?>
                    <li>
                        <div class="avis-tete">
                            <span class="personne">
                                <?= avatar($avis['nom_auteur'], 'avatar--petit') ?>
                                <a class="personne-nom" href="index.php?page=profil&amp;id=<?= $avis['id_auteur'] ?>"><?= e($avis['nom_auteur']) ?></a>
                            </span>
                            <!-- Les étoiles, doublées d'un texte pour les lecteurs d'écran -->
                            <span class="etoiles" aria-hidden="true"><?= etoiles((int) $avis['note']) ?></span>
                            <span class="visuellement-cache"><?= $avis['note'] ?> sur 5</span>
                        </div>
                        <span class="avis-contexte"><?= e($avis['origine']) ?> → <?= e($avis['destination']) ?> - <?= formaterDate($avis['date_avis']) ?></span>
                        <?php
                        // Le commentaire est facultatif : il peut être NULL dans la base
                        if ($avis['commentaire'] !== null) { ?>
                            <p><?= e($avis['commentaire']) ?></p>
                        <?php } ?>
                    </li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>
</section>
