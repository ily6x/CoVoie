<?php
/**
 * Vue : page d'erreur (404, 403...)
 * Variables : $code, $message
 */
?>
<div class="carte erreur">
    <div class="code"><?= $code ?></div>
    <h1>Oups…</h1>
    <p class="sous-titre"><?= e($message) ?></p>
    <a class="bouton" href="index.php">Retour à l'accueil</a>
</div>
