<?php
namespace Controllers;

use Models\User;
use Core\Csrf;
use Core\Mailer;

final class AuthController
{
    public function showRegister(): void
    {
        $errors = [];
        $success = '';
        $old = ['name' => '', 'last_name' => '', 'email' => ''];
        require __DIR__ . '/../Views/auth/register.php';
    }

    public function register(): void
    {
        $errors = [];
        $success = '';
        $old = [
            'name'      => trim((string)($_POST['name'] ?? '')),
            'last_name' => trim((string)($_POST['last_name'] ?? '')),
            'email'     => trim((string)($_POST['email'] ?? '')),
        ];
        $password         = (string)($_POST['password'] ?? '');
        $password_confirm = (string)($_POST['password_confirm'] ?? '');
        $csrf             = (string)($_POST['csrf_token'] ?? '');

        if (!Csrf::validate($csrf)) {
            $errors[] = 'Session expirée ou jeton CSRF invalide.';
        }

        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse email invalide.';
        }

        if ($password !== $password_confirm) {
            $errors[] = 'Mots de passe différents.';
        }

        if (
            strlen($password) < 12 ||
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/\d/', $password) ||
            !preg_match('/[^A-Za-z0-9]/', $password)
        ) {
            $errors[] = 'Le mot de passe doit contenir au moins 12 caractères, avec majuscules, minuscules, chiffres et un caractère spécial.';
        }

        if (!$errors && User::emailExists($old['email'])) {
            $errors[] = 'Un compte existe déjà avec cette adresse email.';
        }

        if (!$errors) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            try {
                // Créer le compte avec activation requise
                $token = User::createWithActivation($old['name'], $old['last_name'], $old['email'], $hash);

                if ($token) {
                    // Construction de l'URL d'activation
                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'];
                    $activationUrl = $protocol . '://' . $host . '/activate?token=' . urlencode($token);

                    // Envoi du mail d'activation
                    $mailSent = Mailer::sendActivationEmail($old['email'], $old['name'], $activationUrl);

                    if ($mailSent) {
                        $success = 'Compte créé avec succès ! 🎉<br><br>'
                            . 'Un email d\'activation a été envoyé à <strong>' . htmlspecialchars($old['email']) . '</strong>.<br>'
                            . 'Veuillez vérifier votre boîte de réception (et vos spams) et cliquer sur le lien pour activer votre compte.<br><br>'
                            . '<em>Le lien est valable 24 heures.</em>';
                    } else {
                        $success = 'Compte créé, mais l\'email d\'activation n\'a pas pu être envoyé.<br>'
                            . 'Veuillez contacter le support technique.';
                    }

                    $old = ['name' => '', 'last_name' => '', 'email' => ''];
                } else {
                    $errors[] = 'Erreur lors de la création du compte. Veuillez réessayer.';
                }
            } catch (\Throwable $e) {
                $errors[] = 'Erreur système. Veuillez réessayer plus tard.';
                error_log('Erreur inscription : ' . $e->getMessage());
            }
        }

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
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $errors = [];
        $success = '';
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = (string)($_POST['password'] ?? '');
        $csrf = (string)($_POST['csrf_token'] ?? '');

        if (!Csrf::validate($csrf)) {
            $errors[] = 'Session expirée ou jeton CSRF invalide.';
        }

        if (!$errors) {
            $user = User::findByEmail($email);

            if (!$user || !password_verify($password, $user['password'])) {
                $errors[] = 'Identifiants invalides.';
            } elseif (!$user['compte_actif']) {
                $errors[] = 'Votre compte n\'est pas encore activé. '
                    . 'Veuillez vérifier vos emails et cliquer sur le lien d\'activation. '
                    . 'Pensez à vérifier vos spams !';
            } else {
                // Connexion réussie
                session_regenerate_id(true);
                $first = $user['prenom'] ?? '';
                $last  = $user['nom'] ?? '';
                $_SESSION['user'] = [
                    'id'    => (int)$user['med_id'],
                    'email' => $user['email'],
                    'name'  => trim($first . ' ' . $last)
                ];
                header('Location: /accueil');
                exit;
            }
        }

        $old = ['email' => $email];
        require __DIR__ . '/../Views/auth/login.php';
    }

    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        header('Location: /');
        exit;
    }
}