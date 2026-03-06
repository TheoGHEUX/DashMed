<?php

namespace Core;

use Core\Container;

// ====================================================================
// --- IMPORTS DES INTERFACES ET CLASSES ---
// ====================================================================

// 1. Repositories (Accès Base de Données)
use Core\Interfaces\ChangePasswordUseCaseInterface;
use Core\Interfaces\UserRepositoryInterface;
use Models\Repositories\UserRepository;

use Core\Interfaces\PasswordResetRepositoryInterface;
use Models\Repositories\PasswordResetRepository;

// 2. Use Cases : Profil & Sécurité
use Domain\UseCases\Auth\ChangePasswordUseCase;

// 3. Use Cases : Vérification d'Email
use Domain\Interfaces\VerifyEmailUseCaseInterface;
use Domain\UseCases\Auth\VerifyEmailUseCase;

use Domain\Interfaces\ResendVerificationEmailUseCaseInterface;
use Domain\UseCases\Auth\ResendVerificationEmailUseCase;


/**
 * Classe principale de l'application.
 * C'est ici qu'on configure l'Injection de Dépendances.
 */
class App
{
    private static Container $container;

    /**
     * Initialise le conteneur et lie les Interfaces aux Implémentations.
     */
    public static function init(): void
    {
        self::$container = new Container();

        // ====================================================================
        // A. BINDING DES REPOSITORIES
        // ====================================================================

        // Gestion des utilisateurs (Médecins)
        self::$container->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );

        // Gestion des tokens "Mot de passe oublié"
        self::$container->bind(
            PasswordResetRepositoryInterface::class,
            PasswordResetRepository::class
        );


        // ====================================================================
        // B. BINDING DES USE CASES (LOGIQUE MÉTIER)
        // ====================================================================

        // --- Changement de Mot de passe ---
        self::$container->bind(
            ChangePasswordUseCaseInterface::class,
            ChangePasswordUseCase::class
        );

        // --- Vérification d'Email (Validation du lien) ---
        self::$container->bind(
            VerifyEmailUseCaseInterface::class,
            VerifyEmailUseCase::class
        );

        // --- Vérification d'Email (Renvoi du lien) ---
        self::$container->bind(
            ResendVerificationEmailUseCaseInterface::class,
            ResendVerificationEmailUseCase::class
        );


    }

    /**
     * Récupère l'instance unique du conteneur de services.
     */
    public static function getContainer(): Container
    {
        return self::$container;
    }
}