<?php

declare(strict_types=1);

namespace App\Models\Doctor\Factories;

use App\Models\Doctor\Repositories\DoctorRepository;
use App\Models\Doctor\Repositories\DoctorVerificationRepository;
use App\Models\Doctor\Repositories\SecurityRepository;
use App\Models\Doctor\Validators\DoctorValidator;
use App\Models\Doctor\UseCases\Authentication\LoginDoctor;
use App\Models\Doctor\UseCases\Authentication\RegisterDoctor;
use App\Models\Doctor\UseCases\Authentication\VerifyEmail;
use App\Models\Doctor\UseCases\Security\ForgottenPassword;
use App\Models\Doctor\UseCases\Security\ResetPassword;
use App\Models\Doctor\UseCases\Profile\ChangePassword;
use App\Models\Doctor\UseCases\Profile\ChangeEmail;
use Core\Services\MailerService;

/**
 * Factory pour créer les Use Cases liés aux médecins
 * Centralise la configuration des dépendances
 */
final class DoctorUseCaseFactory
{
    private static ?DoctorRepository $doctorRepo = null;
    private static ?DoctorVerificationRepository $verificationRepo = null;
    private static ?SecurityRepository $securityRepo = null;
    private static ?DoctorValidator $validator = null;
    private static ?MailerService $mailer = null;

    // Repositories (Singleton pattern pour éviter multiples connexions)
    private static function getDoctorRepo(): DoctorRepository
    {
        if (self::$doctorRepo === null) {
            self::$doctorRepo = new DoctorRepository();
        }
        return self::$doctorRepo;
    }

    private static function getVerificationRepo(): DoctorVerificationRepository
    {
        if (self::$verificationRepo === null) {
            self::$verificationRepo = new DoctorVerificationRepository();
        }
        return self::$verificationRepo;
    }

    private static function getSecurityRepo(): SecurityRepository
    {
        if (self::$securityRepo === null) {
            self::$securityRepo = new SecurityRepository();
        }
        return self::$securityRepo;
    }

    // Services
    private static function getValidator(): DoctorValidator
    {
        if (self::$validator === null) {
            self::$validator = new DoctorValidator();
        }
        return self::$validator;
    }

    private static function getMailer(): MailerService
    {
        if (self::$mailer === null) {
            self::$mailer = new MailerService();
        }
        return self::$mailer;
    }

    // Use Cases
    public static function createLoginDoctor(): LoginDoctor
    {
        return new LoginDoctor(self::getDoctorRepo());
    }

    public static function createRegisterDoctor(): RegisterDoctor
    {
        return new RegisterDoctor(
            self::getDoctorRepo(),
            self::getVerificationRepo(),
            self::getValidator(),
            self::getMailer()
        );
    }

    public static function createVerifyEmail(): VerifyEmail
    {
        return new VerifyEmail(self::getVerificationRepo());
    }

    public static function createForgottenPassword(): ForgottenPassword
    {
        return new ForgottenPassword(
            self::getDoctorRepo(),
            self::getSecurityRepo(),
            self::getValidator(),
            self::getMailer()
        );
    }

    public static function createResetPassword(): ResetPassword
    {
        return new ResetPassword(
            self::getDoctorRepo(),
            self::getSecurityRepo(),
            self::getValidator()
        );
    }

    public static function createChangePassword(): ChangePassword
    {
        return new ChangePassword(
            self::getDoctorRepo(),
            self::getValidator()
        );
    }

    public static function createChangeEmail(): ChangeEmail
    {
        return new ChangeEmail(
            self::getDoctorRepo(),
            self::getVerificationRepo(),
            self::getValidator(),
            self::getMailer()
        );
    }
}
