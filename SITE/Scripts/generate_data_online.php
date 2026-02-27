<?php

use Core\Database;

function generatePatientData(int $patientId): void
{
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("SELECT * FROM mesures WHERE pt_id = ?");
    $stmt->execute([$patientId]);
    $mesures = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($mesures as $mesure) {

        $id_mesure = $mesure['id_mesure'];

        $stmt_last = $pdo->prepare("
            SELECT valeur 
            FROM valeurs_mesures 
            WHERE id_mesure = ?
            ORDER BY date_mesure DESC, heure_mesure DESC
            LIMIT 1
        ");
        $stmt_last->execute([$id_mesure]);
        $last = $stmt_last->fetchColumn();

        $valeur_base = $last !== false ? (float)$last : 70;

        $variation = mt_rand(-5,5)/10;
        $valeur = round($valeur_base + $variation, 1);

        $insert = $pdo->prepare("
            INSERT INTO valeurs_mesures 
            (valeur, date_mesure, heure_mesure, id_mesure)
            VALUES (?, CURDATE(), CURTIME(), ?)
        ");

        $insert->execute([$valeur, $id_mesure]);
    }
}