<?php
/**
 * Vue : page d'erreur (404, 403...)
 * Variables : $code, $message
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var int $code */
/** @var string $message */

// Un titre court selon le code HTTP
$titres = [400 => 'Demande invalide', 403 => 'Accès refusé', 404 => 'Page introuvable', 405 => 'Action refusée', 500 => 'Service interrompu'];
$titreErreur = 'Erreur';
if (isset($titres[$code])) {
    $titreErreur = $titres[$code];
}
?>
<div class="erreur-page">
    <div class="erreur-panneau">Erreur <?= $code ?> - <?= $titreErreur ?></div>
    <h1>Oups…</h1>
    <p><?= e($message) ?></p>
    <a class="bouton bouton--principal" href="index.php">Retour à l'accueil</a>
</div>
