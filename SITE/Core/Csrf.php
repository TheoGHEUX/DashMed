<?php

namespace Core;

/**
 * Gestionnaire CSRF simple.
 *
 * Génère et valide des tokens CSRF stockés dans la session afin de protéger les formulaires
 * contre la falsification de requêtes inter-sites.
 * Les tokens expirent après 2 heures et sont consommés après validation.
 *
 * @package Core
 */
final class Csrf
{
    /**
     * Retourne le token CSRF pour la session courante.
     * Génère un nouveau token (64 caractères hex) si aucun n'existe.
     *
     * @return string Token CSRF
     * @throws \Exception Si random_bytes échoue
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
     * Validations effectuées :
     * - Token présent en session
     * - Correspondance exacte (hash_equals pour éviter timing attacks)
     * - Durée de vie respectée (par défaut 2 heures)
     *
     * Le token est consommé après une validation réussie pour éviter la réutilisation.
     * En cas d'expiration, le token est supprimé de la session.
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
