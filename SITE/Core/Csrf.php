<?php

namespace Core;

/**
 * Gestionnaire CSRF
 *
 * Objectif : Protéger contre les attaques CSRF (Cross-Site Request Forgery).
 *
 * Génère et valide des jetons CSRF stockés dans la session afin de protéger les
 * formulaires contre la falsification de requêtes inter-sites.
 *
 * @package Core
 */
final class Csrf
{
    /**
     * Retourne le jeton CSRF de la session courante.
     *
     * Génère un nouveau jeton (64 caractères hex) si aucun n'existe.
     *
     * @return string      Jeton CSRF
     * @throws \Exception  Si random_bytes échoue
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
     * Valide un jeton CSRF fourni et vérifie sa durée de vie.
     *
     * Validations effectuées :
     * - Jeton présent en session
     * - Correspondance exacte (hash_equals pour éviter timing attacks)
     * - Durée de vie respectée (par défaut 2 heures)
     *
     * @param string  $token Jeton fourni par le formulaire
     * @param int     $ttlSeconds Durée de vie en secondes (7200s = 2h)
     * @return bool   Vrai si le jeton est valide, faux sinon
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
        // Jeton consommé → suppression pour éviter la réutilisation
        unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
        return true;
    }
}
