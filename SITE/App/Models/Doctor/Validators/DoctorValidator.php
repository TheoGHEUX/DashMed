<?php

declare(strict_types=1);

namespace App\Models\Doctor\Validators;

use App\Models\Doctor\Interfaces\IDoctorValidator;

final class DoctorValidator implements IDoctorValidator
{
    /**
     * Valide une adresse email.
     */
    public function validateEmail(string $email): ?string
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Adresse email invalide.";
        }
        return null;
    }

    /**
     * Valide un mot de passe (avec confirmation)
     */
    public function validatePassword(string $password, ?string $confirm = null): array
    {
        $errors = [];
        if (strlen($password) < 12
            || !preg_match('/[A-Z]/', $password)
            || !preg_match('/[a-z]/', $password)
            || !preg_match('/\d/', $password)
            || !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit faire 12 caractères min. avec Maj, Min, Chiffre et Caractère spécial.';
        }
        if ($confirm !== null && $password !== $confirm) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }
        return $errors;
    }

    /**
     * Valide toutes les données d'inscription d'un médecin.
     * @param array $data Les données du formulaire d'inscription.
     * @return array La liste des erreurs (vide si OK)
     */
    public function validateRegistration(array $data): array
    {
        $errors = [];
        if (empty($data['nom'])) $errors[] = "Le nom est obligatoire.";
        if (empty($data['prenom'])) $errors[] = "Le prénom est obligatoire.";
        if (empty($data['sexe'])) $errors[] = "Veuillez sélectionner votre sexe.";

        $specialites = \App\Models\Doctor\Enums\Specialite::all();
        if (empty($data['specialite']) || !in_array($data['specialite'], $specialites, true)) {
            $errors[] = "Veuillez sélectionner une spécialité.";
        }

        $emailError = $this->validateEmail($data['email'] ?? '');
        if ($emailError) $errors[] = $emailError;

        $passwordErrors = $this->validatePassword($data['password'] ?? '', $data['confirm'] ?? null);
        $errors = array_merge($errors, $passwordErrors);

        return $errors;
    }
}