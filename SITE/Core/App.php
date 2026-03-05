<?php

namespace Core;

use Core\Container;
use Models\Repositories\UserRepository;
use Core\Interfaces\UserRepositoryInterface;

/**
 * Classe principale de l'application
 *
 * Initialise le conteneur et configure les services.
 */
class App
{
    private static Container $container;

    /**
     * Démarre l'application et configure l'injection de dépendances.
     */
    public static function init(): void
    {
        self::$container = new Container();

        // --- ENREGISTREMENT DES DÉPENDANCES ---

        // Liaison Interface -> Implémentation
        self::$container->bind(UserRepositoryInterface::class, UserRepository::class);

        // Tu pourras ajouter d'autres bindings ici plus tard (PatientRepository, etc.)
    }

    /**
     * Récupère l'instance unique du conteneur.
     */
    public static function getContainer(): Container
    {
        return self::$container;
    }
}
