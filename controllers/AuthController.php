<?php

class AuthController
{

    public function loginForm(): void
    {
        render('auth/login', ['titre' => 'Connexion']);
    }

    public function registerForm(): void
    {
        render('auth/register', ['titre' => 'Inscription']);
    }

    public function login(): void
    {
        $utilisateur = Utilisateur::findByEmail(mb_strtolower(inputText($_POST, 'email')));
        $password = inputString($_POST, 'mot_de_passe');
        if (!$utilisateur || !password_verify($password, $utilisateur->password)) {
            throw new DomainException('Email ou mot de passe incorrect.');
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = $utilisateur->id;
        unset($_SESSION['csrf']);
        flash('Connexion réussie.');
        redirect('accueil');
    }

    public function register(): void
    {
        $id = Utilisateur::register(inputText($_POST, 'nom'), inputText($_POST, 'email'), inputString($_POST, 'mot_de_passe'), inputString($_POST, 'confirmation'));
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
        unset($_SESSION['csrf']);
        flash('Compte créé.');
        redirect('accueil');
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
        flash('Vous êtes déconnecté.');
        redirect('accueil');
    }
}
