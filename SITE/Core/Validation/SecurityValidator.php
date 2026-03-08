<?php

declare(strict_types=1);

namespace Core\Validation;

class SecurityValidator
{
    /**
     * Valide le format d'une adresse email.
     * Vérifie la syntaxe standard ET la présence d'un domaine avec extension (ex: .com).
     *
     * @param string $email L'email à tester
     * @return string|null Retourne un message d'erreur ou null si tout est valide.
     */
    public static function validateEmail(string $email): ?string
    {
        // 1. Validation syntaxique de base PHP (RFC 822)
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "L'adresse email est invalide.";
        }

        // 2. Vérification supplémentaire pour le format "domaine.extension"
        // filter_var laisse passer "user@localhost", mais on veut généralement "user@site.com"
        if (!preg_match('/@.+\..+$/', $email)) {
            return "L'email doit contenir un domaine valide (ex: @gmail.com).";
        }

        return null; // Pas d'erreur
    }

    /**
     * Valide la robustesse d'un mot de passe selon les règles de l'ANSSI / CNIL.
     * - 12 caractères minimum
     * - 1 Majuscule
     * - 1 Minuscule
     * - 1 Chiffre
     * - 1 Caractère spécial
     *
     * @param string $password Le mot de passe
     * @param string|null $confirm La confirmation du mot de passe (optionnel)
     * @return array La liste des erreurs trouvées (tableau vide si tout est OK).
     */
    public static function validatePassword(string $password, string $confirm = null): array
    {
        $errors = [];

        // Règle 1 : Longueur
        if (strlen($password) < 12) {
            $errors[] = "Le mot de passe doit faire au moins 12 caractères.";
        }

        // Règle 2 : Complexité (Regex)
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une majuscule.";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une minuscule.";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un chiffre.";
        }
        // \W correspond à tout caractère qui n'est pas une lettre ou un chiffre (donc spécial)
        // L'underscore _ est considéré comme un caractère "mot" par \w, donc on l'ajoute explicitement si on veut l'autoriser comme spécial
        if (!preg_match('/[\W_]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un caractère spécial (!, @, #, $, etc.).";
        }

        // Règle 3 : Confirmation (si fournie)
        if ($confirm !== null && $password !== $confirm) {
            $errors[] = "La confirmation du mot de passe ne correspond pas.";
        }

        return $errors;
    }
}