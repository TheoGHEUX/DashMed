<?php

namespace Controllers;

use Models\User;
use Core\Csrf;
use Core\Mailer;

/**
 * Contrôleur : Authentification & inscription
 *
 * Gère l'affichage et le traitement des formulaires d'inscription, de connexion
 * et la déconnexion. Fournit également la liste des spécialités valides.
 *
 * Méthodes principales :
 *  - showRegister(), register()
 *  - showLogin(), login()
 *  - logout()
 *
 * Variables passées aux vues :
 *  - $errors  (array)
 *  - $success (string)
 *  - $old     (array) pour remplir les formulaires après erreur
 *
 * @package Controllers
 */
final class AuthController
{
    // Liste complète des spécialités médicales valides
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
        } elseif (!$errors) {
            // Rien: bloc laissé pour clarté éventuelle
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

    public function login(): void
    {
        $errors = [];
        $success = '';
        $old = [
            'email' => trim((string)($_POST['email'] ?? '')),
        ];
        $password = (string)($_POST['password'] ?? '');
        $csrf     = (string)($_POST['csrf_token'] ?? '');

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
                } elseif (!$user['email_verified']) {
                    $errors[] = 'Adresse email non vérifiée. '
                        . 'Consultez votre boîte de réception et cliquez sur le lien de vérification.';
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
            }
        }

        require __DIR__ . '/../Views/auth/login.php';
    }

    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
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

