<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Security;

use App\Models\Doctor\Interfaces\IDoctorReadRepository;
use App\Models\Doctor\Interfaces\ISecurityWriteRepository;
use Core\Services\MailerService;

class ForgottenPassword
{
    private IDoctorReadRepository $readRepo;
    private ISecurityWriteRepository $writeRepo;
    private MailerService $mailer;

    public function __construct(
        IDoctorReadRepository $readRepo,
        ISecurityWriteRepository $writeRepo,
        MailerService $mailer
    ) {
        $this->readRepo = $readRepo;
        $this->writeRepo = $writeRepo;
        $this->mailer = $mailer;
    }

    public function execute(string $email): void
    {
        // 1. Vérifier si l'utilisateur existe
        // Le repository retourne un objet DoctorEntity (ou null)
        $doctor = $this->readRepo->findByEmail($email);

        if (!$doctor) {
            // Protection contre l'énumération des utilisateurs (réponse silencieuse)
            return;
        }

        // 2. Génération du token
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token); // Hachage SHA-256
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 heure

        // 3. Stockage du token haché
        $this->writeRepo->storeResetToken($email, $tokenHash, $expiresAt);

        // 4. Construction de l'URL avec le token BRUT
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';

        $resetUrl = "$protocol://$domain/reset-password?token=" . urlencode($token) . "&email=" . urlencode($email);

        // 5. Préparation de l'email
        // ✅ CORRECTION ICI : On utilise les accesseurs de l'objet ($doctor->get...)
        $prenom = $doctor->getPrenom();
        $nom = $doctor->getNom();

        $variables = [
            'name' => "$prenom $nom",
            'url' => $resetUrl
        ];

        // Chargement de la vue email
        $htmlBody = $this->loadView('reset-password', $variables);

        // Envoi
        $this->mailer->send(
            $email,
            'Réinitialisation de votre mot de passe - DashMed',
            $htmlBody
        );
    }

    private function loadView(string $viewName, array $variables = []): string
    {
        extract($variables);
        ob_start();
        $path = dirname(__DIR__, 4) . '/Views/emails/' . $viewName . '.php';
        if (file_exists($path)) {
            require $path;
        }
        return ob_get_clean();
    }
}