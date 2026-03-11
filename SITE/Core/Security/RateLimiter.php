<?php

declare(strict_types=1);

namespace Core\Security;

final class RateLimiter
{
    /**
     * Vérifie si l'action est autorisée ou bloquée.
     *
     * @param string $key La clé unique pour identifier l'action (ex: 'login_attempts')
     * @param int $maxAttempts Nombre maximum d'essais autorisés
     * @param int $windowSeconds Fenêtre de temps en secondes (ex: 3600 pour 1h)
     * @return bool True si l'utilisateur est BLOQUÉ, False s'il peut continuer.
     */
    public static function isBlocked(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $attempts = $_SESSION[$key] ?? [];
        $now = time();

        // On ne garde que les tentatives récentes (celles qui sont dans la fenêtre de temps)
        $attempts = array_filter($attempts, function ($timestamp) use ($now, $windowSeconds) {
            return ($now - $timestamp) <= $windowSeconds;
        });

        // On met à jour la session avec la liste nettoyée
        $_SESSION[$key] = $attempts;

        // Si le nombre de tentatives restantes est >= au max autorisé, on bloque
        return count($attempts) >= $maxAttempts;
    }

    /**
     * Enregistre une tentative échouée.
     * À appeler quand le mot de passe est faux ou le CSRF invalide.
     *
     * @param string $key La clé unique (ex: 'login_attempts')
     */
    public static function recordAttempt(string $key): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // On ajoute l'heure actuelle à la liste
        $_SESSION[$key][] = time();
    }

    /**
     * Efface les tentatives après un succès.
     * À appeler quand l'utilisateur réussit à se connecter ou s'inscrire.
     *
     * @param string $key La clé unique (ex: 'login_attempts')
     */
    public static function clear(string $key): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        unset($_SESSION[$key]);
    }
}