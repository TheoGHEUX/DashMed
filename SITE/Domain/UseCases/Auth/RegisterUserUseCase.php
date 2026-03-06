<?php

namespace Domain\UseCases\Auth;

use Core\Interfaces\UserRepositoryInterface;
use Core\Mailer;

/**
 * Cas d'utilisation : Inscription d'un utilisateur
 *
 * Responsabilité :
 * - Valider les données d'entrée
 * - Vérifier l'unicité de l'email
 * - Hacher le mot de passe
 * - Créer l'utilisateur via le Repository
 * - Générer le token de validation
 * - Envoyer l'email de confirmation
 */
class RegisterUserUseCase
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Exécute la logique d'inscription.
     *
     * @param array $data Les données brutes du formulaire ($_POST)
     * @return array Résultat ['success' => bool, 'errors' => array, 'message' => string]
     */
    public function execute(array $data): array
    {
        $errors = [];

        // 1. Nettoyage des entrées
        $name = trim($data['name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        $email = trim($data['email'] ?? '');
        $sexe = trim($data['sexe'] ?? '');
        $specialite = trim($data['specialite'] ?? '');
        $password = $data['password'] ?? '';
        $confirm = $data['password_confirm'] ?? '';

        // 2. Validations
        if (empty($name) || empty($lastName)) {
            $errors[] = 'Nom et prénom obligatoires.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email invalide.';
        }

        if ($password !== $confirm) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }

        // Complexité mot de passe
        if (strlen($password) < 12
            || !preg_match('/[A-Z]/', $password)
            || !preg_match('/[a-z]/', $password)
            || !preg_match('/\d/', $password)
            || !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit faire 12 caractères min. avec Maj, Min, Chiffre et Caractère spécial.';
        }

        // Vérification unicité email via le Repository
        if (!$errors && $this->userRepository->emailExists($email)) {
            $errors[] = 'Cet email est déjà utilisé.';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors,
                'message' => ''
            ];
        }

        // 3. Création du compte
        $created = $this->userRepository->create([
            'prenom' => $name,
            'nom' => $lastName,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'sexe' => $sexe,
            'specialite' => $specialite
        ]);

        if ($created) {
            // 4. Gestion du token et envoi email
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

            $this->userRepository->setVerificationToken($email, $token, $expires);

            $mailSent = Mailer::sendEmailVerification($email, $name, $token);

            if ($mailSent) {
                return [
                    'success' => true,
                    'errors' => [],
                    'message' => "Compte créé ! Un lien de vérification a été envoyé à " . htmlspecialchars($email)
                ];
            } else {
                return [
                    'success' => true,
                    'errors' => [],
                    'message' => "Compte créé, mais l'envoi du mail a échoué. Contactez le support."
                ];
            }
        }

        return [
            'success' => false,
            'errors' => ["Erreur technique lors de la création du compte."],
            'message' => ''
        ];
    }
}
