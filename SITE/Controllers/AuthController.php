<?php
namespace Controllers;

use Models\User;
use Core\Csrf;
use Core\Mailer;

final class AuthController
{
    // Liste officielle des spécialités (correspond à la contrainte CHECK de ta BDD)
    private const SPECIALITES = [
        'Addictologie', 'Algologie', 'Allergologie', 'Anesthésie-Réanimation',
        'Cancérologie', 'Cardio-vasculaire HTA', 'Chirurgie', 'Dermatologie',
        'Diabétologie-Endocrinologie', 'Génétique', 'Gériatrie',
        'Gynécologie-Obstétrique', 'Hématologie', 'Hépato-gastro-entérologie',
        'Imagerie médicale', 'Immunologie', 'Infectiologie', 'Médecine du sport',
        'Médecine du travail', 'Médecine générale', 'Médecine légale',
        'Médecine physique et de réadaptation', 'Néphrologie', 'Neurologie',
        'Nutrition', 'Ophtalmologie', 'ORL', 'Pédiatrie', 'Pneumologie',
        'Psychiatrie', 'Radiologie', 'Rhumatologie', 'Sexologie',
        'Toxicologie', 'Urologie'
    ];

    // ============================================================
    // INSCRIPTION
    // ============================================================

    public function showRegister(): void
    {
        $errors = [];
        $success = '';
        $old = $this->getEmptyFormData();
        require __DIR__ . '/../Views/auth/register.php';
    }

    public function register(): void
    {
        $errors = [];
        $success = '';
        $old = $this->getFormData();
        $password = (string)($_POST['password'] ?? '');
        $password_confirm = (string)($_POST['password_confirm'] ?? '');
        $csrf = (string)($_POST['csrf_token'] ?? '');

        // Validation
        $errors = $this->validateRegistration($old, $password, $password_confirm, $csrf);

        // Création du compte
        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            try {
                $token = User::createWithActivation(
                    $old['prenom'],
                    $old['nom'],
                    $old['email'],
                    $hash,
                    $old['sexe'],
                    $old['specialite']
                );

                // Dans la méthode register(), remplace la partie envoi email par :

                if (!$token) {
                    $errors[] = 'Erreur lors de la création du compte. Veuillez réessayer.';
                } else {
                    // ✅ MODE DEV : Active directement le compte en local
                    $isLocal = ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1');

                    if ($isLocal) {
                        // Active directement sans email
                        if (User::activateAccount($token)) {
                            $success = 'Compte créé et activé avec succès ! 🎉<br><br>'
                                . '<strong>Mode développement :</strong> Votre compte a été activé automatiquement.<br>'
                                . 'Vous pouvez maintenant vous connecter.';
                            $old = $this->getEmptyFormData();
                        } else {
                            $errors[] = 'Erreur lors de l\'activation automatique.';
                        }
                    } else {
                        // Mode production : envoi email normal
                        $activationUrl = $this->buildActivationUrl($token);
                        $mailSent = Mailer::sendActivationEmail($old['email'], $old['prenom'], $activationUrl);

                        if ($mailSent) {
                            $success = 'Compte créé avec succès ! 🎉<br><br>'
                                . 'Un email d\'activation a été envoyé à <strong>' . htmlspecialchars($old['email']) . '</strong>.<br>'
                                . 'Veuillez vérifier votre boîte de réception (et vos spams).<br><br>'
                                . '<em>Le lien est valable 24 heures.</em>';
                            $old = $this->getEmptyFormData();
                        } else {
                            User::deleteUnactivatedAccount($old['email']);
                            $errors[] = 'Impossible d\'envoyer l\'email d\'activation.';
                            error_log('[REGISTER] Mail non envoyé pour ' . $old['email']);
                        }
                    }
                }
            } catch (\Throwable $e) {
                $errors[] = 'Erreur système. Veuillez réessayer plus tard.';
                error_log('Erreur inscription : ' . $e->getMessage());
            }
        }

