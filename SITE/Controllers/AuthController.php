<?php

namespace Controllers;

use Models\User;
use Core\Csrf;
use Core\Mailer;

/**
 * Contrôleur : Authentification & inscription
 *
 * Gère le cycle d'authentification : inscription avec vérification d'email,
 * connexion avec limitation de débit et déconnexion sécurisée. Valide les spécialités
 * médicales et applique des règles de complexité des mots de passe.
 *
 * @package Controllers
 */
final class AuthController
{
    /**
     * Liste des spécialités médicales valides.
     *
     * Utilisée pour la validation côté serveur lors de l'inscription.
     *
     * @var array<int,string>
     */
    private const SPECIALITES_VALIDES = [
        'Addictologie',
        'Algologie',
        'Allergologie',
        'Anesthésie-Réanimation',
        'Cancérologie',
        'Cardio-vasculaire HTA',
        'Chirurgie',
        'Dermatologie',
        'Diabétologie-Endocrinologie',
        'Génétique',
        'Gériatrie',
        'Gynécologie-Obstétrique',
        'Hématologie',
        'Hépato-gastro-entérologie',
        'Imagerie médicale',
        'Immunologie',
        'Infectiologie',
        'Médecine du sport',
        'Médecine du travail',
        'Médecine générale',
        'Médecine légale',
        'Médecine physique et de réadaptation',
        'Néphrologie',
        'Neurologie',
        'Nutrition',
        'Ophtalmologie',
        'ORL',
        'Pédiatrie',
        'Pneumologie',
        'Psychiatrie',
        'Radiologie',
        'Rhumatologie',
        'Sexologie',
        'Toxicologie',
        'Urologie',
    ];

    public function showRegister(): void
    {
        $errors = [];
        $success = '';
        $old = [
            'name' => '',
            'last_name' => '',
            'email' => '',
            'sexe' => '',
            'specialite' => '',
        ];
        $specialites = self::SPECIALITES_VALIDES;
        require __DIR__ . '/../Views/auth/register.php';
    }

    /**
     * Traite l'inscription d'un nouvel utilisateur.
     *
     * Validations effectuées :
     * - Token CSRF
     * - Prénom, nom, email (format et unicité)
     * - Sexe (M ou F)
     * - Spécialité (liste fermée)
     * - Mot de passe (12+ caractères, maj/min/chiffre/spécial, confirmation)
     *
     * Processus en cas de succès :
     * 1. Création du compte (compte_actif = 1, email_verified = 0)
     * 2. Génération d'un token de vérification d'email (64 hex chars, valide 24h)
     * 3. Envoi de l'email de vérification
     * 4. Affichage du message de succès
     *
     * @return void
     */
    public function register(): void
    {
        $errors = [];
        $success = '';
        $old = [
            'name'       => trim((string)($_POST['name'] ?? '')),
            'last_name'  => trim((string)($_POST['last_name'] ?? '')),
            'email'      => trim((string)($_POST['email'] ?? '')),
            'sexe'       => trim((string)($_POST['sexe'] ?? '')),
            'specialite' => trim((string)($_POST['specialite'] ?? '')),
        ];
        $password         = (string)($_POST['password'] ?? '');
        $password_confirm = (string)($_POST['password_confirm'] ?? '');
        $csrf             = (string)($_POST['csrf_token'] ?? '');

        // Validation du jeton CSRF
        if (!Csrf::validate($csrf)) {
            $errors[] = 'Session expirée ou jeton CSRF invalide.';
        }

        // Validation du prénom
        if (empty($old['name'])) {
            $errors[] = 'Le prénom est obligatoire.';
        }

        // Validation du nom
        if (empty($old['last_name'])) {
            $errors[] = 'Le nom est obligatoire.';
        }

        // Validation de l'email
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse email invalide.';
        }

        // Validation du sexe
        if (empty($old['sexe']) || !in_array($old['sexe'], ['M', 'F'], true)) {
            $errors[] = 'Veuillez sélectionner un sexe valide.';
        }

        // Validation de la spécialité
        if (empty($old['specialite']) || !in_array($old['specialite'], self::SPECIALITES_VALIDES, true)) {
            $errors[] = 'Veuillez sélectionner une spécialité médicale valide.';
        }

