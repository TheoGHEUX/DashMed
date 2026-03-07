<?php
// public/test_arch.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ⚠️ CORRECTION DU CHEMIN : On remonte d'un niveau pour trouver Core
require_once __DIR__ . '/../Core/AutoLoader.php';

echo "<h1>🔍 Audit Architecture (Depuis /public)</h1>";

try {
    // 1. Test Entité
    $doc = new \Models\Doctor\Entities\Doctor(['nom' => 'House']);
    echo "✅ Entité Doctor : OK<br>";

    // 2. Test Repository
    // Note : Si la connexion BDD échoue, c'est pas grave pour ce test,
    // on veut juste voir si la classe est trouvée.
    try {
        $repo = new \Models\Doctor\Repositories\DoctorReadRepository();
        echo "✅ Repo DoctorRead : OK<br>";
    } catch (\PDOException $e) {
        echo "⚠️ Repo chargé mais pas de BDD (Normal) : " . $e->getMessage() . "<br>";
        // On crée un "faux" repo pour la suite du test si la BDD plante
        $repo = null;
    }

    // 3. Test Service
    $service = new \Models\Patient\Services\PatientSimilarityService();
    echo "✅ Service KNN : OK<br>";

    // 4. Test UseCase (Injection de dépendance)
    if ($repo) {
        $uc = new \Models\Doctor\UseCases\Authentication\LoginDoctor($repo);
        echo "✅ UseCase Login : OK (Tout est relié !)<br>";
    } else {
        echo "⚠️ Pas de test UseCase (car Repo BDD échoué)<br>";
    }

} catch (\Throwable $e) {
    echo "<h2 style='color:red'>❌ ERREUR FATALE :</h2>";
    echo "<b>Message :</b> " . $e->getMessage() . "<br>";
    echo "<b>Fichier :</b> " . $e->getFile() . " (Ligne " . $e->getLine() . ")<br>";

    // Astuce de debug : Afficher si l'autoloader a cherché au bon endroit
    echo "<br><i>Vérifie que le namespace dans ton fichier correspond bien au chemin du dossier !</i>";
}