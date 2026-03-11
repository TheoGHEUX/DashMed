<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Security;

use App\Exceptions\ValidationException;
use App\Models\Doctor\Interfaces\IDoctorRepository;
use App\Models\Doctor\Interfaces\ISecurityRepository;
use App\Models\Doctor\Validators\DoctorValidator;
use App\ValueObjects\Email;
use App\ValueObjects\Password;
use Core\Services\TokenGenerator;

final class ResetPassword
{
    private IDoctorRepository $repo;
    private ISecurityRepository $securityRepo;
    private DoctorValidator $validator;

    public function __construct(
        IDoctorRepository $repo,
        ISecurityRepository $securityRepo,
        DoctorValidator $validator
    ) {
        $this->repo = $repo;
        $this->securityRepo = $securityRepo;
        $this->validator = $validator;
    }

    public function execute(string $email, string $token, string $newPassword): array
    {
        try {
            $emailVO = new Email($email);
            $passwordVO = new Password($newPassword);
        } catch (ValidationException $e) {
            return ['success' => false, 'error' => implode("\n", array_values($e->getErrors()))];
        }

        $errors = $this->validator->validatePassword($newPassword);
        if (!empty($errors)) {
            return ['success' => false, 'error' => implode("\n", $errors)];
        }

        $tokenHash = TokenGenerator::hash($token);

        $tokenData = $this->securityRepo->findResetTokenByEmailAndToken($emailVO->getValue(), $tokenHash);
        if (!$tokenData) {
            return ['success' => false, 'error' => 'Lien invalide ou expiré.'];
        }

        $doctor = $this->repo->findByEmail($emailVO->getValue());
        if (!$doctor) {
            return ['success' => false, 'error' => 'Utilisateur introuvable.'];
        }

        $newHash = $passwordVO->hash();
        $this->repo->updatePassword($doctor->getId(), $newHash);
        $this->securityRepo->deleteResetToken($emailVO->getValue());

        return ['success' => true];
    }
}
