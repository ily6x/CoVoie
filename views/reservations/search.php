<?php require 'views/layouts/header.php'; ?>

<h1>Rechercher un trajet</h1>
<p class="sous-titre">Seuls les trajets ouverts et à venir sont affichés.</p>

<div class="bloc">
    <!-- Formulaire en GET : les filtres restent visibles dans l'URL,
         la recherche est donc partageable (cours "La Methode GET"). -->
    <form method="GET" action="index.php" class="formulaire-filtres">
        <input type="hidden" name="page" value="trajets">

        <div>
            <label for="origine">Ville de départ</label>
            <input type="text" id="origine" name="origine"
                   value="<?php echo escape($origine); ?>" placeholder="Campus Nord">
        </div>

        <div>
            <label for="destination">Ville d'arrivée</label>
            <input type="text" id="destination" name="destination"
                   value="<?php echo escape($destination); ?>" placeholder="Gare Centrale">
        </div>

        <div>
            <label for="date">Date</label>
            <input type="date" id="date" name="date" value="<?php echo escape($date); ?>">
        </div>

        <div>
            <button type="submit" class="bouton bouton-vert">Filtrer</button>
            <a class="bouton bouton-gris" href="index.php?page=trajets">Tout voir</a>
        </div>
    </form>
</div>

<h2><?php echo count($trajets); ?> trajet<?php echo (count($trajets) > 1) ? 's' : ''; ?> disponible<?php echo (count($trajets) > 1) ? 's' : ''; ?></h2>

<?php if (count($trajets) === 0) { ?>
    <div class="vide">Aucun trajet ne correspond à votre recherche.</div>
<?php } else { ?>
    <?php foreach ($trajets as $trajet) { ?>
        <?php require 'views/layouts/trip-card.php'; ?>
    <?php } ?>
<?php } ?>

<?php require 'views/layouts/footer.php'; ?>
