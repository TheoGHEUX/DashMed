<?php

require_once __DIR__ . '/../Core/AutoLoader.php';
use Core\Database;

// --- Configuration ---
$nb_valeurs = 5;       // nombre de valeurs à générer
$interval_seconds = 3;  // intervalle entre chaque ajout en secondes

// Plages réalistes par type de mesure
$plages = [
    "Température corporelle" => [36.4, 37.8],
    "Tension artérielle" => [110, 140],
    "Fréquence cardiaque" => [55, 100],
    "Poids" => [45, 100],
    "Glycémie" => [4.5, 7.2],
    "Fréquence respiratoire" => [12, 20],
    "Saturation en oxygène" => [95, 100],
];

// Variations max réalistes pour chaque mesure
$deltas = [
    "Poids" => 0.5,
    "Température corporelle" => 0.1,
    "Tension artérielle" => 2,
    "Fréquence cardiaque" => 3,
    "Glycémie" => 0.2,
    "Fréquence respiratoire" => 1,
    "Saturation en oxygène" => 0.5,
];

// Connexion
$pdo = Database::getConnection();

// ID du patient
$patientId = 25;

// Récupérer les mesures du patient
$stmt = $pdo->prepare("SELECT * FROM mesures WHERE pt_id = ?");
$stmt->execute([$patientId]);
$mesures = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Récupérer la dernière valeur pour chaque mesure ---
$last_values = [];
foreach ($mesures as $mesure) {
    $stmt_last = $pdo->prepare(
        "SELECT valeur FROM valeurs_mesures WHERE id_mesure = ? ORDER BY date_mesure DESC, heure_mesure DESC LIMIT 1"
    );
    $stmt_last->execute([$mesure['id_mesure']]);
    $last = $stmt_last->fetchColumn();
    $last_values[$mesure['type_mesure']] = $last !== false ? (float)$last : null;
}

// --- Boucle pour générer $nb_valeurs valeurs ---
for ($i = 0; $i < $nb_valeurs; $i++) {
    $current_time = new DateTime();

    foreach ($mesures as $mesure) {
        $type = $mesure['type_mesure'];
        $id_mesure = $mesure['id_mesure'];
        [$min_val, $max_val] = $plages[$type];
        $delta = $deltas[$type];

        // Base pour la nouvelle valeur : dernière valeur si existante, sinon milieu de plage
        $valeur_base = $last_values[$type] ?? (($min_val + $max_val) / 2);

        // Nouvelle valeur avec petite variation aléatoire
        $variation = mt_rand(-$delta*10, $delta*10)/10;
        $valeur_base += $variation;

        // Limiter aux bornes réalistes
        $valeur_base = max(min($valeur_base, $max_val), $min_val);

        $valeur = round($valeur_base, 1);

        // Sauvegarder comme dernière valeur pour le prochain cycle
        $last_values[$type] = $valeur_base;

        $date_str = $current_time->format('Y-m-d');
        $heure_str = $current_time->format('H:i:s');

        // INSERT PDO
        $sql = "INSERT INTO valeurs_mesures (valeur, date_mesure, heure_mesure, id_mesure) VALUES (?, ?, ?, ?)";
        $stmt_insert = $pdo->prepare($sql);
        $stmt_insert->execute([$valeur, $date_str, $heure_str, $id_mesure]);

        echo "[" . $date_str . " " . $heure_str . "] $type = $valeur\n";
    }

    sleep($interval_seconds);
}

echo "✅ Génération terminée : $nb_valeurs valeurs par mesure pour pt_id = $patientId\n";
