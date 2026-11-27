<?php
/**
 * ------------------------------------------------------------------
 *  CoVoie - AvisController                             MODULE C
 * ------------------------------------------------------------------
 *  Notation des participants après un trajet terminé.
 * ------------------------------------------------------------------
 */
class AvisController
{
    /**
     * Formulaire de notation d'un trajet terminé.
     * URL : index.php?page=noter&id=<trajet>
     */
    public function formulaire(): void
    {
        $idConnecte = proteger();

        $trajet = Trajet::trouver(lireId());
        if ($trajet === null) {
            afficherErreur(404, 'Ce trajet n\'existe pas.');
        }

        if ($trajet->statut !== 'Termine') {
            ajouterMessage('Vous pourrez noter les participants une fois le trajet clôturé.', 'erreur');
            redirection('trajet&id=' . $trajet->id);
        }

        // CONTRÔLE D'ACCÈS : seuls les participants du trajet peuvent noter
        $participants = Avis::participantsANoter($trajet, $idConnecte);
        if ($participants === null) {
            afficherErreur(403, 'Vous n\'avez pas participé à ce trajet.');
        }

        afficherVue('avis/formulaire', [
            'titre' => 'Noter le trajet',
            'trajet' => $trajet,
            'participants' => $participants
        ]);
    }

    /**
     * Enregistre un avis.  (POST)
     */
    public function enregistrer(): void
    {
        $idConnecte = proteger();
        verifierFormulaire();

        $trajet = Trajet::trouver(lireId());
        if ($trajet === null) {
            afficherErreur(404, 'Ce trajet n\'existe pas.');
        }

        $erreur = Avis::enregistrer($trajet, $idConnecte, (int) champ('id_cible'), (int) champ('note'), champ('commentaire'));

        if ($erreur !== '') {
            ajouterMessage($erreur, 'erreur');
        } else {
            ajouterMessage('Merci, votre avis est enregistré.');
        }
        redirection('noter&id=' . $trajet->id);
    }

    /**
     * Page "Mes avis" : trajets à noter, avis reçus et avis donnés.
     */
    public function mesAvis(): void
    {
        $idConnecte = proteger();

        afficherVue('avis/mes-avis', [
            'titre' => 'Mes avis',
            'aNoter' => Avis::trajetsANoter($idConnecte),
            'note' => Avis::noteMoyenne($idConnecte),
            'recus' => Avis::listerRecus($idConnecte),
            'ecrits' => Avis::listerEcrits($idConnecte)
        ]);
    }
}
