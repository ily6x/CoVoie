<?php
/**
 * ------------------------------------------------------------------
 *  CoVoie - TrajetController                           MODULE A
 * ------------------------------------------------------------------
 *  Détail, publication, modification et suppression des
 *  trajets, puis statistiques du conducteur.
 *  (La clôture est automatique : voir Trajet::cloturerTrajetsPasses().)
 * ------------------------------------------------------------------
 */
class TrajetController
{
    /**
     * Charge le trajet dont l'id est dans l'URL (erreur 404 s'il n'existe pas).
     * @return Trajet
     */
    private function chargerTrajet(): Trajet
    {
        $trajet = Trajet::trouver(lireId());
        if ($trajet === null) {
            afficherErreur(404, 'Ce trajet n\'existe pas.');
        }
        return $trajet;
    }

    /**
     * Charge le trajet de l'URL ET vérifie que la personne connectée en est le conducteur.
     * CONTRÔLE D'ACCÈS : personne d'autre ne peut modifier ou supprimer un trajet.
     * @return Trajet
     */
    private function chargerMonTrajet(): Trajet
    {
        $idConnecte = proteger();
        $trajet = $this->chargerTrajet();

        if ($trajet->id_conducteur !== $idConnecte) {
            afficherErreur(403, 'Seul le conducteur peut gérer ce trajet.');
        }
        return $trajet;
    }

    /**
     * Lit les champs du formulaire de trajet dans un tableau.
     * @return array
     */
    private function lireFormulaire(): array
    {
        return [
            'origine' => champ('origine'),
            'destination' => champ('destination'),
            'date_heure' => champ('date_heure'),
            'places' => (int) champ('places'),
            // On accepte la virgule française : "3,50" -> 3.50
            'prix' => (float) str_replace(',', '.', champ('prix'))
        ];
    }


    /* ==============================================================
       PAGES PUBLIQUES
       ============================================================== */

    /**
     * Détail d'un trajet. Le contenu change selon qui regarde :
     *  - visiteur          : invitation à se connecter
     *  - passager          : bouton "Réserver" ou état de sa demande
     *  - conducteur        : liste des demandes + boutons de gestion
     */
    public function detail(): void
    {
        $trajet = $this->chargerTrajet();
        $estConducteur = ($trajet->id_conducteur === idConnecte());

        // Les demandes de réservation ne sont visibles que par le conducteur
        $demandes = [];
        if ($estConducteur) {
            $demandes = Reservation::listerParTrajet($trajet->id);
        }

        afficherVue('trajets/detail', [
            'titre' => $trajet->origine . ' → ' . $trajet->destination,
            'trajet' => $trajet,
            'conducteur' => Utilisateur::trouver($trajet->id_conducteur),
            'noteConducteur' => Avis::noteMoyenne($trajet->id_conducteur),
            'nombreAvisConducteur' => Avis::nombreAvis($trajet->id_conducteur),
            'estConducteur' => $estConducteur,
            'demandes' => $demandes,
            'maDemande' => Reservation::statutDemande($trajet->id, idConnecte())
        ]);
    }


    /* ==============================================================
       PUBLIER / MODIFIER / SUPPRIMER / CLÔTURER
       ============================================================== */

    /**
     * Formulaire vide pour publier un trajet.
     */
    public function formulairePublier(): void
    {
        proteger();

        afficherVue('trajets/formulaire', [
            'titre' => 'Publier un trajet',
            'trajet' => null,
            'valeurs' => ['origine' => '', 'destination' => '', 'date_heure' => '', 'places' => 3, 'prix' => 2],
            'erreurs' => []
        ]);
    }

    /**
     * Enregistre le nouveau trajet.  (POST)
     */
    public function publier(): void
    {
        $idConnecte = proteger();
        verifierFormulaire();

        $v = $this->lireFormulaire();
        $erreurs = Trajet::valider($v['origine'], $v['destination'], $v['date_heure'], $v['places'], $v['prix']);

        if (count($erreurs) > 0) {
            afficherVue('trajets/formulaire', ['titre' => 'Publier un trajet', 'trajet' => null, 'valeurs' => $v, 'erreurs' => $erreurs]);
            return;
        }

        $id = Trajet::creer($idConnecte, $v['origine'], $v['destination'], $v['date_heure'], $v['places'], $v['prix']);

        ajouterMessage('Votre trajet est publié !');
        redirection('trajet&id=' . $id);
    }

    /**
     * Formulaire de modification, pré-rempli avec le trajet.
     */
    public function formulaireModifier(): void
    {
        $trajet = $this->chargerMonTrajet();

        if (!$trajet->estModifiable()) {
            ajouterMessage('Ce trajet est parti ou terminé : il ne peut plus être modifié.', 'erreur');
            redirection('trajet&id=' . $trajet->id);
        }

        afficherVue('trajets/formulaire', [
            'titre' => 'Modifier le trajet',
            'trajet' => $trajet,
            'valeurs' => [
                'origine' => $trajet->origine,
                'destination' => $trajet->destination,
                // Format attendu par le champ datetime-local : 2026-12-03T08:15
                'date_heure' => date('Y-m-d\TH:i', strtotime($trajet->date_heure)),
                'places' => $trajet->places_totales,
                'prix' => $trajet->prix
            ],
            'erreurs' => []
        ]);
    }

    /**
     * Enregistre les modifications.  (POST)
     */
    public function modifier(): void
    {
        $trajet = $this->chargerMonTrajet();
        verifierFormulaire();

        $v = $this->lireFormulaire();
        $erreurs = Trajet::valider($v['origine'], $v['destination'], $v['date_heure'], $v['places'], $v['prix']);

        if (count($erreurs) === 0) {
            $erreur = $trajet->modifier($v['origine'], $v['destination'], $v['date_heure'], $v['places'], $v['prix']);
            if ($erreur !== '') {
                // Erreur qui ne concerne pas un seul champ : affichée en haut du formulaire
                $erreurs['general'] = $erreur;
            }
        }

        if (count($erreurs) > 0) {
            afficherVue('trajets/formulaire', ['titre' => 'Modifier le trajet', 'trajet' => $trajet, 'valeurs' => $v, 'erreurs' => $erreurs]);
            return;
        }

        ajouterMessage('Trajet modifié.');
        redirection('trajet&id=' . $trajet->id);
    }

    /**
     * Supprime le trajet.  (POST)
     */
    public function supprimer(): void
    {
        $trajet = $this->chargerMonTrajet();
        verifierFormulaire();

        $trajet->supprimer();

        ajouterMessage('Trajet supprimé.');
        redirection('mes-trajets');
    }


    /* ==============================================================
       ESPACE CONDUCTEUR
       ============================================================== */

    /**
     * Page "Mes trajets" : demandes à traiter + tous mes trajets publiés.
     */
    public function mesTrajets(): void
    {
        $idConnecte = proteger();

        afficherVue('trajets/mes-trajets', [
            'titre' => 'Mes trajets',
            'demandes' => Reservation::listerEnAttentePourConducteur($idConnecte),
            'trajets' => Trajet::listerParConducteur($idConnecte)
        ]);
    }

    /**
     * Page "Mes statistiques" du conducteur.
     */
    public function statistiques(): void
    {
        $idConnecte = proteger();

        afficherVue('trajets/statistiques', [
            'titre' => 'Mes statistiques',
            'stats' => Trajet::statistiques($idConnecte),
            'maNote' => Avis::noteMoyenne($idConnecte),
            'nombreAvis' => Avis::nombreAvis($idConnecte),
            'passagerFrequent' => Trajet::passagerLePlusFrequent($idConnecte)
        ]);
    }
}
