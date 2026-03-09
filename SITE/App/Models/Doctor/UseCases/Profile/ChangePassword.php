<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Profile;

use App\Models\Doctor\Interfaces\IDoctorRepository;
use App\Models\Doctor\Validators\DoctorValidator;

class ChangePassword
{
    private IDoctorRepository $repo;
    private DoctorValidator $validator;

    public function __construct(IDoctorRepository $repo, DoctorValidator $validator)
    {
        $this->repo = $repo;
        $this->validator = $validator;
    }

    public function execute(int $userId, string $oldPassword, string $newPassword, string $confirmPassword): array
    {
        $errors = $this->validator->validatePassword($newPassword, $confirmPassword);
        if (!empty($errors)) {
            return [
                'success' => false,
                'error' => implode("\n", $errors)
            ];
        }

        $user = $this->repo->findById($userId);
        if (!$user || !password_verify($oldPassword, $user->getPasswordHash())) {
            return ['success' => false, 'error' => "L'ancien mot de passe est incorrect."];
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        if ($this->repo->updatePassword($userId, $hash)) {
            return ['success' => true, 'message' => 'Mot de passe modifié avec succès.'];
        }

        return ['success' => false, 'error' => 'Erreur technique.'];
    }
}