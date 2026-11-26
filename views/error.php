<?php require 'views/layouts/header.php'; ?>
<div class="bloc"><h1><?php echo escape($titre); ?></h1><p><?php echo escape($erreur); ?></p><a class="bouton bouton-gris" href="index.php?page=accueil">Accueil</a> <a class="bouton bouton-vert" href="index.php?page=connexion">Connexion</a></div>
<?php require 'views/layouts/footer.php'; ?>
