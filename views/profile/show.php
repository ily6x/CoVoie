<?php require 'views/layouts/header.php'; ?>

<h1>Profil de <?php echo escape(nameLabel($utilisateur->nom)); ?></h1>
<p class="sous-titre">Réputation construite à partir des avis reçus après chaque trajet terminé.</p>

<div class="grille-chiffres">
    <div class="chiffre">
        <div class="chiffre-valeur">
            <?php echo ($noteMoyenne === null) ? '-' : number_format($noteMoyenne, 2, ',', ' '); ?>
        </div>
        <div class="chiffre-texte">Note moyenne sur 5</div>
    </div>
    <div class="chiffre">
        <div class="chiffre-valeur"><?php echo $nombreAvis; ?></div>
        <div class="chiffre-texte">Avis reçus</div>
    </div>
    <div class="chiffre">
        <div class="chiffre-valeur"><?php echo $trajetsRealises; ?></div>
        <div class="chiffre-texte">Trajets réalisés</div>
    </div>
</div>

<h2>Avis reçus</h2>

<?php if (count($avis) === 0) { ?>
    <div class="vide">Cette personne n'a pas encore reçu d'avis.</div>
<?php } else { ?>
    <?php foreach ($avis as $unAvis) { ?>
        <div class="bloc avis">
            <div class="avis-entete">
                <span>
                    <strong><?php echo escape(nameLabel($unAvis->nomAuteur)); ?></strong>
                    <span class="note-vide">
                        - <?php echo escape(placeLabel($unAvis->origine)); ?> &rarr; <?php echo escape(placeLabel($unAvis->destination)); ?>
                    </span>
                </span>
                <span>
                    <span class="etoiles"><?php echo $unAvis->stars(); ?></span>
                    <span class="note-vide"><?php echo date('d/m/Y', strtotime($unAvis->date)); ?></span>
                </span>
            </div>
            <?php if ($unAvis->commentaire !== '' && $unAvis->commentaire !== null) { ?>
                <p><?php echo nl2br(escape(commentLabel($unAvis->commentaire))); ?></p>
            <?php } ?>
        </div>
    <?php } ?>
<?php } ?>

<?php require 'views/layouts/footer.php'; ?>
