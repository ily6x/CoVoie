<?php require 'views/layouts/header.php'; ?>

<div class="bloc bloc-formulaire">
    <h1>Connexion</h1>
    <p class="sous-titre">Accédez à vos trajets et à vos réservations.</p>

    <form method="POST" action="index.php?page=connexion-valider">
        <?php echo csrfField(); ?>
        <p>
            <label for="email">Adresse email</label>
            <input type="email" id="email" name="email" required
                   placeholder="prenom.nom@campus.fr">
        </p>

        <p>
            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        </p>

        <p>
            <button type="submit" class="bouton bouton-vert">Se connecter</button>
            <a class="bouton bouton-gris" href="index.php?page=inscription">Créer un compte</a>
        </p>
    </form>
</div>

<?php require 'views/layouts/footer.php'; ?>
