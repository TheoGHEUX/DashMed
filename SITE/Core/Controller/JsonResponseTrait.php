<?php

declare(strict_types=1);

namespace Core\Controller;

trait JsonResponseTrait
{
    protected function jsonSuccess(array $data = []): void
    {
        $this->sendJson(['success' => true, ...$data]);
    }

    protected function jsonError(string $message, int $code = 400): void
    {
        http_response_code($code);
        $this->sendJson(['success' => false, 'error' => $message]);
    }

    private function sendJson(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    protected function getJsonInput(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
}