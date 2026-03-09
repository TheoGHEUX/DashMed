<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Security;

use App\Models\Doctor\Interfaces\IDoctorRepository;
use App\Models\Doctor\Interfaces\ISecurityRepository;
use App\Models\Doctor\Validators\DoctorValidator;

class ResetPassword
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
        $errors = $this->validator->validatePassword($newPassword);
        if (!empty($errors)) {
            return ['success' => false, 'error' => implode("\n", $errors)];
        }

        $tokenHash = hash('sha256', $token);

        $tokenData = $this->securityRepo->findResetTokenByEmailAndToken($email, $tokenHash);
        if (!$tokenData) {
            return ['success' => false, 'error' => 'Lien invalide ou expiré.'];
        }

        $doctor = $this->repo->findByEmail($email);
        if (!$doctor) {
            return ['success' => false, 'error' => 'Utilisateur introuvable.'];
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->repo->updatePassword($doctor->getId(), $newHash);
        $this->securityRepo->deleteResetToken($email);

        return ['success' => true];
    }
}