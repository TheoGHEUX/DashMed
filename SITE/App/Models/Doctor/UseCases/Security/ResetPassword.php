<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Security;

use App\Models\Doctor\Interfaces\IDoctorReadRepository;
use App\Models\Doctor\Interfaces\IDoctorWriteRepository;
use App\Models\Doctor\Interfaces\ISecurityReadRepository;
use App\Models\Doctor\Interfaces\ISecurityWriteRepository;

class ResetPassword
{
    private IDoctorReadRepository $doctorRead;
    private IDoctorWriteRepository $doctorWrite;
    private ISecurityReadRepository $securityRead;
    private ISecurityWriteRepository $securityWrite;

    public function __construct(
        IDoctorReadRepository $doctorRead,
        IDoctorWriteRepository $doctorWrite,
        ISecurityReadRepository $securityRead,
        ISecurityWriteRepository $securityWrite
    ) {
        $this->doctorRead = $doctorRead;
        $this->doctorWrite = $doctorWrite;
        $this->securityRead = $securityRead;
        $this->securityWrite = $securityWrite;
    }

    public function execute(string $email, string $token, string $newPassword): array
    {
        $tokenData = $this->securityRead->findResetToken($email);

        if (!$tokenData || !hash_equals($tokenData['token_hash'], hash('sha256', $token))) {
            return ['success' => false, 'error' => 'Lien invalide ou expiré.'];
        }

        $doctor = $this->doctorRead->findByEmail($email);
        if (!$doctor) {
            return ['success' => false, 'error' => 'Utilisateur introuvable.'];
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->doctorWrite->updatePassword($doctor->getId(), $newHash);
        $this->securityWrite->deleteResetToken($email);

        return ['success' => true];
    }
}