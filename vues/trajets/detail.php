<?php
/**
 * Vue : détail d'un trajet
 * Variables : $trajet (Trajet), $conducteur (Utilisateur), $noteConducteur, $nombreAvisConducteur,
 *             $estConducteur (bool), $demandes (liste, seulement pour le conducteur),
 *             $maDemande (statut de ma demande, ou null)
 */

// Étapes du cycle de vie : quelles étapes sont atteintes ?
$etapeComplet = ($trajet->statut === 'Complet' || $trajet->statut === 'Termine');
$etapeTermine = ($trajet->statut === 'Termine');
?>
<p class="retour"><a href="index.php?page=trajets">← Retour aux trajets</a></p>

<div class="deux-colonnes">
    <div>
        <!-- ===== Le billet ===== -->
        <article class="billet billet-detail">
            <div class="billet-haut">
                <div class="ligne-haut">
                    <span class="statut <?= classeStatut($trajet->statut) ?>"><?= libelleStatut($trajet->statut) ?></span>
                    <span class="discret"><?= ucfirst(formaterJour($trajet->date_heure)) ?> <?= date('Y', strtotime($trajet->date_heure)) ?></span>
                </div>

                <div class="horaire">
                    <div class="lieu">
                        <b><?= formaterHeure($trajet->date_heure) ?></b>
                        <span><?= e($trajet->origine) ?></span>
                    </div>
                    <div class="trait"></div>
                    <div class="lieu arrivee">
                        <b>Arrivée</b>
                        <span><?= e($trajet->destination) ?></span>
                    </div>
                </div>

                <div class="infos">
                    <div><small>Date</small><b><?= formaterDate($trajet->date_heure) ?></b></div>
                    <div><small>Places libres</small><b><?= $trajet->places_restantes ?> sur <?= $trajet->places_totales ?></b></div>
                    <div><small>Prix par place</small><b><?= formaterPrix($trajet->prix) ?></b></div>
                </div>
            </div>

            <div class="decoupe"></div>

            <div class="billet-bas">
                <!-- Cycle de vie du trajet -->
                <div class="etapes">
                    <div class="etape faite"><i></i>Ouvert</div>
                    <div class="ligne<?= $etapeComplet ? ' faite' : '' ?>"></div>
                    <div class="etape<?= $etapeComplet ? ' faite' : '' ?>"><i></i>Complet</div>
                    <div class="ligne<?= $etapeTermine ? ' faite' : '' ?>"></div>
                    <div class="etape<?= $etapeTermine ? ' faite' : '' ?>"><i></i>Terminé</div>
                </div>
                <span class="tampon"><?= formaterPrix($trajet->prix) ?></span>
            </div>
        </article>

        <?php
        // ===== Demandes de réservation : visibles UNIQUEMENT par le conducteur =====
        if ($estConducteur) { ?>
            <div class="carte carte-demandes">
                <h2>Demandes de réservation <span class="discret"><?= count($demandes) ?> au total</span></h2>
                <p class="discret">Accepter une demande retire une place du trajet. À zéro place, le trajet passe « Complet ».</p>

                <?php if (count($demandes) === 0) { ?>
                    <div class="vide"><b>Aucune demande</b>Les demandes des passagers apparaîtront ici.</div>
                <?php } ?>

                <ul class="liste">
                    <?php foreach ($demandes as $demande) { ?>
                        <li>
                            <span class="avatar"><?= e(initiales($demande['nom_passager'])) ?></span>
                            <div class="infos-personne">
                                <b><a href="index.php?page=profil&amp;id=<?= $demande['id_passager'] ?>"><?= e($demande['nom_passager']) ?></a></b>
                                <small>
                                    <?php if ($demande['note_passager'] === null) { ?>
                                        <span class="note-vide">Pas encore noté</span>
                                    <?php } else { ?>
                                        <span class="note"><?= formaterNote((float) $demande['note_passager']) ?></span>
                                    <?php } ?>
                                    · demande du <?= formaterDate($demande['date_reservation']) ?>
                                </small>
                            </div>

                            <?php if ($demande['statut'] === 'En attente' && $trajet->estModifiable()) { ?>
                                <div class="actions">
                                    <?php if ($trajet->places_restantes > 0) { ?>
                                        <form class="en-ligne" method="post" action="index.php?page=accepter&amp;id=<?= $trajet->id ?>&amp;reservation=<?= $demande['id'] ?>">
                                            <?= champCsrf() ?>
                                            <button class="bouton bouton-petit" type="submit">Accepter</button>
                                        </form>
                                    <?php } ?>
                                    <form class="en-ligne" method="post" action="index.php?page=refuser&amp;id=<?= $trajet->id ?>&amp;reservation=<?= $demande['id'] ?>">
                                        <?= champCsrf() ?>
                                        <button class="bouton bouton-danger bouton-petit" type="submit">Refuser</button>
                                    </form>
                                </div>
                            <?php } else { ?>
                                <span class="statut <?= classeStatut($demande['statut']) ?>"><?= libelleStatut($demande['statut']) ?></span>
                            <?php } ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>
    </div>

    <aside>
        <!-- ===== Le conducteur ===== -->
        <div class="carte">
            <h2>Conducteur</h2>
            <div class="conducteur">
                <span class="avatar"><?= e(initiales($conducteur->nom)) ?></span>
                <div>
                    <a class="nom" href="index.php?page=profil&amp;id=<?= $conducteur->id ?>"><?= e($conducteur->nom) ?></a>
                    <?php if ($noteConducteur === null) { ?>
                        <small class="note-vide">Pas encore noté</small>
                    <?php } else { ?>
                        <small><span class="note"><?= formaterNote($noteConducteur) ?></span> · <?= $nombreAvisConducteur ?> avis</small>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- ===== Action possible : dépend de QUI regarde la page ===== -->
        <div class="carte">
            <?php if (!estConnecte()) { ?>
                <h2>Réserver</h2>
                <p class="discret">Connectez-vous pour demander une place.</p>
                <a class="bouton bouton-large" href="index.php?page=connexion">Se connecter</a>

            <?php } elseif ($estConducteur) { ?>
                <h2>Gérer mon trajet</h2>
                <div class="actions">
                    <?php if ($trajet->estModifiable()) { ?>
                        <a class="bouton bouton-secondaire bouton-petit" href="index.php?page=modifier&amp;id=<?= $trajet->id ?>">Modifier</a>
                    <?php } ?>

                    <?php if ($trajet->statut === 'Termine') { ?>
                        <a class="bouton bouton-petit" href="index.php?page=noter&amp;id=<?= $trajet->id ?>">Noter mes passagers</a>
                    <?php } ?>

                    <!-- data-confirmation : le JavaScript demande confirmation avant l'envoi -->
                    <form class="en-ligne" method="post" action="index.php?page=supprimer-trajet&amp;id=<?= $trajet->id ?>"
                          data-confirmation="Supprimer définitivement ce trajet et toutes ses réservations ?">
                        <?= champCsrf() ?>
                        <button class="bouton bouton-danger bouton-petit" type="submit">Supprimer</button>
                    </form>
                </div>

            <?php } elseif ($maDemande !== null) { ?>
                <h2>Ma demande</h2>
                <p><span class="statut <?= classeStatut($maDemande) ?>"><?= libelleStatut($maDemande) ?></span></p>
                <?php if ($maDemande === 'Confirmee' && $trajet->statut === 'Termine') { ?>
                    <a class="bouton bouton-large" href="index.php?page=noter&amp;id=<?= $trajet->id ?>">Noter le conducteur</a>
                <?php } else { ?>
                    <a class="bouton bouton-secondaire bouton-large" href="index.php?page=mes-reservations">Voir mes réservations</a>
                <?php } ?>

            <?php } elseif ($trajet->statut !== 'Ouvert' || $trajet->places_restantes < 1 || $trajet->estParti()) { ?>
                <h2>Réservation impossible</h2>
                <p class="discret">Ce trajet est complet, déjà parti ou terminé.</p>

            <?php } else { ?>
                <h2>Réserver une place</h2>
                <p class="discret">Le conducteur recevra votre demande et pourra l'accepter.</p>
                <form method="post" action="index.php?page=reserver&amp;id=<?= $trajet->id ?>">
                    <?= champCsrf() ?>
                    <button class="bouton bouton-large" type="submit">Demander une place · <?= formaterPrix($trajet->prix) ?></button>
                </form>
            <?php } ?>
        </div>
    </aside>
</div>