        // Validation des mots de passe
        if ($password !== $password_confirm) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }

        // Validation de la complexité du mot de passe
        if (
            strlen($password) < 12
            || !preg_match('/[A-Z]/', $password)
            || !preg_match('/[a-z]/', $password)
            || !preg_match('/\d/', $password)
            || !preg_match('/[^A-Za-z0-9]/', $password)
        ) {
            $errors[] = 'Le mot de passe doit contenir au moins 12 caractères, avec majuscules, '
                . 'minuscules, chiffres et un caractère spécial.';
        }

        // Vérification de l'existence de l'email
        if (!$errors && User::emailExists($old['email'])) {
            $errors[] = 'Un compte existe déjà avec cette adresse email.';
        }

        // Création du compte si aucune erreur
        if (
            !$errors
            && User::create(
                $old['name'],
                $old['last_name'],
                $old['email'],
                password_hash($password, PASSWORD_DEFAULT),
                $old['sexe'],
                $old['specialite']
            )
        ) {
            // Génération du token de vérification d'email
            $verificationToken = User::generateEmailVerificationToken($old['email']);

            if ($verificationToken) {
                // Envoi de l'email de vérification
                $mailSent = Mailer::sendEmailVerification(
                    $old['email'],
                    $old['name'],
                    $verificationToken
                );

                $success = $mailSent
                    ? 'Compte créé avec succès ! Un email de vérification a été envoyé. '
                        . 'Veuillez vérifier votre boîte de réception pour activer votre compte.'
                    : 'Compte créé avec succès. (Attention: l\'email de vérification n\'a pas pu être envoyé. '
                        . 'Vous pouvez demander un nouveau lien.)';
            } else {
                $success = 'Compte créé mais erreur lors de la génération du lien de vérification. '
                    . 'Contactez le support.';
            }

            // Réinitialisation des champs après succès
            $old = [
                'name' => '',
                'last_name' => '',
                'email' => '',
                'sexe' => '',
                'specialite' => '',
            ];
        } elseif (!$errors) {
            $errors[] = 'L\'insertion en base de données a échoué.';
        }

        $specialites = self::SPECIALITES_VALIDES;
        require __DIR__ . '/../Views/auth/register.php';
    }

    public function showLogin(): void
    {
        $errors = [];
        $success = (isset($_GET['reset']) && $_GET['reset'] === '1')
            ? 'Votre mot de passe a été réinitialisé. Vous pouvez vous connecter.'
            : '';
        $old = ['email' => ''];
        require __DIR__ . '/../Views/auth/login.php';
    }

    /**
     * Traite la connexion d'un utilisateur.
     *
     * Validations effectuées :
     * - Token CSRF
     * - Format email
     * - Identifiants corrects (email + mot de passe)
     * - Email vérifié (email_verified = 1)
     * - Connexion à accès limité :  5 tentatives max par IP sur 5 minutes
     *
     * Processus en cas de succès :
     * 1. Régénération de l'ID de session (prévient session fixation)
     * 2. Stockage des données utilisateur en session
     * 3. Redirection vers /accueil
     *
     * En cas d'échec, la tentative est loggée pour le rate-limiting.
     *
     * @return void
     */
    public function login(): void
    {
        $errors = [];
        $success = '';
        $old = [
            'email' => trim((string)($_POST['email'] ?? '')),
        ];
        $password = (string)($_POST['password'] ?? '');
        $csrf     = (string)($_POST['csrf_token'] ?? '');

        // Limitation basique des tentatives de connexion par IP (5 essais / 5 minutes)
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = [];
        }
        $now = time();
        $windowSeconds = 300; // Durée avant de recommencer
        $maxAttempts   = 5; // Tentatives mauvaises autorisées
        // Purge des anciennes tentatives hors fenêtre
        $attempts = array_filter((array)($_SESSION['login_attempts'][$ip] ?? []), function ($ts) use ($now, $windowSeconds) {
            return ($now - (int)$ts) <= $windowSeconds;
        });
        $_SESSION['login_attempts'][$ip] = $attempts;
        if (count($attempts) >= $maxAttempts) {
            http_response_code(429);
            $errors[] = 'Trop de tentatives. Réessayez dans quelques minutes.';
        }

        // Validation du jeton CSRF
        if (!Csrf::validate($csrf)) {
            $errors[] = 'Session expirée ou jeton CSRF invalide.';
        }

        // Validation de l'email
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse email invalide.';
        }

        // Vérification des identifiants
        if (!$errors) {
            try {
                $user = User::findByEmail($old['email']);

                if (!$user || !password_verify($password, $user['password'])) {
                    $errors[] = 'Identifiants incorrects.';
                    // Enregistrer la tentative échouée
                    $_SESSION['login_attempts'][$ip][] = $now;
                } elseif (!$user['email_verified']) {
                    $errors[] = 'Adresse email non vérifiée. '
                        . 'Consultez votre boîte de réception et cliquez sur le lien de vérification.';
                    // Enregistrer la tentative échouée
                    $_SESSION['login_attempts'][$ip][] = $now;
                } else {
                    if (session_status() !== PHP_SESSION_ACTIVE) {
                        session_start();
                    }
                    session_regenerate_id(true);

                    $_SESSION['user'] = [
                        'id'         => $user['user_id'],
                        'email'      => $user['email'],
                        'name'       => $user['name'],
                        'last_name'  => $user['last_name'],
                        'sexe'       => $user['sexe'],
                        'specialite' => $user['specialite'],
                        'email_verified' => (bool) $user['email_verified'],
                    ];

                    header('Location: /accueil');
                    exit;
                }
            } catch (\Throwable $e) {
                error_log(sprintf(
                    '[LOGIN] Erreur: %s dans %s:%d',
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ));
                $errors[] = 'Erreur lors de la connexion. Veuillez réessayer.';
                // Enregistrer la tentative échouée
                $_SESSION['login_attempts'][$ip][] = $now;
            }
        }

        require __DIR__ . '/../Views/auth/login.php';
    }

    /**
     * Déconnecte l'utilisateur et détruit la session.
     *
     * Sécurité :
     * - Requiert un token CSRF valide (évite logout CSRF)
     * - Requiert POST (défini dans Router:: POST_ONLY)
     * - Détruit complètement la session (variables + cookie)
     * - Redirige vers /login
     *
     * @return void
     */
    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $csrf = (string)($_POST['csrf_token'] ?? '');
        if (!\Core\Csrf::validate($csrf)) {
            http_response_code(405);
            header('Location: /login');
            exit;
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        header('Location: /login');
        exit;
    }
}
