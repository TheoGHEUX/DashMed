<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;
use App\Models\Doctor\Factories\DoctorUseCaseFactory;
use App\Models\Doctor\Enums\Specialite;

/**
 * Contrôleur d'inscription
 * Gère l'affichage du formulaire et le traitement de l'inscription
 */
final class RegisterController extends AbstractController
{
    public function show(): void
    {
        $this->startSession();
        $csrf_token = \Core\Csrf::token();

        $this->render('Authentication/register', [
            'csrf_token' => $csrf_token,
            'errors' => [],
            'success' => '',
            'old' => [],
            'specialites' => Specialite::all()
        ]);
    }

    public function register(): void
    {
        $this->startSession();

        $posted = $_POST['csrf_token'] ?? '';
        $session = $_SESSION['csrf_token'] ?? '';
        if (empty($posted) || empty($session) || !hash_equals($session, $posted)) {
            $this->render('Authentication/register', [
                'errors' => ["Session expirée. Veuillez recharger la page et réessayer."],
                'old' => [],
                'specialites' => Specialite::all(),
                'csrf_token' => \Core\Csrf::token()
            ]);
            return;
        }

        $data = [
            'prenom'     => trim($this->getPost('prenom')),
            'nom'        => trim($this->getPost('nom')),
            'email'      => trim($this->getPost('email')),
            'password'   => $this->getPost('password'),
            'confirm'    => $this->getPost('password_confirm'),
            'specialite' => $this->getPost('specialite'),
            'sexe'       => $this->getPost('sexe')
        ];

        $useCase = DoctorUseCaseFactory::createRegisterDoctor();
        $result = $useCase->execute($data);

        if ($result['success']) {
            $this->render('Authentication/register', [
                'success' => "Compte créé ! Un lien de vérification a été envoyé à " . htmlspecialchars($data['email']),
                'errors' => [],
                'old' => [],
                'specialites' => Specialite::all(),
                'csrf_token' => \Core\Csrf::token()
            ]);
        } else {
            $this->render('Authentication/register', [
                'errors' => $result['errors'],
                'old' => $data,
                'specialites' => Specialite::all(),
                'csrf_token' => \Core\Csrf::token()
            ]);
        }
    }
}