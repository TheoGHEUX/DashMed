<?php

namespace Models\Entities;

/**
 * Entité Patient
 *
 * Représente un patient inscrit dans l'application.
 *
 * Cette classe sert à stocker toutes les informations d'un patient (nom, prénom, adresse, etc.)
 * pour les utiliser facilement dans le reste du code.
 *
 * @package Models\Entities
 */
class Patient
{
    private int $id;
    private string $nom;
    private string $prenom;
    private ?string $email;
    private ?string $sexe;
    private ?string $groupeSanguin;
    private ?string $dateNaissance;
    private ?string $telephone;
    private ?string $adresse;
    private ?string $codePostal;
    private ?string $ville;

    /**
     * Constructeur : Crée un patient à partir de données brutes.
     *
     * Prend un tableau de données
     * et remplit automatiquement les propriétés de l'objet.
     *
     * Si une info est manquante, elle est laissée vide ou nulle.
     *
     * @param array $data Tableau contenant les infos du patient
     */
    public function __construct(array $data)
    {
        $this->id = (int) ($data['pt_id'] ?? 0);
        $this->nom = $data['nom'] ?? '';
        $this->prenom = $data['prenom'] ?? '';
        $this->email = $data['email'] ?? null;
        $this->sexe = $data['sexe'] ?? null;
        $this->groupeSanguin = $data['groupe_sanguin'] ?? null;
        $this->dateNaissance = $data['date_naissance'] ?? null;
        $this->telephone = $data['telephone'] ?? null;
        $this->adresse = $data['adresse'] ?? null;
        $this->codePostal = $data['code_postal'] ?? null;
        $this->ville = $data['ville'] ?? null;
    }

    // --- Méthodes pour récupérer les infos (Getters) ---

    /**
     * Récupère l'identifiant unique du patient.
     *
     * @return int L'ID du patient
     */
    public function getId(): int { return $this->id; }

    /**
     * Récupère le nom de famille.
     *
     * @return string Le nom
     */
    public function getNom(): string { return $this->nom; }

    /**
     * Récupère le prénom.
     *
     * @return string Le prénom
     */
    public function getPrenom(): string { return $this->prenom; }

    /**
     * Récupère l'adresse email (peut être vide).
     *
     * @return string|null L'email ou null
     */
    public function getEmail(): ?string { return $this->email; }

    /**
     * Convertit l'objet Patient en un simple tableau.
     *
     * Transforme toutes les infos de l'objet en un format tableau
     *
     * @return array Tableau des données du patient
     */
    public function toArray(): array
    {
        return [
            'pt_id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'sexe' => $this->sexe,
            'groupe_sanguin' => $this->groupeSanguin,
            'date_naissance' => $this->dateNaissance,
            'telephone' => $this->telephone,
            'adresse' => $this->adresse,
            'code_postal' => $this->codePostal,
            'ville' => $this->ville
        ];
    }
}