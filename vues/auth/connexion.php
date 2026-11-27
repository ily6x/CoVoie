<?php
/**
 * Vue : formulaire de connexion
 * Variables : $email (pré-rempli après une erreur)
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var string $email */
?>
<div class="carte formulaire-centre">
    <img class="logo-formulaire" src="images/logo.svg" alt="">
    <h1>Connexion</h1>
    <p class="sous-titre">Heureux de vous revoir sur CoVoie.</p>

    <!-- Formulaire envoyé en POST vers AuthController::connecter() -->
    <form method="post" action="index.php?page=connexion-valider">
        <?= champCsrf() ?>

        <div class="groupe">
            <label for="email">Adresse email</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" required autofocus>
        </div>
        <div class="groupe">
            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        </div>

        <button class="bouton bouton-large" type="submit">Se connecter</button>
    </form>

    <p class="discret lien-bas">
        Pas encore de compte ? <a href="index.php?page=inscription">Créer un compte</a>
    </p>

    <!-- Aide pour la démonstration (à retirer en production) -->
    <div class="demo">
        <strong>Comptes de démonstration</strong> - mot de passe : <code>Password123!</code><br>
        Admin : ilyes.karim@campus.fr<br>
        Conducteur : sarah.benjelloun@campus.fr - Passagère : emma.girard@campus.fr
    </div>
</div>
