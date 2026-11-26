<?php require 'views/layouts/header.php'; ?>

<section class="banniere">
    <div class="hero-contenu">
    <span class="hero-sur-titre"><span></span> Un campus. Des trajets partagés.</span>
    <h1>Le covoiturage,<br>entre étudiants du campus<span class="point-titre">.</span></h1>
    <p>Publiez votre trajet, réservez une place, et notez-vous mutuellement
       pour construire une réputation de confiance.</p>
    <div class="hero-actions">
    <a class="bouton bouton-blanc" href="index.php?page=trajets">Rechercher un trajet <span aria-hidden="true">↗</span></a>
    <?php if (isLoggedIn()) { ?>
        <a class="bouton bouton-transparent" href="index.php?page=publier">Publier un trajet</a>
    <?php } ?>
    </div>
    </div>
    <div class="hero-carte" aria-hidden="true">
        <?php require 'views/layouts/campus-map.svg'; ?>
        <div class="carte-flottante">
            <span class="carte-surtitre">La route se partage.</span>
            <div class="carte-mini-voiture"><img src="images/hero-car.png" alt=""></div>
            <div class="carte-etapes"><i></i><span></span><i></i><span></span><i></i></div>
            <div class="carte-legende"><span>Départ</span><span>Ensemble</span><span>Campus</span></div>
        </div>
        <span class="carte-label label-depart"><i></i> Votre départ</span>
        <span class="carte-label label-campus"><i></i> Le campus</span>
    </div>
    <div class="hero-bas"><span>Moins de voitures. Plus de rencontres.</span><span aria-hidden="true">↓</span></div>
</section>

<div class="grille-chiffres">
    <div class="chiffre">
        <div class="chiffre-valeur"><?php echo $chiffres['total_trajets']; ?></div>
        <div class="chiffre-texte">Trajets publiés</div>
    </div>
    <div class="chiffre">
        <div class="chiffre-valeur"><?php echo $chiffres['trajets_realises']; ?></div>
        <div class="chiffre-texte">Trajets réalisés</div>
    </div>
    <div class="chiffre">
        <div class="chiffre-valeur"><?php echo $chiffres['total_membres']; ?></div>
        <div class="chiffre-texte">Étudiants inscrits</div>
    </div>
    <div class="chiffre">
        <div class="chiffre-valeur">
            <?php echo ($chiffres['note_moyenne'] === null) ? '-' : number_format($chiffres['note_moyenne'], 2, ',', ' '); ?>
        </div>
        <div class="chiffre-texte">Note moyenne</div>
    </div>
</div>

<div class="deux-colonnes">
    <div>
        <h2>Prochains départs</h2>

        <?php if (count($trajets) === 0) { ?>
            <div class="vide">Aucun trajet ouvert pour le moment.</div>
        <?php } else { ?>
            <?php foreach ($trajets as $trajet) { ?>
                <?php require 'views/layouts/trip-card.php'; ?>
            <?php } ?>
        <?php } ?>
    </div>

    <div>
        <h2>Meilleures réputations</h2>
        <div class="bloc">
            <?php if (count($meilleurs) === 0) { ?>
                <p class="note-vide">Aucun avis pour le moment.</p>
            <?php } else { ?>
                <?php foreach ($meilleurs as $membre) { ?>
                    <div class="ligne-classement">
                        <a href="index.php?page=profil&amp;id=<?php echo $membre['id']; ?>">
                            <?php echo escape(nameLabel($membre['nom'])); ?>
                        </a>
                        <span class="etoiles">&#9733;</span>
                        <?php echo number_format($membre['moyenne'], 2, ',', ' '); ?>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
