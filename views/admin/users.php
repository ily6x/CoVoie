<?php require 'views/layouts/header.php'; ?>

<h1>Gestion des comptes</h1>
<p class="sous-titre">Supprimer un compte supprime aussi ses trajets, ses réservations et ses avis.</p>

<div class="bloc">
    <table>
        <tr>
            <th>Nom</th><th>Email</th><th>Rôle</th>
            <th>Note moyenne</th><th>Trajets réalisés</th><th></th>
        </tr>
        <?php foreach ($utilisateurs as $membre) { ?>
            <tr>
                <td>
                    <a href="index.php?page=profil&amp;id=<?php echo $membre->id; ?>">
                        <?php echo escape(nameLabel($membre->nom)); ?>
                    </a>
                </td>
                <td><?php echo escape($membre->email); ?></td>
                <td>
                    <span class="badge <?php echo ($membre->role === 'admin') ? 'badge-complet' : 'badge-termine'; ?>">
                        <?php echo $membre->role; ?>
                    </span>
                </td>
                <td><?php echo formatRating($membre->noteMoyenne(), $membre->nombreAvis()); ?></td>
                <td><?php echo $membre->completedTripCount(); ?></td>
                <td>
                    <?php if ($membre->id != currentUserId()) { ?>
                        <form class="action-inline" method="POST" action="index.php?page=admin-supprimer-compte&amp;id=<?php echo $membre->id; ?>" onsubmit="return confirm('Supprimer définitivement ce compte ?');">
                            <?php echo csrfField(); ?>
                            <button type="submit" class="bouton bouton-rouge bouton-petit">Supprimer</button>
                        </form>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

<?php require 'views/layouts/footer.php'; ?>
