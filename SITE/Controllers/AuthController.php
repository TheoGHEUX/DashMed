<?php

declare(strict_types=1);

namespace Controllers;

use Models\Repositories\UserRepository;
use Core\Csrf;
use Core\Mailer;
use Core\View;

/**
 * Authentification
 *
 * Gère le cycle complet d'authentification des praticiens.
 *
 * Inclut l'inscription (avec vérification d'email),
 * la connexion sécurisée (avec limitation de débit) et la gestion de session.
 *
 * @package Controllers
 */
final class AuthController
{
    private UserRepository $userRepo;

    /**
     * Liste des spécialités médicales valides.
     *
     * Note : Utilisée pour la validation côté serveur lors de l'inscription.
     *
     * @var array<int,string>
     */
    private const SPECIALITES_VALIDES = [
        'Addictologie', 'Algologie', 'Allergologie', 'Anesthésie-Réanimation',
        'Cancérologie', 'Cardio-vasculaire HTA', 'Chirurgie', 'Dermatologie',
        'Diabétologie-Endocrinologie', 'Génétique', 'Gériatrie', 'Gynécologie-Obstétrique',
        'Hématologie', 'Hépato-gastro-entérologie', 'Imagerie médicale', 'Immunologie',
        'Infectiologie', 'Médecine du sport', 'Médecine du travail', 'Médecine générale',
        'Médecine légale', 'Médecine physique et de réadaptation', 'Néphrologie',
        'Neurologie', 'Nutrition', 'Ophtalmologie', 'ORL', 'Pédiatrie',
        'Pneumologie', 'Psychiatrie', 'Radiologie', 'Rhumatologie', 'Sexologie',
        'Toxicologie', 'Urologie',
    ];

    public function __construct()
    {
        $this->userRepo = new UserRepository();
    }

    /**
     * Affiche le formulaire de connexion.
     *
     * @return void
     */
    public function showLogin(): void
    {
        $errors = [];
        $success = (isset($_GET['reset']) && $_GET['reset'] === '1') ? 'Mot de passe réinitialisé.' : '';
        $old = ['email' => ''];
        \Core\View::render('auth/login', compact('errors', 'success', 'old'));
    }

    /**
     * Traite la soumission du formulaire de connexion.
     *
     * Sécurité appliquée :
     * - Protection brute force : 5 tentatives max par session sur 15 minutes
     * - Jeton CSRF
     * - Existence de l'utilisateur
     * - Vérification du mot de passe
     * - Email vérifié
     *
     * En cas de succès, régénère l'ID de session et redirige vers /dashboard.
     *
     * @return void
     */
    public function login(): void
    {
        // Démarrer la session pour la limitation de débit
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Protection contre le brute force : 5 tentatives max par session sur 15 minutes
        $maxAttempts = 5;
        $windowSeconds = 900; // 15 minutes
        $now = time();
        $loginAttempts = $_SESSION['login_attempts'] ?? [];
        $loginAttempts = array_filter($loginAttempts, function ($ts) use ($now, $windowSeconds) {
            return ($now - $ts) <= $windowSeconds;
        });

        if (count($loginAttempts) >= $maxAttempts) {
            $errors = ['Trop de tentatives de connexion. Veuillez réessayer dans 15 minutes.'];
            $old = ['email' => trim($_POST['email'] ?? '')];
            \Core\View::render('auth/login', compact('errors', 'old'));
            return;
        }

        $errors = [];
        $old = ['email' => trim($_POST['email'] ?? '')];
        $password = $_POST['password'] ?? '';
        $csrf = $_POST['csrf_token'] ?? '';

        if (!Csrf::validate($csrf)) {
            $errors[] = 'Session expirée.';
        }

        if (!$errors) {
            // Appel REPOSITORY
            $user = $this->userRepo->findByEmail($old['email']);

            if (!$user || !password_verify($password, $user->getPasswordHash())) {
                $errors[] = 'Identifiants incorrects.';
                // Enregistrer la tentative échouée
                $loginAttempts[] = $now;
                $_SESSION['login_attempts'] = $loginAttempts;
            } elseif (!$user->isEmailVerified()) {
                $errors[] = 'Adresse email non vérifiée. Vérifiez vos spams.';
                // Enregistrer la tentative échouée
                $loginAttempts[] = $now;
                $_SESSION['login_attempts'] = $loginAttempts;
            } else {
                // Succès : Réinitialiser les tentatives et mettre en session
                $_SESSION['login_attempts'] = [];
                session_regenerate_id(true);

                $_SESSION['user'] = $user->toSessionArray();

                header('Location: /dashboard');
                exit;
            }
        } else {
            // Enregistrer la tentative avec erreur CSRF
            $loginAttempts[] = $now;
            $_SESSION['login_attempts'] = $loginAttempts;
        }

        \Core\View::render('auth/login', compact('errors', 'old'));
    }

