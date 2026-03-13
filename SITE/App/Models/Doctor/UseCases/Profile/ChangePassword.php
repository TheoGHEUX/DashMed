<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Profile;

use App\Models\Doctor\Interfaces\IDoctorRepository;
use App\Models\Doctor\Validators\DoctorValidator;
use App\ValueObjects\Password;
use App\Exceptions\ValidationException;

/**
 * Use Case: changement de mot de passe pour un médecin.
 */
final class ChangePassword
{
    private IDoctorRepository $repo;
    private DoctorValidator $validator;

    public function __construct(IDoctorRepository $repo, DoctorValidator $validator)
    {
        $this->repo = $repo;
        $this->validator = $validator;
    }

    /**
     * Valide les mots de passe puis effectue la modification si tout est correct.
     */
    public function execute(int $userId, string $oldPassword, string $newPassword, string $confirmPassword): array
    {
        // Validation basique
        $errors = $this->validator->validatePassword($newPassword, $confirmPassword);
        if (!empty($errors)) {
            return [
                'success' => false,
                'error' => implode("\n", $errors)
            ];
        }

        // Vérifier l'ancien mot de passe
        $user = $this->repo->findById($userId);
        if (!$user || !password_verify($oldPassword, $user->getPasswordHash())) {
            return ['success' => false, 'error' => "L'ancien mot de passe est incorrect."];
        }

        // Utiliser le Value Object pour hasher le nouveau mot de passe
        try {
            $passwordVO = new Password($newPassword);
            $hash = $passwordVO->hash();
        } catch (ValidationException $e) {
            return ['success' => false, 'error' => implode("\n", array_values($e->getErrors()))];
        }

        // Mettre à jour le mot de passe
        if ($this->repo->updatePassword($userId, $hash)) {
            return ['success' => true, 'message' => 'Mot de passe modifié avec succès.'];
        }

        return ['success' => false, 'error' => 'Erreur technique.'];
    }
}