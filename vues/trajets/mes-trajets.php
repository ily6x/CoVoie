<?php
/**
 * Vue : "Mes trajets" (espace conducteur)
 * Variables : $demandes (demandes en attente sur mes trajets à venir), $trajets (tous mes trajets)
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var array $demandes */
/** @var array $trajets */
?>
<div class="entete-page">
    <div>
        <p class="surtitre">Espace conducteur</p>
        <h1>Mes trajets</h1>
        <p>Les trajets que vous conduisez et les demandes de place à traiter.</p>
    </div>
</div>

<?php require 'vues/gabarit/onglets-espace.php'; ?>

<!-- ===== Demandes en attente : n'apparaît que s'il y en a sur des trajets à venir ===== -->
<?php if (count($demandes) > 0) { ?>
    <section class="section">
        <div class="section-titre">
            <h2>À traiter</h2>
            <span class="plaque statut-en-attente"><?= count($demandes) ?> demande<?= count($demandes) > 1 ? 's' : '' ?></span>
        </div>
        <div class="defilant">
            <table class="donnees">
                <thead>
                    <tr><th>Passager</th><th>Trajet</th><th>Départ</th><th>Places libres</th><th><span class="visuellement-cache">Actions</span></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($demandes as $demande) { ?>
                        <tr>
                            <td>
                                <span class="personne">
                                    <?= avatar($demande['nom_passager'], 'avatar--petit') ?>
                                    <a class="personne-nom" href="index.php?page=profil&amp;id=<?= $demande['id_passager'] ?>"><?= e($demande['nom_passager']) ?></a>
                                </span>
                            </td>
                            <td><a href="index.php?page=trajet&amp;id=<?= $demande['id_trajet'] ?>"><?= e($demande['origine']) ?> → <?= e($demande['destination']) ?></a></td>
                            <td class="date"><?= formaterJour($demande['date_heure']) ?> - <?= formaterHeure($demande['date_heure']) ?></td>
                            <td class="chiffre"><?= $demande['places_restantes'] ?></td>
                            <td>
                                <div class="actions">
                                    <?php
                                    // On ne propose "Accepter" que s'il reste au moins une place
                                    if ($demande['places_restantes'] > 0) { ?>
                                        <form class="en-ligne" method="post" action="index.php?page=accepter&amp;id=<?= $demande['id_trajet'] ?>&amp;reservation=<?= $demande['id'] ?>">
                                            <?= champCsrf() ?>
                                            <!-- Après l'action, on revient sur cette page -->
                                            <input type="hidden" name="retour" value="mes-trajets">
                                            <button class="bouton bouton--principal bouton--petit" type="submit">Accepter</button>
                                        </form>
                                    <?php } ?>
                                    <form class="en-ligne" method="post" action="index.php?page=refuser&amp;id=<?= $demande['id_trajet'] ?>&amp;reservation=<?= $demande['id'] ?>">
                                        <?= champCsrf() ?>
                                        <input type="hidden" name="retour" value="mes-trajets">
                                        <button class="bouton bouton--danger bouton--petit" type="submit">Refuser</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </section>
<?php } ?>

<!-- ===== Tous mes trajets, sous forme de cartes (avec le statut) ===== -->
<section class="section">
    <div class="section-titre">
        <h2>Trajets publiés</h2>
        <span class="discret"><?= count($trajets) ?> trajet(s), du plus récent au plus ancien</span>
    </div>

    <?php if (count($trajets) === 0) { ?>
        <div class="vide">
            <img class="vide-illustration" src="images/illustration-vide.svg" alt="">
            <h3>Vous n'avez publié aucun trajet</h3>
            <p>Proposez vos places libres aux autres étudiants.</p>
            <a class="bouton bouton--principal" href="index.php?page=publier">Publier mon premier trajet</a>
        </div>
    <?php } else {
        // Paramètres du gabarit : cartes avec le statut du trajet
        $modeConducteur = true;
        require 'vues/gabarit/cartes-trajets.php';
    } ?>
</section>
