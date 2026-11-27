<?php
/**
 * Vue : détail d'un trajet
 * Variables : $trajet (Trajet), $conducteur (Utilisateur), $noteConducteur, $nombreAvisConducteur,
 *             $estConducteur (bool), $demandes (liste, seulement pour le conducteur),
 *             $maDemande (statut de ma demande, ou null)
 */
// Variables transmises par le contrôleur (afficherVue) : ces lignes les décrivent pour l'éditeur
/** @var Trajet $trajet */
/** @var bool $estConducteur */
/** @var array $demandes */
/** @var Utilisateur $conducteur */
/** @var float|null $noteConducteur */
/** @var int $nombreAvisConducteur */
/** @var string|null $maDemande */

// Étapes du cycle de vie : quelles étapes sont atteintes ?
$etapeComplet = ($trajet->statut === 'Complet' || $trajet->statut === 'Termine');
$etapeTermine = ($trajet->statut === 'Termine');
$placesPrises = $trajet->placesOccupees();
?>
<a class="retour" href="index.php?page=trajets">← Tous les départs</a>

<div class="entete-page">
    <div>
        <p class="surtitre">
            Trajet n° <?= sprintf('%04d', $trajet->id) ?> - <?= sensTrajet($trajet->origine, $trajet->destination) ?>
            <span class="plaque <?= classeStatut($trajet->statut) ?>"><?= libelleStatut($trajet->statut) ?></span>
        </p>
        <h1><?= e($trajet->origine) ?> <span class="fleche" aria-hidden="true">→</span><span class="visuellement-cache"> vers </span> <?= e($trajet->destination) ?></h1>
        <p><?= ucfirst(formaterDateLongue($trajet->date_heure)) ?>, départ à <?= formaterHeure($trajet->date_heure) ?></p>
    </div>
</div>

