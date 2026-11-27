<?php
/**
 * Vue : formulaire d'inscription
 * Variables : $nom, $email (pré-remplis après une erreur), $erreurs (liste de messages)
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var array $erreurs */
/** @var string $nom */
/** @var string $email */
?>
<div class="carte formulaire-centre">
    <h1>Créer un compte</h1>
    <p class="sous-titre">Un seul compte pour conduire et pour voyager.</p>

    <?php if (count($erreurs) > 0) { ?>
        <div class="message message-erreur">
            Le formulaire contient des erreurs :
            <ul>
                <?php foreach ($erreurs as $erreur) { ?>
                    <li><?= e($erreur) ?></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>

    <form method="post" action="index.php?page=inscription-valider">
        <?= champCsrf() ?>

        <div class="groupe">
            <label for="nom">Prénom et nom</label>
            <input type="text" id="nom" name="nom" value="<?= e($nom) ?>" minlength="2" maxlength="100" required>
        </div>
        <div class="groupe">
            <label for="email">Adresse email</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" maxlength="180" required>
        </div>
        <div class="deux-champs">
            <div class="groupe">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" minlength="8" maxlength="72" required>
            </div>
            <div class="groupe">
                <label for="confirmation">Confirmation</label>
                <input type="password" id="confirmation" name="confirmation" minlength="8" maxlength="72" required>
            </div>
        </div>
        <p class="aide">8 caractères minimum.</p>

        <button class="bouton bouton-large" type="submit">Créer mon compte</button>
    </form>

    <p class="discret lien-bas">
        Déjà inscrit ? <a href="index.php?page=connexion">Se connecter</a>
    </p>
</div>
