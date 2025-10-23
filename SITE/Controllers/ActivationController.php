<?php
namespace Controllers;

use Models\User;

final class ActivationController
{
    /**
     * Page d'activation de compte
     */
    public function activate(): void
    {
        $token = (string)($_GET['token'] ?? '');
        $errors = [];
        $success = '';
        $userName = '';

        if (empty($token)) {
            $errors[] = 'Lien d\'activation invalide. Le token est manquant.';
        } else {
            // Vérifier si le token est valide
            $medecin = User::checkActivationToken($token);

            if (!$medecin) {
                $errors[] = 'Ce lien d\'activation est invalide ou a expiré.';
                $errors[] = 'Les liens d\'activation sont valables 24 heures.';
                $errors[] = 'Si votre lien a expiré, veuillez vous réinscrire ou contacter le support.';
            } else {
                // Token valide, on active le compte
                if (User::activateAccount($token)) {
                    $userName = $medecin['prenom'];
                    $success = 'Félicitations ' . htmlspecialchars($userName) . ' ! 🎉<br><br>'
                        . 'Votre compte a été activé avec succès.<br>'
                        . 'Vous pouvez maintenant vous connecter et profiter de DashMed.';
                } else {
                    $errors[] = 'Erreur lors de l\'activation du compte.';
                    $errors[] = 'Veuillez réessayer ou contacter le support technique.';
                }
            }
        }

        require __DIR__ . '/../Views/auth/activation.php';
    }
}