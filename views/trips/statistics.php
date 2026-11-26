<?php require 'views/layouts/header.php'; ?>

<h1>Mes statistiques de conducteur</h1>
<p class="sous-titre">Back-office du module A.</p>

<div class="grille-chiffres">
    <div class="chiffre">
        <div class="chiffre-valeur"><?php echo (int) $stats['realises']; ?></div>
        <div class="chiffre-texte">Trajets réalisés</div>
    </div>
    <div class="chiffre">
        <div class="chiffre-valeur"><?php echo (int) $stats['publies']; ?></div>
        <div class="chiffre-texte">Trajets publiés</div>
    </div>
    <div class="chiffre">
        <div class="chiffre-valeur"><?php echo (int) $stats['places_occupees']; ?></div>
        <div class="chiffre-texte">Places occupees</div>
    </div>
    <div class="chiffre">
        <div class="chiffre-valeur"><?php echo number_format($tauxRemplissage, 1, ',', ' '); ?> %</div>
        <div class="chiffre-texte">Taux de remplissage</div>
    </div>
    <div class="chiffre">
        <div class="chiffre-valeur">
            <?php echo ($maNote === null) ? '-' : number_format($maNote, 2, ',', ' '); ?>
        </div>
        <div class="chiffre-texte">Ma note moyenne</div>
    </div>
</div>

<div class="bloc">
    <h2>Mon passager le plus frequent</h2>
    <?php if ($passagerFrequent === false) { ?>
        <p class="note-vide">Aucun passager accepté pour le moment.</p>
    <?php } else { ?>
        <p>
            <a href="index.php?page=profil&amp;id=<?php echo $passagerFrequent['id']; ?>">
                <strong><?php echo escape(nameLabel($passagerFrequent['nom'])); ?></strong>
            </a>
            - <?php echo $passagerFrequent['nb']; ?> trajet(s) ensemble
        </p>
    <?php } ?>
</div>

<?php require 'views/layouts/footer.php'; ?>
