<?php require 'views/layouts/header.php'; ?>

<div class="bloc bloc-formulaire">
    <h1>Inscription</h1>
    <p class="sous-titre">Un seul compte : vous serez conducteur ou passager selon les trajets.</p>

    <form method="POST" action="index.php?page=inscription-valider">
        <?php echo csrfField(); ?>
        <p>
            <label for="nom">Nom complet</label>
            <input type="text" id="nom" name="nom" required>
        </p>

        <p>
            <label for="email">Adresse email</label>
            <input type="email" id="email" name="email" required>
        </p>

        <p>
            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required minlength="8">
            <span class="aide">Entre 8 et 72 caractères.</span>
        </p>

        <p>
            <label for="confirmation">Confirmez le mot de passe</label>
            <input type="password" id="confirmation" name="confirmation" required minlength="8">
        </p>

        <p>
            <button type="submit" class="bouton bouton-vert">Créer mon compte</button>
            <a class="bouton bouton-gris" href="index.php?page=connexion">J'ai déjà un compte</a>
        </p>
    </form>
</div>

<?php require 'views/layouts/footer.php'; ?>
