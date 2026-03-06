<?php

namespace Domain\UseCases\Auth;

use Core\Interfaces\UserRepositoryInterface;
use Core\Mailer;

/**
 * Cas d'utilisation : Changement d'adresse email
 *
 * Gère la logique métier complexe : vérification mot de passe, unicité,
 * mise à jour, génération de token et notifications de sécurité.
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
     * @return array Résultat ['success' => bool, 'message' => string, 'errors' => array, 'newEmail' => string|null]
     */
    public function execute(array $data): array
    {
        $errors = [];

        // 1. Extraction et Nettoyage
        $userId = (int)($data['userId'] ?? 0);
        $currentPassword = (string)($data['current_password'] ?? '');
        $newEmail = trim((string)($data['new_email'] ?? ''));
        $confirmEmail = trim((string)($data['new_email_confirm'] ?? ''));

        // 2. Validations de format
        if ($newEmail !== $confirmEmail) {
            $errors[] = 'Les adresses email ne correspondent pas.';
        }
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse email invalide.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // 3. Récupération de l'utilisateur
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return ['success' => false, 'errors' => ['Utilisateur introuvable.']];
        }

        // 4. Vérification du mot de passe actuel (Sécurité critique)
        if (!password_verify($currentPassword, $user->getPasswordHash())) {
            return ['success' => false, 'errors' => ['Mot de passe incorrect.']];
        }

        // 5. Vérification unicité email
        $oldEmail = $user->getEmail();

        // Est-ce le même mail ?
        if (strtolower($oldEmail) === strtolower($newEmail)) {
            return ['success' => false, 'errors' => ['La nouvelle adresse est identique à l’ancienne.']];
        }

        // Est-il déjà pris ?
        if ($this->userRepo->emailExists($newEmail)) {
            return ['success' => false, 'errors' => ['Cette adresse email est déjà utilisée par un autre compte.']];
        }


        //Mise à jour de l'email
        $updated = $this->userRepo->updateEmail($userId, $newEmail);

        if (!$updated) {
            return ['success' => false, 'errors' => ['Erreur technique lors de la mise à jour.']];
        }

        //Génération et sauvegarde du token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $this->userRepo->setVerificationToken($newEmail, $token, $expires);

        // Email de validation au nouveau mail
        $mailSent = Mailer::sendEmailVerification($newEmail, $user->getPrenom(), $token);

        // Notifications de sécurité (Ancien et Nouveau mail)
        $this->sendSecurityNotifications($oldEmail, $newEmail, $user->getPrenom());

        if ($mailSent) {
            return [
                'success' => true,
                'message' => 'Adresse mise à jour. Un lien de vérification a été envoyé à votre nouvelle adresse.',
                'newEmail' => $newEmail,
                'errors' => []
            ];
        } else {
            return [
                'success' => true, // On considère que c'est un succès partiel (email changé mais mail non parti)
                'message' => 'Adresse changée, mais erreur d\'envoi du mail. Contactez le support.',
                'newEmail' => $newEmail,
                'errors' => []
            ];
        }
    }

    /**
     * Envoie des notifications de sécurité aux deux adresses.
     */
    private function sendSecurityNotifications(string $oldEmail, string $newEmail, string $userName): void
    {
        // Notification à l'ANCIENNE adresse (Alerte de sécurité)
        $subjectOld = "Alerte de sécurité - Modification de votre email - DashMed";
        $messageOld = "Bonjour $userName,\n\n"
            . "Votre adresse email de connexion vient d'être modifiée pour : $newEmail.\n"
            . "Si vous n'êtes pas à l'origine de cette action, contactez IMMÉDIATEMENT le support.\n\n"
            . "L'équipe DashMed";

        mail($oldEmail, $subjectOld, $messageOld);

        // Notification à la NOUVELLE adresse (Confirmation simple)
        $subjectNew = "Confirmation de changement d'email - DashMed";
        $messageNew = "Bonjour $userName,\n\n"
            . "Votre adresse email a bien été modifiée.\n"
            . "N'oubliez pas de valider ce compte via le lien reçu dans l'autre email.\n\n"
            . "L'équipe DashMed";

        mail($newEmail, $subjectNew, $messageNew);
    }
}