<?php

/**
 * Bootstrap pour les tests PHPUnit
 */

// Charger l'autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Définir l'environnement de test
define('TESTING', true);

// Démarrer la session pour les tests qui en ont besoin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
