<?php

namespace Domain\UseCases\Auth;

use Core\Interfaces\UserRepositoryInterface;
use Core\Mailer;
use Exception;

/**
 * Cas d'utilisation : Changement d'adresse email
 */
class ChangeEmailUseCase
{
    private UserRepositoryInterface $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /**
     * Exécute le changement d'email.
     *
     * @param array $data Données du formulaire (userId, currentPassword, newEmail, confirmEmail)
     * @return array Résultat ['success' => bool, 'message' => string, 'errors' => array]
     */
    public function execute(array $data): array
    {
        $errors = [];
        $userId = (int)($data['userId'] ?? 0);
        $currentPassword = (string)($data['current_password'] ?? '');
        $newEmail = trim((string)($data['new_email'] ?? ''));
        $confirmEmail = trim((string)($data['new_email_confirm'] ?? ''));

        // 1. Validations de base
        if ($newEmail !== $confirmEmail) {
            $errors[] = 'Les adresses email ne correspondent pas.';
        }
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse email invalide.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // 2. Vérification utilisateur
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return ['success' => false, 'errors' => ['Utilisateur introuvable.']];
        }

        // 3. Vérification mot de passe
        if (!password_verify($currentPassword, $user->getPasswordHash())) {
            return ['success' => false, 'errors' => ['Mot de passe incorrect.']];
        }

        // 4. Vérification unicité email (Ancien vs Nouveau & Déjà pris)
        $oldEmail = $user->getEmail();
        if (strtolower($oldEmail) === strtolower($newEmail)) {
            return ['success' => false, 'errors' => ['La nouvelle adresse est identique à l’ancienne.']];
        }

        if ($this->userRepo->findByEmail($newEmail)) {
            return ['success' => false, 'errors' => ['Cette adresse email est déjà utilisée.']];
        }

        // 5. Logique Métier : Token & Mise à jour
        $token = bin2hex(random_bytes(32));

        $updated = $this->userRepo->updateEmail($userId, $newEmail);

        if (!$updated) {
            return ['success' => false, 'errors' => ['Erreur technique lors de la mise à jour.']];
        }

        // 6. Envoi des Emails (Logique déplacée ici ou dans un Service de Notification)

        // Email de validation au NOUVEAU
        Mailer::sendEmailVerification($newEmail, $user->getPrenom(), $token);

        // Notifications de sécurité
        $this->sendSecurityNotifications($oldEmail, $newEmail, $user->getPrenom());

        return [
            'success' => true,
            'message' => 'Adresse mise à jour. Vérifiez votre email.',
            'newEmail' => $newEmail // Pour mettre à jour la session dans le contrôleur
        ];
    }

    private function sendSecurityNotifications(string $oldEmail, string $newEmail, string $userName): void
    {

        $subjectOld = "Modification de votre adresse email - DashMed";
        $messageOld = "Bonjour $userName,\n\nVotre adresse email a été modifiée.\nSi ce n'est pas vous, contactez le support.";
        mail($oldEmail, $subjectOld, $messageOld);

        $subjectNew = "Confirmation de votre nouvelle adresse email - DashMed";
        $messageNew = "Bonjour $userName,\n\nVotre adresse email a été modifiée : $newEmail\nSi ce n'est pas vous, contactez le support.";
        mail($newEmail, $subjectNew, $messageNew);
    }
}