<div class="gabarit-detail">
    <div>
        <!-- ===== Itinéraire : frise verticale départ -> arrivée ===== -->
        <section class="section">
            <h2>Itinéraire</h2>
            <div class="bloc">
                <ol class="frise">
                    <li>
                        <span class="frise-heure"><?= formaterHeure($trajet->date_heure) ?></span>
                        <span class="frise-lieu"><?= e($trajet->origine) ?></span>
                        <small>Départ</small>
                    </li>
                    <li>
                        <span class="frise-lieu"><?= e($trajet->destination) ?></span>
                        <small>Arrivée</small>
                    </li>
                </ol>

                <!-- Cycle de vie du trajet : Ouvert -> Complet -> Terminé -->
                <div class="bloc-pied">
                    <p class="cycle">
                        <span class="<?= $trajet->statut === 'Ouvert' ? 'actuel' : 'fait' ?>">Ouvert</span>
                        <span class="separateur" aria-hidden="true">→</span>
                        <span class="<?= $trajet->statut === 'Complet' ? 'actuel' : ($etapeComplet ? 'fait' : '') ?>">Complet</span>
                        <span class="separateur" aria-hidden="true">→</span>
                        <span class="<?= $etapeTermine ? 'actuel' : '' ?>">Terminé</span>
                    </p>
                    <p class="discret sans-marge">
                        <?php if ($etapeTermine) { ?>
                            Le trajet est terminé : les participants peuvent se noter.
                        <?php } else { ?>
                            Le trajet passera « Terminé » automatiquement à l'heure du départ.
                        <?php } ?>
                    </p>
                </div>
            </div>
        </section>

        <?php
        // ===== Demandes de réservation : visibles UNIQUEMENT par le conducteur =====
        if ($estConducteur) { ?>
            <section class="section">
                <div class="section-titre">
                    <h2>Demandes de réservation</h2>
                    <span class="discret"><?= count($demandes) ?> au total</span>
                </div>
                <p class="discret">Accepter une demande retire une place du trajet. À zéro place, le trajet passe « Complet ».</p>

                <?php if (count($demandes) === 0) { ?>
                    <div class="vide">
                        <img class="vide-illustration" src="images/illustration-vide.svg" alt="">
                        <h3>Aucune demande pour l'instant</h3>
                        <p>Les demandes des passagers apparaîtront ici.</p>
                    </div>
                <?php } else { ?>
                    <div class="defilant">
                        <table class="donnees">
                            <thead>
                                <tr><th>Passager</th><th>Demande du</th><th>Statut</th><th><span class="visuellement-cache">Actions</span></th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($demandes as $demande) { ?>
                                    <tr>
                                        <td>
                                            <span class="personne">
                                                <?= avatar($demande['nom_passager']) ?>
                                                <span>
                                                    <a class="personne-nom" href="index.php?page=profil&amp;id=<?= $demande['id_passager'] ?>"><?= e($demande['nom_passager']) ?></a>
                                                    <!-- La note moyenne du passager aide le conducteur à décider -->
                                                    <?php if ($demande['note_passager'] === null) { ?>
                                                        <small class="note-vide">Pas encore noté</small>
                                                    <?php } else { ?>
                                                        <small class="note"><?= formaterNote((float) $demande['note_passager']) ?></small>
                                                    <?php } ?>
                                                </span>
                                            </span>
                                        </td>
                                        <td class="date"><?= formaterDate($demande['date_reservation']) ?></td>
                                        <td><span class="plaque <?= classeStatut($demande['statut']) ?>"><?= libelleStatut($demande['statut']) ?></span></td>
                                        <td>
                                            <?php
                                            // Boutons seulement pour une demande en attente sur un trajet pas encore parti
                                            if ($demande['statut'] === 'En attente' && $trajet->estModifiable()) { ?>
                                                <div class="actions">
                                                    <?php if ($trajet->places_restantes > 0) { ?>
                                                        <form class="en-ligne" method="post" action="index.php?page=accepter&amp;id=<?= $trajet->id ?>&amp;reservation=<?= $demande['id'] ?>">
                                                            <?= champCsrf() ?>
                                                            <button class="bouton bouton--principal bouton--petit" type="submit">Accepter</button>
                                                        </form>
                                                    <?php } ?>
                                                    <form class="en-ligne" method="post" action="index.php?page=refuser&amp;id=<?= $trajet->id ?>&amp;reservation=<?= $demande['id'] ?>">
                                                        <?= champCsrf() ?>
                                                        <button class="bouton bouton--danger bouton--petit" type="submit">Refuser</button>
                                                    </form>
                                                </div>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
            </section>
        <?php } ?>
    </div>

    <!-- ===== Panneau d'action : reste visible pendant le défilement ===== -->
    <aside class="panneau-action">
        <div class="bloc">
            <div class="panneau-prix">
                <span class="prix prix--grand"><?= formaterPrix($trajet->prix) ?></span>
                <span class="sieges" title="<?= $trajet->places_restantes ?> place(s) libre(s) sur <?= $trajet->places_totales ?>">
                    <?php for ($i = 1; $i <= $trajet->places_totales; $i++) { ?>
                        <i class="siege<?= $i <= $placesPrises ? ' pris' : '' ?>"></i>
                    <?php } ?>
                    <span class="sieges-texte"><?= $trajet->places_restantes ?> sur <?= $trajet->places_totales ?> libre<?= $trajet->places_restantes > 1 ? 's' : '' ?></span>
                </span>
            </div>

            <?php
            // L'action proposée dépend de QUI regarde la page
            if (!estConnecte()) { ?>
                <h3>Réserver</h3>
                <p class="discret">Connectez-vous pour demander une place.</p>
                <a class="bouton bouton--principal bouton--large" href="index.php?page=connexion">Se connecter</a>

            <?php } elseif ($estConducteur) { ?>
                <h3>Gérer mon trajet</h3>
                <div class="actions">
                    <?php if ($trajet->estModifiable()) { ?>
                        <a class="bouton bouton--secondaire bouton--petit" href="index.php?page=modifier&amp;id=<?= $trajet->id ?>">Modifier</a>
                    <?php } ?>

                    <?php if ($trajet->statut === 'Termine') { ?>
                        <a class="bouton bouton--principal bouton--petit" href="index.php?page=noter&amp;id=<?= $trajet->id ?>">Noter mes passagers</a>
                    <?php } ?>

                    <!-- data-confirmation : le JavaScript demande confirmation avant l'envoi -->
                    <form class="en-ligne" method="post" action="index.php?page=supprimer-trajet&amp;id=<?= $trajet->id ?>"
                          data-confirmation="Supprimer définitivement ce trajet et toutes ses réservations ?">
                        <?= champCsrf() ?>
                        <button class="bouton bouton--danger bouton--petit" type="submit">Supprimer</button>
                    </form>
                </div>

            <?php } elseif ($maDemande !== null) { ?>
                <h3>Ma demande</h3>
                <p><span class="plaque <?= classeStatut($maDemande) ?>"><?= libelleStatut($maDemande) ?></span></p>
                <?php if ($maDemande === 'Confirmee' && $trajet->statut === 'Termine') { ?>
                    <a class="bouton bouton--principal bouton--large" href="index.php?page=noter&amp;id=<?= $trajet->id ?>">Noter le conducteur</a>
                <?php } else { ?>
                    <a class="bouton bouton--secondaire bouton--large" href="index.php?page=mes-reservations">Voir mes réservations</a>
                <?php } ?>

            <?php } elseif ($trajet->statut !== 'Ouvert' || $trajet->places_restantes < 1 || $trajet->estParti()) { ?>
                <h3>Réservation impossible</h3>
                <p class="discret sans-marge">Ce trajet est complet, déjà parti ou terminé.</p>

            <?php } else { ?>
                <h3>Réserver une place</h3>
                <p class="discret">Le conducteur recevra votre demande et pourra l'accepter ou la refuser.</p>
                <form method="post" action="index.php?page=reserver&amp;id=<?= $trajet->id ?>">
                    <?= champCsrf() ?>
                    <button class="bouton bouton--principal bouton--large" type="submit">Demander une place</button>
                </form>
            <?php } ?>
        </div>

        <!-- ===== Le conducteur et sa réputation ===== -->
        <div class="bloc">
            <p class="surtitre">Conducteur</p>
            <div class="personne">
                <?= avatar($conducteur->nom) ?>
                <span>
                    <a class="personne-nom" href="index.php?page=profil&amp;id=<?= $conducteur->id ?>"><?= e($conducteur->nom) ?></a>
                    <?php if ($noteConducteur === null) { ?>
                        <small class="note-vide">Pas encore noté</small>
                    <?php } else { ?>
                        <small><span class="note"><?= formaterNote($noteConducteur) ?></span> - <?= $nombreAvisConducteur ?> avis</small>
                    <?php } ?>
                </span>
            </div>
        </div>
    </aside>
</div>
