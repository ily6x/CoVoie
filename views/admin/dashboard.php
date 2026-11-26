<?php require 'views/layouts/header.php'; ?>

<h1>Espace administration</h1>
<p class="sous-titre">Statistiques globales de la plateforme.</p>

<p>
    <a class="bouton bouton-gris" href="index.php?page=admin-avis">Modération des avis</a>
    <a class="bouton bouton-gris" href="index.php?page=admin-utilisateurs">Gestion des comptes</a>
    <a class="bouton bouton-gris" href="index.php?page=admin-trajets">Tous les trajets</a>
</p>

<div class="grille-chiffres">
    <div class="chiffre">
        <div class="chiffre-valeur"><?php echo (int) $trajets['realises']; ?></div>
        <div class="chiffre-texte">Trajets réalisés</div>
    </div>
    <div class="chiffre">
        <div class="chiffre-valeur"><?php echo number_format($tauxRemplissage, 1, ',', ' '); ?> %</div>
        <div class="chiffre-texte">Remplissage moyen</div>
    </div>
    <div class="chiffre">
        <div class="chiffre-valeur"><?php echo $autres['membres']; ?></div>
        <div class="chiffre-texte">Comptes</div>
    </div>
    <div class="chiffre">
        <div class="chiffre-valeur"><?php echo $trajets['total_trajets']; ?></div>
        <div class="chiffre-texte">Trajets au total</div>
    </div>
    <div class="chiffre">
        <div class="chiffre-valeur"><?php echo $autres['reservations']; ?></div>
        <div class="chiffre-texte">Réservations</div>
    </div>
    <div class="chiffre">
        <div class="chiffre-valeur"><?php echo $autres['avis']; ?></div>
        <div class="chiffre-texte">Avis deposes</div>
    </div>
</div>

<div class="bloc">
    <h2>Détail</h2>
    <table>
        <tr><th>Trajets ouverts</th><td><?php echo (int) $trajets['ouverts']; ?></td></tr>
        <tr><th>Trajets complets</th><td><?php echo (int) $trajets['complets']; ?></td></tr>
        <tr><th>Réservations confirmées</th><td><?php echo $autres['confirmees']; ?></td></tr>
        <tr><th>Réservations en attente</th><td><?php echo $autres['en_attente']; ?></td></tr>
        <tr>
            <th>Note moyenne de la plateforme</th>
            <td>
                <?php echo ($autres['note_moyenne'] === null)
                    ? '-'
                    : number_format($autres['note_moyenne'], 2, ',', ' ') . ' / 5'; ?>
            </td>
        </tr>
    </table>
</div>

<?php require 'views/layouts/footer.php'; ?>
