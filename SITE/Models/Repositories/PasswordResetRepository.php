<?php

namespace Models\Repositories;

use Core\Database;
use PDO;

/**
 * Dépôt de Réinitialisation de Mot de Passe
 *
 * Gère les tokens pour la procédure Mot de passe oublié
 *
 * Responsable de la vérification, de la récupération et de la non validation
 * des liens de réinitialisation envoyés par mail
 *
 * @package Models\Repositories
 */
class PasswordResetRepository
{
    private PDO $db;

    /**
     * Constructeur : Initialise la connexion à la base de données
     */
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Vérifie la validité d'un token pour une adresse mail donnée
     *
     * Contrôle plusieurs critères de sécurité :
     * 1. L'email correspond au token
     * 2. Le token (haché) existe en base
     * 3. La date d'expiration n'est pas dépassée
     * 4. Le token n'a pas déjà été utilisé
     *
     * @param string $email L'email de l'utilisateur
     * @param string $token Le token brut reçu par mail
     *
     * @return bool Vrai si le token est valide et utilisable
     */
    public function isValidToken(string $email, string $token): bool
    {
        if ($email === '' || $token === '') return false;

        // On compare toujours avec le hash stocké en base
        $tokenHash = hash('sha256', $token);

        $stmt = $this->db->prepare('
            SELECT 1 FROM password_resets
            WHERE LOWER(email) = LOWER(?)
              AND token_hash = ?
              AND expires_at > NOW()
              AND used_at IS NULL
            LIMIT 1
        ');
        $stmt->execute([$email, $tokenHash]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Récupère l'email associé à un token valide
     *
     * Utilise `FOR UPDATE` pour verrouiller la ligne temporairement,
     * empêchant ainsi une utilisation simultanée du même token
     *
     * @param string $token Le token brut
     *
     * @return string|null L'email associé ou null si invalide/expiré
     */
    public function getEmailFromToken(string $token): ?string
    {
        $tokenHash = hash('sha256', $token);

        $stmt = $this->db->prepare('
            SELECT email FROM password_resets
            WHERE token_hash = ?
              AND expires_at > NOW()
              AND used_at IS NULL
            LIMIT 1
            FOR UPDATE
        ');
        $stmt->execute([$tokenHash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? strtolower(trim($row['email'])) : null;
    }

    /**
     * Invalide un token après son utilisation.
     *
     * Marque la date d'utilisation (`used_at`) pour empêcher que le lien
     * ne soit réutilisé une seconde fois
     *
     * @param string $token Le token brut qui vient d'être utilisé
     * @return void
     */
    public function markAsUsed(string $token): void
    {
        $tokenHash = hash('sha256', $token);

        $stmt = $this->db->prepare('
            UPDATE password_resets 
            SET used_at = NOW() 
            WHERE token_hash = ? 
            AND used_at IS NULL
        ');
        $stmt->execute([$tokenHash]);
    }
}