
<?php
/**
 * Trait pour la gestion de la validation CSRF dans les contrôleurs.
 * Propose des méthodes pour sécuriser les formulaires et les API contre les attaques CSRF.
 */

declare(strict_types=1);

namespace Core\Controller;

use Core\Csrf;

trait CsrfTrait
{
    protected function validateCsrf(): bool
    {
        return Csrf::validate($_POST['csrf_token'] ?? '');
    }

    protected function validateApiCsrf(): void
    {
        // Headers pour API JSON
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$token || !Csrf::validateWithoutConsuming($token)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Jeton CSRF invalide ou manquant']);
            exit;
        }
    }
}
