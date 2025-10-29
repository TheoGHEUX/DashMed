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

        $user = $_SESSION['user'];
        $first = $user['prenom'] ?? '';
        $last  = $user['nom'] ?? '';

        require __DIR__ . '/../Views/profile.php';
    }
}