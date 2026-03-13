<?php

declare(strict_types=1);

namespace Core\Controller;

use Core\Csrf;

/**
 * Trait pour la gestion et validation des tokens CSRF dans les contrôleurs.
 */
trait CsrfTrait
{
    /**
     * Valide le token CSRF pour les formulaires HTML classiques.
     */
    protected function validateCsrf(): bool
    {
        return Csrf::validate($_POST['csrf_token'] ?? '');
    }

    /**
     * Valide le token CSRF envoyé en header pour API.
     */
    protected function validateApiCsrf(): void
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$token || !Csrf::validateWithoutConsuming($token)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Jeton CSRF invalide ou manquant']);
            exit;
        }
    }
}