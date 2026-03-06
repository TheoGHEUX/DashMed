<?php

namespace Controllers;

use Core\View;
use Core\Csrf;
use Core\Mailer;
use Models\Repositories\UserRepository;

/**
 * Contrôleur d'Authentification
 *
 * Gère exclusivement :
 * - La connexion (Login)
 * - La déconnexion (Logout)
 * - L'inscription (Register)
 */
final class AuthController
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }
    public function showLogin(): void
    {
        // Si déjà connecté, on redirige vers le dashboard
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!empty($_SESSION['user'])) {
            header('Location: /dashboard');
            exit;
        }

        View::render('auth/login');
    }

    public function login(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $errors = [];
        $success = '';
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $csrf = $_POST['csrf_token'] ?? '';

        // 1. Vérification CSRF
        if (!Csrf::validate($csrf)) {
            $errors[] = 'Session expirée, veuillez réessayer.';
        }

        // 2. Vérification des identifiants
        if (empty($errors)) {
            $user = $this->users->findByEmail($email);

            if ($user && password_verify($password, $user->getPasswordHash())) {

                // 3. Vérification Email Validé
                if (!$user->isEmailVerified()) {
                    $errors[] = 'Votre compte n\'est pas encore activé. Veuillez vérifier vos emails.';
                } else {
                    // --- SUCCÈS ---
                    // Régénération de l'ID de session (Sécurité anti-fixation)
                    session_regenerate_id(true);

                    // Stockage en session (On évite de stocker le hash du mot de passe)
                    $_SESSION['user'] = [
                        'id' => $user->getId(),
                        'prenom' => $user->getPrenom(),
                        'nom' => $user->getNom(),
                        'email' => $user->getEmail(),
                        'role' => $user->getRole(),
                        'sexe' => $user->getSexe(), // Utile pour le profil
                        'specialite' => $user->getSpecialite() // Utile pour le profil
                    ];

                    header('Location: /dashboard');
                    exit;
                }
            } else {
                $errors[] = 'Identifiants incorrects.';
            }
        }

        $old = ['email' => $email];
        View::render('auth/login', compact('errors', 'success', 'old'));
    }


    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        // Détruit toutes les données de session
        $_SESSION = [];
        session_destroy();

        // Redirection vers la page de login
        header('Location: /login');
        exit;
    }

    public function showRegister(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!empty($_SESSION['user'])) {
            header('Location: /dashboard');
            exit;
        }

        View::render('auth/register');
    }

    public function register(): void
    {
        $errors = [];
        $success = '';

        // Récupération des données
        $csrf = $_POST['csrf_token'] ?? '';
        $prenom = trim($_POST['name'] ?? '');
        $nom = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';
        $sexe = $_POST['sexe'] ?? '';
        $specialite = $_POST['specialite'] ?? '';

        // 1. Validations de base
        if (!Csrf::validate($csrf)) $errors[] = 'Session expirée.';
        if (empty($prenom) || empty($nom) || empty($email)) $errors[] = 'Tous les champs sont obligatoires.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
        if ($password !== $confirm) $errors[] = 'Les mots de passe ne correspondent pas.';

        if (strlen($password) < 8) $errors[] = 'Le mot de passe est trop court (min 8 caractères).';

        // 2. Vérification existence email
        if (empty($errors)) {
            if ($this->users->findByEmail($email)) {
                $errors[] = 'Cet email est déjà utilisé.';
            }
        }

        // 3. Création du compte
        if (empty($errors)) {
            // Hashage
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Token de validation email
            $token = bin2hex(random_bytes(32));
            $expires = (new \DateTime('+24 hours'))->format('Y-m-d H:i:s');

            // Sauvegarde en BDD
            $created = $this->users->create([
                'prenom' => $prenom,
                'nom' => $nom,
                'email' => $email,
                'password' => $hash,
                'sexe' => $sexe,
                'specialite' => $specialite,
                'verification_token' => $token,
                'verification_expires' => $expires,
                'email_verified' => 0
            ]);

            if ($created) {
                // Envoi de l'email
                $mailSent = Mailer::sendEmailVerification($email, $prenom, $token);

                if ($mailSent) {
                    $success = "Compte créé avec succès ! Veuillez vérifier votre boîte mail pour activer votre compte.";
                    // On vide le formulaire
                    $prenom = $nom = $email = $sexe = $specialite = '';
                } else {
                    $errors[] = "Compte créé, mais impossible d'envoyer l'email de validation. Contactez le support.";
                }
            } else {
                $errors[] = "Erreur technique lors de la création du compte.";
            }
        }

        // Préparation des données pour ré-afficher le formulaire
        $old = [
            'name' => $prenom,
            'last_name' => $nom,
            'email' => $email,
            'sexe' => $sexe,
            'specialite' => $specialite
        ];

        View::render('auth/register', compact('errors', 'success', 'old'));
    }
}