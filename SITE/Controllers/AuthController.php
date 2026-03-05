<?php

namespace Controllers;

use Core\Interfaces\UserRepositoryInterface;
use Domain\UseCases\Auth\RegisterUserUseCase;
use Core\Csrf;
use Core\View;

/**
 * Authentification
 *
 * Gère le cycle complet d'authentification des praticiens.
 *
 * Architecture :
 * - Le constructeur reçoit le Repository via l'injection de dépendances (DI).
 * - L'inscription délègue la logique métier à un Use Case dédié.
 * - La connexion utilise directement le Repository (pour l'instant).
 *
 * @package Controllers
 */
final class AuthController
{
    /**
     * @var UserRepositoryInterface Le contrat d'accès aux données utilisateurs
     */
    private UserRepositoryInterface $userRepo;

    /**
     * Liste des spécialités médicales valides.
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

    /**
     * Constructeur avec Injection de Dépendances.
     *
     * Le routeur (via le conteneur) va automatiquement fournir une instance
     * qui respecte l'interface UserRepositoryInterface.
     *
     * @param UserRepositoryInterface $userRepo
     */
    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /**
     * Affiche le formulaire de connexion.
     */
    public function showLogin(): void
    {
        $errors = [];
        $success = (isset($_GET['reset']) && $_GET['reset'] === '1') ? 'Mot de passe réinitialisé.' : '';
        $old = ['email' => ''];
        View::render('auth/login', compact('errors', 'success', 'old'));
    }

    /**
     * Traite la soumission du formulaire de connexion.
     */
    public function login(): void
    {
        $errors = [];
        $old = ['email' => trim($_POST['email'] ?? '')];
        $password = $_POST['password'] ?? '';
        $csrf = $_POST['csrf_token'] ?? '';

        if (!Csrf::validate($csrf)) {
            $errors[] = 'Session expirée.';
        }

        if (!$errors) {
            // Utilisation du Repository injecté
            $user = $this->userRepo->findByEmail($old['email']);

            if (!$user || !password_verify($password, $user->getPasswordHash())) {
                $errors[] = 'Identifiants incorrects.';
            } elseif (!$user->isEmailVerified()) {
                $errors[] = 'Adresse email non vérifiée. Vérifiez vos spams.';
            } else {
                // Succès : Mise en session
                if (session_status() !== PHP_SESSION_ACTIVE) session_start();
                session_regenerate_id(true);

                $_SESSION['user'] = $user->toSessionArray();

                header('Location: /dashboard');
                exit;
            }
        }

        View::render('auth/login', compact('errors', 'old'));
    }

    /**
     * Affiche le formulaire d'inscription.
     */
    public function showRegister(): void
    {
        $errors = [];
        $success = '';
        $old = ['name' => '', 'last_name' => '', 'email' => '', 'sexe' => '', 'specialite' => ''];
        $specialites = self::SPECIALITES_VALIDES;
        View::render('auth/register', compact('errors', 'success', 'old', 'specialites'));
    }

    /**
     * Traite l'inscription via le Use Case RegisterUser.
     */
    public function register(): void
    {
        $errors = [];
        $success = '';
        $csrf = $_POST['csrf_token'] ?? '';

        // Données pour ré-affichage en cas d'erreur
        $old = [
            'name' => trim($_POST['name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'sexe' => trim($_POST['sexe'] ?? ''),
            'specialite' => trim($_POST['specialite'] ?? ''),
        ];

        // 1. Vérification CSRF (Responsabilité HTTP du contrôleur)
        if (!Csrf::validate($csrf)) {
            $errors[] = 'Session expirée.';
        }

        // 2. Appel du Use Case
        if (empty($errors)) {
            // On instancie le Use Case en lui passant le Repository injecté
            $useCase = new RegisterUserUseCase($this->userRepo);

            // Exécution de la logique métier
            $result = $useCase->execute($_POST);

            if ($result['success']) {
                $success = $result['message'];
                // Reset du formulaire en cas de succès
                $old = ['name' => '', 'last_name' => '', 'email' => '', 'sexe' => '', 'specialite' => ''];
            } else {
                // Récupération des erreurs métier
                $errors = $result['errors'];
            }
        }

        $specialites = self::SPECIALITES_VALIDES;
        View::render('auth/register', compact('errors', 'success', 'old', 'specialites'));
    }

    /**
     * Déconnecte l'utilisateur.
     */
    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

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