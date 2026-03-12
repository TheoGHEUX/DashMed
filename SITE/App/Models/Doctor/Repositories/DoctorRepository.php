<?php

namespace App\Models\Doctor\Repositories;

use Core\Database;
use App\Models\Doctor\Entities\Doctor;
use App\Models\Doctor\Interfaces\IDoctorRepository;
use PDO;

/**
 * Repository pour la gestion des médecins.
 *
 * Un repository est une classe qui fait le lien entre le code métier et la base de données.
 * Il centralise les requêtes SQL et permet de manipuler les données de façon structurée.
 */
final class DoctorRepository implements IDoctorRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Recherche un médecin par email (insensible à la casse).
     */
    public function findByEmail(string $email): ?Doctor
    {
        $stmt = $this->db->prepare('SELECT * FROM medecin WHERE LOWER(email) = LOWER(?) LIMIT 1');
        $stmt->execute([trim($email)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new Doctor($row) : null;
    }

    /**
     * Recherche un médecin par identifiant unique.
     */
    public function findById(int $id): ?Doctor
    {
        $stmt = $this->db->prepare('SELECT * FROM medecin WHERE med_id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new Doctor($row) : null;
    }

    /**
     * Vérifie si un email existe déjà en base.
     */
    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM medecin WHERE LOWER(email) = LOWER(?) LIMIT 1');
        $stmt->execute([trim($email)]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Crée un nouveau médecin en base de données.
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO medecin (prenom, nom, email, mdp, sexe, specialite, compte_actif, email_verified, date_creation)
             VALUES (?, ?, ?, ?, ?, ?, 1, 0, NOW())'
        );
        return $stmt->execute([
            $data['prenom'],
            $data['nom'],
            strtolower(trim($data['email'])),
            $data['password_hash'],
            $data['sexe'] ?? null,
            $data['specialite'] ?? null
        ]);
    }

    /**
     * Met à jour le mot de passe d'un médecin.
     */
    public function updatePassword(int $id, string $hash): bool
    {
        $stmt = $this->db->prepare('UPDATE medecin SET mdp = ?, date_derniere_maj = NOW() WHERE med_id = ?');
        return $stmt->execute([$hash, $id]);
    }

    /**
     * Met à jour l'adresse email d'un médecin.
     */
    public function updateEmail(int $id, string $email): bool
    {
        $stmt = $this->db->prepare('UPDATE medecin SET email = ?, email_verified = 0 WHERE med_id = ?');
        return $stmt->execute([$email, $id]);
    }

    public function activateByEmail(string $email): bool
    {
        $stmt = $this->db->prepare('UPDATE medecin SET compte_actif = 1 WHERE LOWER(email) = LOWER(?)');
        return $stmt->execute([trim($email)]);
    }
}