        require __DIR__ . '/../Views/auth/register.php';
    }

    // ============================================================
    // CONNEXION
    // ============================================================

    public function showLogin(): void
    {
        $errors = [];
        $success = isset($_GET['reset']) && $_GET['reset'] === '1'
            ? 'Votre mot de passe a été réinitialisé. Vous pouvez vous connecter.'
            : '';
        $old = ['email' => ''];
        require __DIR__ . '/../Views/auth/login.php';
    }

    public function login(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $errors = [];
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = (string)($_POST['password'] ?? '');
        $csrf = (string)($_POST['csrf_token'] ?? '');

        // ✅ AJOUT : Rate limiting
        if ($this->isRateLimited()) {
            $errors[] = 'Trop de tentatives de connexion. Veuillez attendre 15 minutes.';
            $old = ['email' => $email];
            require __DIR__ . '/../Views/auth/login.php';
            return;
        }

        // Validation CSRF
        if (!Csrf::validate($csrf)) {
            $errors[] = 'Session expirée ou jeton CSRF invalide.';
        }

        if (empty($errors)) {
            $medecin = User::findByEmail($email);

            if (!$medecin || !password_verify($password, $medecin['mdp'])) {
                $errors[] = 'Identifiants invalides.';
                $this->recordFailedAttempt(); // ✅ Enregistre la tentative échouée
            } elseif (!$medecin['compte_actif']) {
                $errors[] = 'Votre compte n\'est pas encore activé. '
                    . 'Veuillez vérifier vos emails et cliquer sur le lien d\'activation.';
            } else {
                // Connexion réussie
                $this->loginUser($medecin);
                return;
            }
        }

        $old = ['email' => $email];
        require __DIR__ . '/../Views/auth/login.php';
    }

    // ============================================================
    // DÉCONNEXION
    // ============================================================

    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }

        session_destroy();

        if (headers_sent()) {
            die('Erreur: headers déjà envoyés');
        }

        header('Location: /');
        exit;
    }

    // ============================================================
    // MÉTHODES PRIVÉES
    // ============================================================

    /**
     * Retourne un tableau vide pour le formulaire
     */
    private function getEmptyFormData(): array
    {
        return ['prenom' => '', 'nom' => '', 'email' => '', 'sexe' => '', 'specialite' => ''];
    }

    /**
     * Récupère et nettoie les données du formulaire
     */
    private function getFormData(): array
    {
        return [
            'prenom'     => trim((string)($_POST['prenom'] ?? '')),
            'nom'        => trim((string)($_POST['nom'] ?? '')),
            'email'      => trim((string)($_POST['email'] ?? '')),
            'sexe'       => trim((string)($_POST['sexe'] ?? '')),
            'specialite' => trim((string)($_POST['specialite'] ?? '')),
        ];
    }

    /**
     * Valide toutes les données d'inscription
     */
    private function validateRegistration(array $old, string $password, string $confirm, string $csrf): array
    {
        $errors = [];

        // CSRF
        if (!Csrf::validate($csrf)) {
            $errors[] = 'Session expirée ou jeton CSRF invalide.';
        }

        // Champs obligatoires
        if (empty($old['prenom']) || empty($old['nom'])) {
            $errors[] = 'Le prénom et le nom sont obligatoires.';
        }

        // Sexe
        if (!in_array($old['sexe'], ['M', 'F'])) {
            $errors[] = 'Veuillez sélectionner votre sexe (M ou F).';
        }

        // ✅ CORRECTION : Validation stricte de la spécialité
        if (!in_array($old['specialite'], self::SPECIALITES)) {
            $errors[] = 'Veuillez sélectionner une spécialité valide.';
        }

        // Longueur
        if (strlen($old['prenom']) > 50 || strlen($old['nom']) > 100) {
            $errors[] = 'Le prénom (max 50) et le nom (max 100) sont trop longs.';
        }

        if (strlen($old['email']) > 150) {
            $errors[] = 'L\'adresse email est trop longue (max 150 caractères).';
        }

        // Email
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse email invalide.';
        }

        // Mot de passe
        if ($password !== $confirm) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }

        if (!$this->isPasswordStrong($password)) {
            $errors[] = 'Le mot de passe doit contenir au moins 12 caractères, avec majuscules, minuscules, chiffres et un caractère spécial.';
        }

        // Email existant
        if (empty($errors) && User::emailExists($old['email'])) {
            $errors[] = 'Un compte existe déjà avec cette adresse email.';
        }

        return $errors;
    }

    /**
     * Vérifie si le mot de passe est fort
     */
    private function isPasswordStrong(string $password): bool
    {
        return strlen($password) >= 12
            && preg_match('/[A-Z]/', $password)
            && preg_match('/[a-z]/', $password)
            && preg_match('/\d/', $password)
            && preg_match('/[^A-Za-z0-9]/', $password);
    }

    /**
     * Construit l'URL d'activation
     */
    private function buildActivationUrl(string $token): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . '://' . $host . '/activate?token=' . urlencode($token);
    }

    /**
     * ✅ AJOUT : Vérifie si l'utilisateur est rate-limité
     */
    private function isRateLimited(): bool
    {
        $maxAttempts = 5;
        $windowSeconds = 900; // 15 minutes
        $now = time();

        $attempts = $_SESSION['login_attempts'] ?? [];
        $attempts = array_filter($attempts, fn($ts) => ($now - $ts) <= $windowSeconds);
        $_SESSION['login_attempts'] = $attempts;

        return count($attempts) >= $maxAttempts;
    }

    /**
     * ✅ AJOUT : Enregistre une tentative échouée
     */
    private function recordFailedAttempt(): void
    {
        $_SESSION['login_attempts'][] = time();
    }

    /**
     * ✅ AJOUT : Connecte l'utilisateur et redirige
     */
    private function loginUser(array $medecin): void
    {
        if (headers_sent()) {
            die('Erreur: headers déjà envoyés');
        }

        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'         => (int)$medecin['med_id'],
            'email'      => $medecin['email'],
            'prenom'     => $medecin['prenom'],
            'nom'        => $medecin['nom'],
            'name'       => trim($medecin['prenom'] . ' ' . $medecin['nom']),
            'sexe'       => $medecin['sexe'],
            'specialite' => $medecin['specialite']
        ];

        unset($_SESSION['login_attempts']); // ✅ Réinitialise les tentatives

        header('Location: /accueil');
        exit;
    }
}