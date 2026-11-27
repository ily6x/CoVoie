<?php
/**
 * ------------------------------------------------------------------
 *  CoVoie - AuthController
 * ------------------------------------------------------------------
 *  Inscription, connexion et déconnexion.
 * ------------------------------------------------------------------
 */
class AuthController
{
    /**
     * Affiche le formulaire de connexion.
     */
    public function formulaireConnexion(): void
    {
        afficherVue('auth/connexion', ['titre' => 'Connexion', 'email' => '']);
    }

    /**
     * Traite le formulaire de connexion.  (POST)
     */
    public function connecter(): void
    {
        verifierFormulaire();

        $email = champ('email');
        // Le mot de passe n'est pas "trimé" : les espaces en font partie (comme à l'inscription)
        $motDePasse = '';
        if (isset($_POST['mot_de_passe'])) {
            $motDePasse = (string) $_POST['mot_de_passe'];
        }
        $utilisateur = Utilisateur::verifierConnexion($email, $motDePasse);

        if ($utilisateur === null) {
            // Pause d'une seconde après un échec : un programme ne peut plus essayer
            // des milliers de mots de passe à la suite (attaque "par force brute")
            sleep(1);
            // Message volontairement vague : on ne dit pas si c'est l'email ou le mot de passe
            ajouterMessage('Email ou mot de passe incorrect.', 'erreur');
            afficherVue('auth/connexion', ['titre' => 'Connexion', 'email' => $email]);
            return;
        }

        // Nouvel identifiant de session à la connexion (contre le vol de session)
        session_regenerate_id(true);

        // On garde en session uniquement ce dont le site a besoin
        $_SESSION['utilisateur'] = [
            'id' => $utilisateur->id,
            'nom' => $utilisateur->nom,
            'role' => $utilisateur->role
        ];

        ajouterMessage('Bonjour ' . $utilisateur->nom . ' !');
        redirection('accueil');
    }

    /**
     * Affiche le formulaire d'inscription.
     */
    public function formulaireInscription(): void
    {
        afficherVue('auth/inscription', ['titre' => 'Inscription', 'nom' => '', 'email' => '', 'erreurs' => []]);
    }

    /**
     * Traite le formulaire d'inscription.  (POST)
     */
    public function inscrire(): void
    {
        verifierFormulaire();

        $nom = champ('nom');
        $email = champ('email');

        // Le mot de passe n'est pas "trimé" : les espaces en font partie
        $motDePasse = '';
        $confirmation = '';
        if (isset($_POST['mot_de_passe'])) {
            $motDePasse = (string) $_POST['mot_de_passe'];
        }
        if (isset($_POST['confirmation'])) {
            $confirmation = (string) $_POST['confirmation'];
        }

        $erreurs = Utilisateur::inscrire($nom, $email, $motDePasse, $confirmation);

        if (count($erreurs) > 0) {
            // On réaffiche le formulaire avec les erreurs et les champs déjà remplis
            afficherVue('auth/inscription', ['titre' => 'Inscription', 'nom' => $nom, 'email' => $email, 'erreurs' => $erreurs]);
            return;
        }

        ajouterMessage('Compte créé ! Vous pouvez maintenant vous connecter.');
        redirection('connexion');
    }

    /**
     * Déconnexion : on vide la session.  (POST)
     */
    public function deconnecter(): void
    {
        verifierFormulaire();

        $_SESSION = [];
        session_regenerate_id(true);

        ajouterMessage('Vous êtes déconnecté. À bientôt !');
        redirection('accueil');
    }
}
