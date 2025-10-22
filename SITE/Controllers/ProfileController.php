<?php
namespace Controllers;

final class ProfileController
{
    public function show(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        $user = $_SESSION['user']; // id, email, name
        $parts = preg_split('/\s+/', trim($user['name'] ?? ''), 2);
        $first = $parts[0] ?? '';
        $last  = $parts[1] ?? '';
        require __DIR__ . '/../Views/profile.php';
    }
}