    /**
     * Affiche le formulaire d'inscription.
     *
     * @return void
     */
    public function showRegister(): void
    {
        $errors = [];
        $success = '';
        $old = ['name' => '', 'last_name' => '', 'email' => '', 'sexe' => '', 'specialite' => ''];
        $specialites = self::SPECIALITES_VALIDES;
        \Core\View::render('auth/register', compact('errors', 'success', 'old', 'specialites'));
    }

    /**
     * Traite l'inscription d'un nouveau praticien.
     *
     * Sécurité appliquée :
     * - Protection brute force : 3 inscriptions max par session sur 1 heure
     * - Jeton CSRF : protection contre l'usurpation de requête
     * - Nom et prénom
     * - Email (format et unicité)
     * - Mot de passe (12+ car., maj, min, chiffre, spécial)
     *
     * Processus en cas de succès :
     * 1. Création du compte avec hachage du mot de passe
     * 2. Génération d'un jeton de vérification d'email (64 hex chars, valide 24h)
     * 3. Envoi de l'email de vérification
     * 4. Affichage du message de succès
     *
     * @return void
     */
    public function register(): void
    {
        // Démarrer la session pour la limitation de débit
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Protection contre le brute force : 3 inscriptions max par session sur 1 heure
        $maxAttempts = 3;
        $windowSeconds = 3600; // 1 heure
        $now = time();
        $registerAttempts = $_SESSION['register_attempts'] ?? [];
        $registerAttempts = array_filter($registerAttempts, function ($ts) use ($now, $windowSeconds) {
            return ($now - $ts) <= $windowSeconds;
        });

        if (count($registerAttempts) >= $maxAttempts) {
            $errors = ['Trop de tentatives d\'inscription. Veuillez réessayer dans 1 heure.'];
            $old = ['name' => '', 'last_name' => '', 'email' => '', 'sexe' => '', 'specialite' => ''];
            $success = '';
            $specialites = self::SPECIALITES_VALIDES;
            \Core\View::render('auth/register', compact('errors', 'success', 'old', 'specialites'));
            return;
        }

        $errors = [];
        $success = '';

        $old = [
            'name' => trim($_POST['name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'sexe' => trim($_POST['sexe'] ?? ''),
            'specialite' => trim($_POST['specialite'] ?? ''),
        ];
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';
        $csrf = $_POST['csrf_token'] ?? '';


        if (!Csrf::validate($csrf)) {
            $errors[] = 'Session expirée.';
        }
        if (empty($old['name']) || empty($old['last_name'])) {
            $errors[] = 'Nom et prénom obligatoires.';
        }
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email invalide.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }
        // Complexité mot de passe (12 chars + Maj + Min + Chiffre + Spécial)
        if (strlen($password) < 12
            || !preg_match('/[A-Z]/', $password)
            || !preg_match('/[a-z]/', $password)
            || !preg_match('/\d/', $password)
            || !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit faire 12 caractères min. avec Maj, Min, Chiffre et Caractère spécial.';
        }

        if (!$errors && $this->userRepo->emailExists($old['email'])) {
            $errors[] = 'Cet email est déjà utilisé.';
        }



        if (!$errors) {
            $created = $this->userRepo->create([
                'prenom' => $old['name'],
                'nom' => $old['last_name'],
                'email' => $old['email'],
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'sexe' => $old['sexe'],
                'specialite' => $old['specialite']
            ]);

            if ($created) {
                // Gestion du token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

                $this->userRepo->setVerificationToken($old['email'], $token, $expires);

                // Envoi email
                $mailSent = Mailer::sendEmailVerification($old['email'], $old['name'], $token);

                if ($mailSent) {
                    $success = "Compte créé ! Un lien de vérification a été envoyé à " . htmlspecialchars($old['email']);
                } else {
                    $success = "Compte créé, mais l'envoi du mail a échoué. Contactez le support.";
                }

                // Reset form et réinitialiser les tentatives après succès
                $old = ['name' => '', 'last_name' => '', 'email' => '', 'sexe' => '', 'specialite' => ''];
                $_SESSION['register_attempts'] = [];
            } else {
                $errors[] = "Erreur technique lors de la création du compte.";
                // Enregistrer la tentative échouée
                $registerAttempts[] = $now;
                $_SESSION['register_attempts'] = $registerAttempts;
            }
        } else {
            // Enregistrer la tentative avec erreur de validation
            $registerAttempts[] = $now;
            $_SESSION['register_attempts'] = $registerAttempts;
        }

        $specialites = self::SPECIALITES_VALIDES;
        \Core\View::render('auth/register', compact('errors', 'success', 'old', 'specialites'));
    }

    /**
     * Déconnecte l'utilisateur.
     *
     * Détruit la session et supprime le cookie de session,
     * puis redirige vers la page de connexion.
     *
     * @return void
     */
    public function logout(): void
    {
        // Session déjà démarrée dans index.php

        $csrf = $_POST['csrf_token'] ?? '';
        if (!Csrf::validate($csrf)) {
            header('Location: /dashboard');
            exit;
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();

        header('Location: /login');
        exit;
    }
}
