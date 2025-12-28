<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests unitaires pour la classe Patient
 *
 * Ces tests utilisent des méthodes mockées pour éviter les dépendances à la base de données.
 */
class PatientTest extends TestCase
{
    /**
     * Test de la méthode normalizeValue avec des valeurs normales
     */
    #[Test]
    public function normalizeValueReturnsCorrectValueForNormalRange(): void
    {
        // Valeur au milieu de l'intervalle
        $result = \Models\Patient::normalizeValue(50.0, 0.0, 100.0);
        $this->assertEquals(0.5, $result);

        // Valeur au minimum
        $result = \Models\Patient::normalizeValue(0.0, 0.0, 100.0);
        $this->assertEquals(0.0, $result);

        // Valeur au maximum
        $result = \Models\Patient::normalizeValue(100.0, 0.0, 100.0);
        $this->assertEquals(1.0, $result);
    }

    /**
     * Test de la méthode normalizeValue avec min == max
     */
    #[Test]
    public function normalizeValueReturnsHalfWhenMinEqualsMax(): void
    {
        $result = \Models\Patient::normalizeValue(50.0, 50.0, 50.0);
        $this->assertEquals(0.5, $result);
    }

    /**
     * Test de la méthode normalizeValue avec des valeurs hors limites
     */
    #[Test]
    public function normalizeValueClampsBelowZero(): void
    {
        // Valeur en dessous du minimum
        $result = \Models\Patient::normalizeValue(-10.0, 0.0, 100.0);
        $this->assertEquals(0.0, $result);
    }

    /**
     * Test de la méthode normalizeValue avec des valeurs hors limites
     */
    #[Test]
    public function normalizeValueClampsAboveOne(): void
    {
        // Valeur au dessus du maximum
        $result = \Models\Patient::normalizeValue(150.0, 0.0, 100.0);
        $this->assertEquals(1.0, $result);
    }

    /**
     * Test de la méthode normalizeValue avec des valeurs décimales
     */
    #[Test]
    #[DataProvider('normalizeValueDataProvider')]
    public function normalizeValueWithVariousInputs(float $value, float $min, float $max, float $expected): void
    {
        $result = \Models\Patient::normalizeValue($value, $min, $max);
        $this->assertEqualsWithDelta($expected, $result, 0.0001);
    }

    /**
     * Provider de données pour le test normalizeValue
     */
    public static function normalizeValueDataProvider(): array
    {
        return [
            'middle_value' => [50.0, 0.0, 100.0, 0.5],
            'quarter_value' => [25.0, 0.0, 100.0, 0.25],
            'three_quarter_value' => [75.0, 0.0, 100.0, 0.75],
            'negative_range' => [0.0, -100.0, 100.0, 0.5],
            'decimal_values' => [36.5, 35.0, 42.0, 0.2143],
            'body_temperature_normal' => [37.0, 35.0, 42.0, 0.2857],
            'blood_pressure_systolic' => [120.0, 90.0, 180.0, 0.3333],
        ];
    }

    /**
     * Test de la méthode prepareChartValues
     */
    #[Test]
    public function prepareChartValuesReturnsNormalizedArray(): void
    {
        $valeurs = [
            ['valeur' => '100'],
            ['valeur' => '50'],
            ['valeur' => '0'],
            ['valeur' => '75'],
        ];

        $result = \Models\Patient::prepareChartValues($valeurs, 0.0, 100.0);

        $this->assertIsArray($result);
        $this->assertCount(4, $result);
        $this->assertEquals(1.0, $result[0]);
        $this->assertEquals(0.5, $result[1]);
        $this->assertEquals(0.0, $result[2]);
        $this->assertEquals(0.75, $result[3]);
    }

    /**
     * Test de la méthode prepareChartValues avec un tableau vide
     */
    #[Test]
    public function prepareChartValuesReturnsEmptyArrayForEmptyInput(): void
    {
        $result = \Models\Patient::prepareChartValues([], 0.0, 100.0);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test de la méthode prepareChartValues avec des valeurs de température corporelle
     */
    #[Test]
    public function prepareChartValuesWithBodyTemperature(): void
    {
        $valeurs = [
            ['valeur' => '36.5'],
            ['valeur' => '37.0'],
            ['valeur' => '38.5'],
            ['valeur' => '39.0'],
        ];

        $result = \Models\Patient::prepareChartValues($valeurs, 35.0, 42.0);

        $this->assertIsArray($result);
        $this->assertCount(4, $result);

        // Vérifier que toutes les valeurs sont comprises entre 0 et 1
        foreach ($result as $value) {
            $this->assertGreaterThanOrEqual(0.0, $value);
            $this->assertLessThanOrEqual(1.0, $value);
        }
    }
}
