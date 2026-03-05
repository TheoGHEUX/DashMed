<?php

namespace Core;

use ReflectionClass;
use ReflectionNamedType;
use Exception;

/**
 * Conteneur d'Injection de Dépendances (DI Container)
 *
 * Responsabilité :
 * - Stocker les associations Interface <-> Classe Concrète (Bindings)
 * - Créer automatiquement les objets et leurs dépendances (Auto-wiring)
 */
class Container
{
    /**
     * Stocke les instances uniques (Singleton)
     */
    private array $instances = [];

    /**
     * Stocke les recettes de création (Bindings)
     */
    private array $bindings = [];

    /**
     * Associe une interface à une classe concrète.
     * Ex: bind(UserRepositoryInterface::class, UserRepository::class)
     */
    public function bind(string $interface, string $implementation): void
    {
        $this->bindings[$interface] = $implementation;
    }

    /**
     * Résout (instancie) une classe en injectant ses dépendances.
     *
     * @param string $class Le nom de la classe ou de l'interface à résoudre
     * @return object L'instance prête à l'emploi
     * @throws Exception Si la résolution échoue
     */
    public function get(string $class): object
    {
        // 1. Si on a déjà l'instance en cache (Singleton), on la retourne
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        // 2. Si on demande une Interface, on regarde quelle classe concrète utiliser
        if (isset($this->bindings[$class])) {
            $class = $this->bindings[$class];
        }

        // 3. Réflexion : On analyse la classe demandée
        try {
            $reflector = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new Exception("Classe introuvable : $class");
        }

        if (!$reflector->isInstantiable()) {
            throw new Exception("La classe [$class] n'est pas instanciable.");
        }

        // 4. On regarde le constructeur
        $constructor = $reflector->getConstructor();

        // S'il n'y a pas de constructeur, on instancie direct
        if (is_null($constructor)) {
            return new $class;
        }

        // 5. S'il y a un constructeur, on résout ses paramètres (les dépendances)
        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                // Cas simple non géré ici (paramètres primitifs comme string, int)
                // Pour l'instant on suppose que tout est une classe/interface
                continue;
            }

            // RÉCURSIVITÉ : On demande au conteneur de créer la dépendance
            $dependencies[] = $this->get($type->getName());
        }

        // 6. On crée l'instance avec toutes ses dépendances
        $instance = $reflector->newInstanceArgs($dependencies);

        // On sauvegarde l'instance (optionnel, comportement Singleton simple)
        $this->instances[$class] = $instance;

        return $instance;
    }
}
