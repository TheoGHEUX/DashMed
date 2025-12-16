<?php

namespace Core;

/**
 * Gestionnaire CSRF simple.
 *
 * Génère et valide des tokens CSRF stockés dans la session afin de protéger les formulaires
 * contre la falsification de requêtes inter-sites.
 */
final class Csrf
{
    /**
     * Retourne le token CSRF pour la session courante. En crée un nouveau si nécessaire.
     *
     * @return string Token CSRF
     * @throws \Exception Si random_bytes échoue (propagé)
     */
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Valide un token CSRF fourni et vérifie sa durée de vie.
     *
     * @param string $token Token fourni par le formulaire
     * @param int $ttlSeconds Durée de vie en secondes (par défaut 7200s = 2h)
     * @return bool Vrai si le token est valide, faux sinon
     */
    public static function validate(string $token, int $ttlSeconds = 7200): bool
    {
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            return false;
        }
        if (!empty($_SESSION['csrf_token_time']) && (time() - (int)$_SESSION['csrf_token_time']) > $ttlSeconds) {
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
            return false;
        }
        // Token consommé : suppression pour éviter la réutilisation
        unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
        return true;
    }
}
