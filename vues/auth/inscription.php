<?php
/**
 * Vue : formulaire d'inscription
 * Variables : $nom, $email (pré-remplis après une erreur), $erreurs (erreurs rangées par nom de champ)
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var array $erreurs */
/** @var string $nom */
/** @var string $email */
?>
<div class="authentification-page">
    <div class="entete-page">
        <div>
            <p class="surtitre">Mon compte</p>
            <h1>Créer un compte</h1>
            <p>Un seul compte pour conduire et pour voyager.</p>
        </div>
    </div>

    <div class="authentification">
        <section>
            <!-- novalidate : on laisse PHP vérifier, pour afficher nos messages sous chaque champ -->
            <form class="bloc" method="post" action="index.php?page=inscription-valider" novalidate>
                <?= champCsrf() ?>

                <div class="<?= classeChamp($erreurs, 'nom') ?>">
                    <label for="nom">Prénom et nom</label>
                    <input type="text" id="nom" name="nom"<?= attributsErreur($erreurs, 'nom') ?> value="<?= e($nom) ?>" maxlength="100" autocomplete="name" required>
                    <?= erreurChamp($erreurs, 'nom') ?>
                </div>
                <div class="<?= classeChamp($erreurs, 'email') ?>">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email"<?= attributsErreur($erreurs, 'email') ?> value="<?= e($email) ?>" maxlength="180" autocomplete="email" required>
                    <?= erreurChamp($erreurs, 'email') ?>
                </div>
                <div class="deux-champs">
                    <div class="<?= classeChamp($erreurs, 'mot_de_passe') ?>">
                        <label for="mot_de_passe">Mot de passe</label>
                        <input type="password" id="mot_de_passe" name="mot_de_passe"<?= attributsErreur($erreurs, 'mot_de_passe') ?> maxlength="72" autocomplete="new-password" required>
                        <?= erreurChamp($erreurs, 'mot_de_passe') ?>
                    </div>
                    <div class="<?= classeChamp($erreurs, 'confirmation') ?>">
                        <label for="confirmation">Confirmation</label>
                        <input type="password" id="confirmation" name="confirmation"<?= attributsErreur($erreurs, 'confirmation') ?> maxlength="72" autocomplete="new-password" required>
                        <?= erreurChamp($erreurs, 'confirmation') ?>
                    </div>
                </div>

                <button class="bouton bouton--principal bouton--large" type="submit">Créer mon compte</button>
                <p class="lien-bas">Déjà inscrit ? <a href="index.php?page=connexion">Se connecter</a></p>
            </form>
        </section>

        <!-- Côté droit : illustration et règles vérifiées par Utilisateur::inscrire() -->
        <aside class="authentification-cote">
            <img class="authentification-illustration" src="images/illustration-inscription.svg" alt="Deux étudiants marchent ensemble">
            <div class="bloc bloc--doux">
            <p class="surtitre">À savoir</p>
            <ul class="avis-liste">
                <li><b>Une adresse email = un compte</b><span class="avis-contexte">L'adresse ne doit pas déjà être utilisée.</span></li>
                <li><b>Mot de passe de 8 caractères minimum</b><span class="avis-contexte">Il est enregistré chiffré (password_hash), jamais en clair.</span></li>
                <li><b>Conducteur et passager à la fois</b><span class="avis-contexte">Publiez vos trajets ou réservez ceux des autres avec le même compte.</span></li>
            </ul>
            </div>
        </aside>
    </div>
</div>
