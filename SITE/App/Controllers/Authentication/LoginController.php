<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;
use App\Models\Doctor\Repositories\DoctorReadRepository;
use App\Models\Doctor\UseCases\Authentication\LoginDoctor;

final class LoginController extends AbstractController
{
    private LoginDoctor $useCase;

    public function __construct()
    {
        $this->useCase = new LoginDoctor(new DoctorReadRepository());
    }

    public function show(): void
    {
        $this->render('auth/login', [
            'errors' => [],
            'success' => $_GET['reset'] ?? '',
            'old' => ['email' => '']
        ]);
    }

    public function submit(): void
    {
        $this->startSession();

        if (!$this->validateCsrf()) {
            $this->render('auth/login', ['errors' => ['Session expirée.']]);
            return;
        }

        try {
            $email = $this->getPost('email');
            $doctor = $this->useCase->execute($email, $this->getPost('password'));

            if ($doctor) {
                session_regenerate_id(true);
                $_SESSION['user'] = $doctor->toArray();
                $_SESSION['login_attempts'] = [];
                $this->redirect('/dashboard');
            } else {
                $this->render('auth/login', [
                    'errors' => ['Identifiants incorrects.'],
                    'old' => ['email' => $email]
                ]);
            }
        } catch (\Exception $e) {
            $this->render('auth/login', ['errors' => [$e->getMessage()]]);
        }
    }
}