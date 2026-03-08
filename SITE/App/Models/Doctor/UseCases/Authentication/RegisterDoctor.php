<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Authentication;

use App\Models\Doctor\Interfaces\IDoctorReadRepository;
use App\Models\Doctor\Interfaces\IDoctorWriteRepository;
use App\Models\Doctor\Interfaces\IDoctorVerificationRepository;
use App\Models\Doctor\Interfaces\IDoctorValidator;
use Core\Services\MailerService;

class RegisterDoctor
{
    private IDoctorReadRepository $readRepo;
    private IDoctorWriteRepository $writeRepo;
    private IDoctorVerificationRepository $verifyRepo;
    private IDoctorValidator $validator;
    private MailerService $mailer;

    public function __construct(
        IDoctorReadRepository $readRepo,
        IDoctorWriteRepository $writeRepo,
        IDoctorVerificationRepository $verifyRepo,
        IDoctorValidator $validator, // ✅ Ajout du paramètre manquant (4ème position)
        MailerService $mailer        // ✅ Décalé en 5ème position
    ) {
        $this->readRepo = $readRepo;
        $this->writeRepo = $writeRepo;
        $this->verifyRepo = $verifyRepo;
        $this->validator = $validator;
        $this->mailer = $mailer;
    }

    public function execute(array $data): array
    {
        // 1. VALIDATION via le Validator injecté
        // Plus besoin de faire des if empty() ici, le validateur s'en charge
        $validationErrors = $this->validator->validateRegistration($data);

        if (!empty($validationErrors)) {
            // On renvoie les erreurs au contrôleur
            return ['success' => false, 'errors' => $validationErrors];
        }

        // 2. Vérification Email unique
        if ($this->readRepo->findByEmail($data['email'])) {
            return ['success' => false, 'errors' => ['Cet email est déjà utilisé.']];
        }

        // 3. Création du compte
        $hashedPassword = password_hash($data['password'], PASSWORD_ARGON2ID);

        $doctorId = $this->writeRepo->create([
            'prenom' => $data['prenom'],
            'nom' => $data['nom'],
            'email' => $data['email'],
            'password_hash' => $hashedPassword,
            'specialite' => $data['specialite'],
            'telephone' => $data['telephone'] ?? null,
            'sexe' => $data['sexe']
        ]);

        if (!$doctorId) {
            return ['success' => false, 'errors' => ['Erreur technique lors de l\'enregistrement.']];
        }

        // 4. Token & Email
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $this->verifyRepo->setVerificationToken($data['email'], $token, $expires);

        $this->sendWelcomeEmail($data['email'], $data['nom'], $token);

        return ['success' => true];
    }

    private function sendWelcomeEmail(string $email, string $name, string $token): void
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';

        $variables = [
            'name' => $name,
            'url' => "$protocol://$domain/verify-email?token=$token"
        ];

        $htmlBody = $this->loadView('verify-email', $variables);

        $this->mailer->send(
            $email,
            'Bienvenue sur DashMed - Confirmez votre compte',
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