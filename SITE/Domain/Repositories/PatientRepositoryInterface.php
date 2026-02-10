<?php

namespace Domain\Repositories;

interface PatientRepositoryInterface
{
    public function findById(int $id): ?array;

    public function getPatientsForDoctor(int $doctorId): array;

    public function findByIdForDoctor(int $patientId, int $medId): ?array;

    public function getChartDataForDoctor(int $medId, int $patientId, string $typeMesure, int $limit = 50): ?array;

    public function getMesures(int $patientId): array;

    public function getValeursMesure(int $mesureId, ?int $limit = null): array;

    public function getDernieresValeurs(int $patientId): array;

    public function getChartData(int $patientId, string $typeMesure, int $limit = 50): ?array;

    public function getFirstPatientIdForDoctor(int $medId): ?int;

    public function getSeuilByStatus(int $patientId, string $typeMesure, string $statut, bool $majorant): ?float;
}
