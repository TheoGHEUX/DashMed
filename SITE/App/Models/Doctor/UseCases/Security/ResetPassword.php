<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Security;

use App\Models\Doctor\Interfaces\IDoctorRepository;
use App\Models\Doctor\Interfaces\ISecurityReadRepository;
use App\Models\Doctor\Interfaces\ISecurityWriteRepository;
use App\Models\Doctor\Validators\DoctorValidator;

class ResetPassword
{
    private IDoctorRepository $repo;
    private ISecurityReadRepository $securityRead;
    private ISecurityWriteRepository $securityWrite;
    private DoctorValidator $validator;

    public function __construct(
        IDoctorRepository $repo,
        ISecurityReadRepository $securityRead,
        ISecurityWriteRepository $securityWrite,
        DoctorValidator $validator
    ) {
        $this->repo = $repo;
        $this->securityRead = $securityRead;
        $this->securityWrite = $securityWrite;
        $this->validator = $validator;
    }

    public function execute(string $email, string $token, string $newPassword): array
    {
        $errors = $this->validator->validatePassword($newPassword);
        if (!empty($errors)) {
            return ['success' => false, 'error' => implode("\n", $errors)];
        }

        $tokenHash = hash('sha256', $token);

        // Utilise une requête qui vérifie bien email ET token hash
        $tokenData = $this->securityRead->findResetTokenByEmailAndToken($email, $tokenHash);
        if (!$tokenData) {
            return ['success' => false, 'error' => 'Lien invalide ou expiré.'];
        }

        $doctor = $this->repo->findByEmail($email);
        if (!$doctor) {
            return ['success' => false, 'error' => 'Utilisateur introuvable.'];
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->repo->updatePassword($doctor->getId(), $newHash);
        $this->securityWrite->deleteResetToken($email);

        return ['success' => true];
    }
}