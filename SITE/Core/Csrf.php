<?php
namespace Core;

// Cette classe sert à gérer les tokens pour sécuriser les formulaires.
// Elle permet de générer un token unique pour chaque session et de vérifier s'il est validite ou pas lors du remplissage d'un formulaire.
// Le but de cette classe est donc de protéger l'application contre les attaques par falsification de requête.
final class Csrf
{
    // Génère et retourne un tokenunique pour la session courante.
    // Si le token n'existe pas déjà, il en crée un nouveau.
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            // Génère un token aléatoire et le stocke dans la session
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            // Enregistre le moment de la création du token
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }

    // Vérifie la validité du token
    public static function validate(string $token, int $ttlSeconds = 7200): bool
    {
        // Vérifie que le token en session existe et correspond à celui fourni
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            return false;
        }
        // Vérifie que le token n'a pas expiré en fonction de sa durée de vie
        if (!empty($_SESSION['csrf_token_time']) && (time() - (int)$_SESSION['csrf_token_time']) > $ttlSeconds) {
            // Supprime le token de la session en cas d'expiration
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
            return false;
        }
        // Supprime le token de la session après validation
        unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
        return true;
    }
}
