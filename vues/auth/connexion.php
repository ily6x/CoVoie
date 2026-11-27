<?php
/**
 * Vue : formulaire de connexion
 * Variables : $email (pré-rempli après une erreur)
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var string $email */
?>
<div class="authentification-page">
    <div class="entete-page">
        <div>
            <p class="surtitre">Mon compte</p>
            <h1>Connexion</h1>
            <p>Un seul compte pour conduire et pour voyager.</p>
        </div>
    </div>

    <div class="authentification">
        <section>
            <!-- Formulaire envoyé en POST vers AuthController::connecter() -->
            <form class="bloc" method="post" action="index.php?page=connexion-valider">
                <?= champCsrf() ?>

                <div class="champ">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" value="<?= e($email) ?>" autocomplete="email" required autofocus>
                </div>
                <div class="champ">
                    <label for="mot_de_passe">Mot de passe</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" autocomplete="current-password" required>
                </div>

                <button class="bouton bouton--principal bouton--large" type="submit">Se connecter</button>
                <p class="lien-bas">Pas encore de compte ? <a href="index.php?page=inscription">Créer un compte</a></p>
            </form>
        </section>

        <!-- Côté droit : illustration et comptes de démonstration du jeu de données -->
        <aside class="authentification-cote">
            <img class="authentification-illustration" src="images/illustration-connexion.svg" alt="Un étudiant ouvre une porte">
            <div class="bloc bloc--doux">
                <p class="surtitre">Comptes de démonstration</p>
                <p class="discret">Mot de passe commun : <code>Password123!</code></p>
                <ul class="comptes-demo">
                    <li><span>Admin</span><code>ilyes.karim@campus.fr</code></li>
                    <li><span>Conductrice</span><code>sarah.benjelloun@campus.fr</code></li>
                    <li><span>Passagère</span><code>emma.girard@campus.fr</code></li>
                    <li><span>Bien noté</span><code>bilel.mansouri@campus.fr</code></li>
                    <li><span>Sans avis</span><code>hugo.petit@campus.fr</code></li>
                </ul>
            </div>
        </aside>
    </div>
</div>
