<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use Core\Controller\AbstractController;
use Core\Services\MailerService;
use App\Models\Doctor\Repositories\DoctorReadRepository;
use App\Models\Doctor\Repositories\DoctorWriteRepository;
use App\Models\Doctor\Repositories\DoctorVerificationRepository;
use App\Models\Doctor\UseCases\Authentication\RegisterDoctor;

final class RegisterController extends AbstractController
{
    private RegisterDoctor $useCase;

    private const SPECIALITES = [
        'Addictologie',
        'Algologie',
        'Allergologie',
        'Anesthésie-Réanimation',
        'Cancérologie',
        'Cardio-vasculaire HTA',
        'Chirurgie',
        'Dermatologie',
        'Diabétologie-Endocrinologie',
        'Génétique',
        'Gériatrie',
        'Gynécologie-Obstétrique',
        'Hématologie',
        'Hépato-gastro-entérologie',
        'Imagerie médicale',
        'Immunologie',
        'Infectiologie',
        'Médecine du sport',
        'Médecine du travail',
        'Médecine générale',
        'Médecine légale',
        'Médecine physique et de réadaptation',
        'Néphrologie',
        'Neurologie',
        'Nutrition',
        'Ophtalmologie',
        'ORL',
        'Pédiatrie',
        'Pneumologie',
        'Psychiatrie',
        'Radiologie',
        'Rhumatologie',
        'Sexologie',
        'Toxicologie',
        'Urologie',
    ];

    public function __construct()
    {
        $readRepo = new DoctorReadRepository();
        $writeRepo = new DoctorWriteRepository();
        $verifyRepo = new DoctorVerificationRepository();
        $mailer = new MailerService();

        $this->useCase = new RegisterDoctor(
            $readRepo,
            $writeRepo,
            $verifyRepo,
            $mailer
        );
    }

    public function show(): void
    {
        $this->render('auth/register', [
            'errors' => [],
            'success' => '',
            'old' => [],
            'specialites' => self::SPECIALITES
        ]);
    }

    public function submit(): void
    {
        $this->startSession();

        if (!$this->validateCsrf()) {
            $this->render('auth/register', ['errors' => ['Session expirée.']]);
            return;
        }

        $data = [
            'prenom' => $this->getPost('name'),
            'nom' => $this->getPost('last_name'),
            'email' => $this->getPost('email'),
            'password' => $this->getPost('password'),
            'sexe' => $this->getPost('sexe'),
            'specialite' => $this->getPost('specialite')
        ];

        $confirm = $this->getPost('password_confirm');

        if ($data['password'] !== $confirm) {
            $this->render('auth/register', [
                'errors' => ['Les mots de passe ne correspondent pas.'],
                'old' => $_POST, // On garde les champs remplis
                'specialites' => self::SPECIALITES
            ]);
            return;
        }

        $result = $this->useCase->execute($data);

        if ($result['success']) {
            $msg = 'Compte créé ! Vérifiez vos emails pour l\'activer.';
            if (isset($result['warning'])) {
                $msg .= ' (' . $result['warning'] . ')';
            }

            $this->render('auth/register', [
                'errors' => [],
                'success' => $msg,
                'old' => [],
                'specialites' => self::SPECIALITES
            ]);
        } else {
            $this->render('auth/register', [
                'errors' => [$result['error']],
                'old' => $_POST,
                'specialites' => self::SPECIALITES
            ]);
        }
    }
}