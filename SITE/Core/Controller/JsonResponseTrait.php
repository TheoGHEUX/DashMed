<?php

declare(strict_types=1);

namespace Core\Controller;

trait JsonResponseTrait
{
    /**
     * Envoie une réponse JSON (Méthode principale utilisée par les contrôleurs).
     */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    // Helpers (Bonus)
    protected function jsonSuccess(array $data = []): void
    {
        $this->json(['success' => true, ...$data]);
    }

    protected function jsonError(string $message, int $code = 400): void
    {
        $this->json(['success' => false, 'error' => $message], $code);
    }

    protected function getJsonInput(): array
    {
        $content = file_get_contents('php://input');
        if ($content === false) {
            return [];
        }
        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : [];
    }
}
