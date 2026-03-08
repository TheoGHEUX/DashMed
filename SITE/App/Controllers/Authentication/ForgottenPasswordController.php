<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;
use Core\Security\RateLimiter;
use Core\Services\MailerService;
use App\Models\Doctor\Repositories\DoctorReadRepository;
use App\Models\Doctor\Repositories\SecurityWriteRepository;
use App\Models\Doctor\UseCases\Security\ForgottenPassword;

final class ForgottenPasswordController extends AbstractController
{
    private ForgottenPassword $useCase;

    public function __construct()
    {
        $this->useCase = new ForgottenPassword(
            new DoctorReadRepository(),
            new SecurityWriteRepository(),
            new MailerService()
        );
    }

    public function showForm(): void
    {
        $success = $_SESSION['success'] ?? '';
        $errors = $_SESSION['errors'] ?? [];

        unset($_SESSION['success'], $_SESSION['errors']);

        $this->render('Authentication/forgotten-password', [
            'errors' => $errors,
            'success' => $success,
            'old' => ['email' => '']
        ]);
    }

    public function submit(): void
    {
        $this->startSession();

        // 1. Rate Limiting
        if (RateLimiter::isBlocked('forgot_password_attempts', 5, 3600)) {
            $_SESSION['success'] = "Si un compte existe, un lien a été envoyé. (Limite atteinte)";
            $this->redirect('/forgotten-password');
            return;
        }

        // 2. CSRF
        if (!$this->validateCsrf()) {
            $_SESSION['errors'] = ['Session expirée.'];
            $this->redirect('/forgotten-password');
            return;
        }

        $email = trim($this->getPost('email') ?? '');

        // 3. ✅ VALIDATION STRICTE (C'est ici que ça manquait)
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Si l'email est vide ou invalide, on affiche une ERREUR, pas un succès.
            $_SESSION['errors'] = ['Veuillez entrer une adresse email valide.'];
            $this->redirect('/forgotten-password');
            return;
        }

        // 4. Exécution (Seulement si l'email est valide)
        try {
            // On enregistre la tentative AVANT d'exécuter (pour limiter le spam même sur des emails valides)
            RateLimiter::recordAttempt('forgot_password_attempts');

            $this->useCase->execute($email);

            // Message neutre de succès
            $_SESSION['success'] = "Si un compte existe à cette adresse mail, un lien de réinitialisation a été envoyé.\nN'oubliez pas de vérifier vos spams.";

        } catch (\Exception $e) {
            // En cas d'erreur technique (ex: serveur mail en panne), on log mais on ne le dit pas à l'user
            error_log("[FORGOT PWD ERROR] " . $e->getMessage());
            $_SESSION['success'] = "Si un compte existe à cette adresse mail, un lien de réinitialisation a été envoyé.\nN'oubliez pas de vérifier vos spams.";
        }

        $this->redirect('/forgotten-password');
    }
}