<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;
use Core\Security\RateLimiter;
use Core\Services\MailerService;
use App\Models\Doctor\Repositories\DoctorReadRepository;
use App\Models\Doctor\Repositories\DoctorWriteRepository;
use App\Models\Doctor\Repositories\DoctorVerificationRepository;
use App\Models\Doctor\Validators\DoctorValidator;
use App\Models\Doctor\UseCases\Authentication\RegisterDoctor;

final class RegisterController extends AbstractController
{
    private RegisterDoctor $useCase;

    // Liste des spécialités pour l'affichage dans la vue
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
        $validator = new DoctorValidator();
        $mailer = new MailerService();

        $this->useCase = new RegisterDoctor(
            $readRepo,
            $writeRepo,
            $verifyRepo,
            $validator,
            $mailer
        );
    }

    /**
     * Affiche le formulaire d'inscription.
     */
    public function show(): void
    {
        $this->render('Authentication/register', [
            'errors' => [],
            'success' => '',
            'old' => [],
            'specialites' => self::SPECIALITES
        ]);
    }

    /**
     * Traite la soumission du formulaire d'inscription.
     */
    public function register(): void
    {
        $this->startSession();

        if (RateLimiter::isBlocked('register_attempts', 3, 3600)) {
            $this->render('Authentication/register', [
                'errors' => ['Trop de tentatives d\'inscription. Veuillez réessayer dans 1 heure.'],
                'old' => $_POST,
                'specialites' => self::SPECIALITES
            ]);
            return;
        }

        if (!$this->validateCsrf()) {
            RateLimiter::recordAttempt('register_attempts');

            $this->render('Authentication/register', [
                'errors' => ['Session expirée, veuillez recharger la page.'],
                'old' => $_POST,
                'specialites' => self::SPECIALITES
            ]);
            return;
        }


        $data = [
            'prenom' => trim($this->getPost('name') ?? ''),
            'nom' => trim($this->getPost('last_name') ?? ''),
            'email' => trim($this->getPost('email') ?? ''),
            'password' => $this->getPost('password') ?? '',
            'confirm' => $this->getPost('password_confirm') ?? '',
            'sexe' => $this->getPost('sexe') ?? '',
            'specialite' => $this->getPost('specialite') ?? ''
        ];

        try {
            $result = $this->useCase->execute($data);

            if ($result['success']) {
                RateLimiter::clear('register_attempts');

                $msg = 'Compte créé avec succès ! Un lien de vérification a été envoyé à ' . htmlspecialchars($data['email']);
                if (isset($result['warning'])) {
                    $msg .= ' (' . $result['warning'] . ')';
                }

                $this->render('Authentication/register', [
                    'errors' => [],
                    'success' => $msg,
                    'old' => [], // On vide les champs
                    'specialites' => self::SPECIALITES
                ]);
            } else {
                RateLimiter::recordAttempt('register_attempts');

                // On s'assure que 'errors' est bien un tableau
                $errors = $result['errors'] ?? [$result['error'] ?? 'Une erreur est survenue.'];

                $this->render('Authentication/register', [
                    'errors' => $errors,
                    'old' => $data,
                    'specialites' => self::SPECIALITES
                ]);
            }
        } catch (\Exception $e) {
            RateLimiter::recordAttempt('register_attempts');

            $this->render('Authentication/register', [
                'errors' => ['Erreur technique : ' . $e->getMessage()],
                'old' => $data,
                'specialites' => self::SPECIALITES
            ]);
        }
    }
}