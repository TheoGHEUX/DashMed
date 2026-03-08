<?php

declare(strict_types=1);

namespace App\Models\Doctor\Validators;

use App\Models\Doctor\Interfaces\IDoctorValidator;
use Core\Validation\SecurityValidator;

class DoctorValidator implements IDoctorValidator
{
    /**
     * Valide toutes les données du formulaire d'inscription.
     *
     * @param array $data Les données $_POST nettoyées
     * @return array La liste des erreurs (vide si tout est bon)
     */
    public function validateRegistration(array $data): array
    {
        $errors = [];

        // 1. Champs obligatoires spécifiques au Médecin
        if (empty($data['nom'])) {
            $errors[] = "Le nom est obligatoire.";
        }
        if (empty($data['prenom'])) {
            $errors[] = "Le prénom est obligatoire.";
        }
        if (empty($data['sexe'])) {
            $errors[] = "Veuillez sélectionner votre sexe.";
        }
        if (empty($data['specialite'])) {
            $errors[] = "Veuillez sélectionner une spécialité.";
        }

        // 2. Validation de l'Email (Format + Domaine) via le Core
        // Utilise la méthode statique qu'on a créée dans Core/Validation/SecurityValidator
        $emailError = SecurityValidator::validateEmail($data['email'] ?? '');
        if ($emailError) {
            $errors[] = $emailError;
        }

        // 3. Validation du Mot de passe (Complexité + Confirmation) via le Core
        // Mot de passe : 12 chars, Maj, Min, Chiffre, Spécial
        $passwordErrors = SecurityValidator::validatePassword(
            $data['password'] ?? '',
            $data['confirm'] ?? null
        );

        // On fusionne les erreurs de mot de passe avec les autres erreurs
        return array_merge($errors, $passwordErrors);
    }
}