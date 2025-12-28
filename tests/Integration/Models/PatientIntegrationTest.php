<?php

namespace Tests\Integration\Models;

use Models\Patient;
use Core\Database;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests d'intégration pour la classe Patient
 *
 * Ces tests nécessitent une connexion à la base de données de test.
 *
 * @group integration
 * @group database
 */
class PatientIntegrationTest extends TestCase
{
    /**
     * Test de récupération d'un patient inexistant
     */
    #[Test]
    public function findByIdReturnsNullForNonExistentPatient(): void
    {
        $this->markTestSkipped('Test d\'intégration - nécessite une base de données configurée');

        $patient = Patient::findById(999999999);
        $this->assertNull($patient);
    }

    /**
     * Test de récupération des mesures pour un patient inexistant
     */
    #[Test]
    public function getMesuresReturnsEmptyArrayForNonExistentPatient(): void
    {
        $this->markTestSkipped('Test d\'intégration - nécessite une base de données configurée');

        $mesures = Patient::getMesures(999999999);

        $this->assertIsArray($mesures);
        $this->assertEmpty($mesures);
    }

    /**
     * Test de récupération des valeurs pour une mesure inexistante
     */
    #[Test]
    public function getValeursMesureReturnsEmptyArrayForNonExistentMesure(): void
    {
        $this->markTestSkipped('Test d\'intégration - nécessite une base de données configurée');

        $valeurs = Patient::getValeursMesure(999999999);

        $this->assertIsArray($valeurs);
        $this->assertEmpty($valeurs);
    }

    /**
     * Test de récupération des dernières valeurs pour un patient inexistant
     */
    #[Test]
    public function getDernieresValeursReturnsEmptyArrayForNonExistentPatient(): void
    {
        $this->markTestSkipped('Test d\'intégration - nécessite une base de données configurée');

        $valeurs = Patient::getDernieresValeurs(999999999);

        $this->assertIsArray($valeurs);
        $this->assertEmpty($valeurs);
    }

    /**
     * Test de getChartData pour un patient/type inexistant
     */
    #[Test]
    public function getChartDataReturnsNullForNonExistentData(): void
    {
        $this->markTestSkipped('Test d\'intégration - nécessite une base de données configurée');

        $data = Patient::getChartData(999999999, 'inexistent_type');
        $this->assertNull($data);
    }

    /**
     * Test de la structure de retour de findById
     */
    #[Test]
    public function findByIdReturnsCorrectStructure(): void
    {
        $this->markTestSkipped(
            'Test d\'intégration - nécessite une base de données configurée avec des données de test'
        );

        // Ce test suppose qu'il existe au moins un patient avec pt_id = 1
        $patient = Patient::findById(1);

        if ($patient !== null) {
            $this->assertArrayHasKey('pt_id', $patient);
            $this->assertArrayHasKey('prenom', $patient);
            $this->assertArrayHasKey('nom', $patient);
            $this->assertArrayHasKey('email', $patient);
            $this->assertArrayHasKey('sexe', $patient);
            $this->assertArrayHasKey('groupe_sanguin', $patient);
            $this->assertArrayHasKey('date_naissance', $patient);
            $this->assertArrayHasKey('telephone', $patient);
            $this->assertArrayHasKey('ville', $patient);
            $this->assertArrayHasKey('code_postal', $patient);
            $this->assertArrayHasKey('adresse', $patient);
        }
    }

    /**
     * Test de getValeursMesure avec limite
     */
    #[Test]
    public function getValeursMesureRespectsLimit(): void
    {
        $this->markTestSkipped('Test d\'intégration - nécessite une base de données configurée');

        // Ce test vérifie que la limite est respectée
        $limit = 5;
        $valeurs = Patient::getValeursMesure(1, $limit);

        $this->assertIsArray($valeurs);
        $this->assertLessThanOrEqual($limit, count($valeurs));
    }
}
