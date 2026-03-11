<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Models\Patient\Entities\Patient;

/**
 * Tests unitaires pour l'entité Patient (DDD)
 *
 * Ces tests couvrent l'entité Patient (constructeur + getters).
 */
class PatientTest extends TestCase
{
    /**
     * Vérifie que l'entité Patient se construit correctement depuis un tableau de données
     * et que tous ses getters retournent les bonnes valeurs.
     */
    #[Test]
    public function patientEntityConstructsCorrectlyFromArray(): void
    {
        $data = [
            'pt_id'          => 10,
            'nom'            => 'Durand',
            'prenom'         => 'Marc',
            'email'          => 'marc.durand@example.com',
            'sexe'           => 'M',
            'groupe_sanguin' => 'A+',
            'date_naissance' => '1985-06-15',
            'telephone'      => '0612345678',
            'adresse'        => '12 rue de la Paix',
            'code_postal'    => '75001',
            'ville'          => 'Paris',
        ];

        $patient = new Patient($data);

        $this->assertEquals(10, $patient->getId());
        $this->assertEquals('Durand', $patient->getNom());
        $this->assertEquals('Marc', $patient->getPrenom());
        $this->assertEquals('marc.durand@example.com', $patient->getEmail());
        $this->assertEquals('M', $patient->getSexe());
        $this->assertEquals('A+', $patient->getGroupeSanguin());
        $this->assertEquals('1985-06-15', $patient->getDateNaissance());
        $this->assertEquals('0612345678', $patient->getTelephone());
        $this->assertEquals('12 rue de la Paix', $patient->getAdresse());
        $this->assertEquals('75001', $patient->getCodePostal());
        $this->assertEquals('Paris', $patient->getVille());
    }

    /**
     * Vérifie que les champs optionnels absents sont null (pas une erreur PHP).
     */
    #[Test]
    public function patientEntityHandlesMissingOptionalFields(): void
    {
        $patient = new Patient([
            'pt_id'  => 1,
            'nom'    => 'Test',
            'prenom' => 'Patient',
        ]);

        $this->assertNull($patient->getEmail());
        $this->assertNull($patient->getSexe());
        $this->assertNull($patient->getGroupeSanguin());
        $this->assertNull($patient->getDateNaissance());
        $this->assertNull($patient->getTelephone());
        $this->assertNull($patient->getAdresse());
        $this->assertNull($patient->getCodePostal());
        $this->assertNull($patient->getVille());
    }

    /**
     * Vérifie que toArray() retourne un tableau cohérent avec les données transmises.
     *
     * Les clés attendues doivent correspondre aux colonnes SQL.
     */
    #[Test]
    public function patientToArrayReturnsExpectedKeys(): void
    {
        $patient = new Patient([
            'pt_id'  => 7,
            'nom'    => 'Legrand',
            'prenom' => 'Claire',
        ]);

        $array = $patient->toArray();

        $this->assertArrayHasKey('pt_id', $array);
        $this->assertArrayHasKey('nom', $array);
        $this->assertArrayHasKey('prenom', $array);
        $this->assertArrayHasKey('email', $array);
        $this->assertArrayHasKey('sexe', $array);
        $this->assertArrayHasKey('groupe_sanguin', $array);
        $this->assertArrayHasKey('date_naissance', $array);
        $this->assertArrayHasKey('telephone', $array);
        $this->assertArrayHasKey('adresse', $array);
        $this->assertArrayHasKey('code_postal', $array);
        $this->assertArrayHasKey('ville', $array);

        $this->assertEquals(7, $array['pt_id']);
        $this->assertEquals('Legrand', $array['nom']);
        $this->assertEquals('Claire', $array['prenom']);
    }

    /**
     * Vérifie que toArray() et le constructeur sont cohérents (aller-retour).
     */
    #[Test]
    public function patientToArrayRoundTrip(): void
    {
        $data = [
            'pt_id'          => 3,
            'nom'            => 'Bernard',
            'prenom'         => 'Luc',
            'email'          => 'luc@test.com',
            'sexe'           => 'M',
            'groupe_sanguin' => 'O-',
            'date_naissance' => '1990-01-01',
            'telephone'      => null,
            'adresse'        => null,
            'code_postal'    => null,
            'ville'          => null,
        ];

        $patient1 = new Patient($data);
        $patient2 = new Patient($patient1->toArray());

        $this->assertEquals($patient1->getId(), $patient2->getId());
        $this->assertEquals($patient1->getNom(), $patient2->getNom());
        $this->assertEquals($patient1->getEmail(), $patient2->getEmail());
        $this->assertEquals($patient1->getGroupeSanguin(), $patient2->getGroupeSanguin());
    }

    /**
     * Vérifie que getId() retourne bien un int (cast depuis la BDD qui retourne des strings).
     *
     * L'entité doit forcer le type int pour pt_id.
     */
    #[Test]
    public function patientIdIsAlwaysInteger(): void
    {
        $patient = new Patient(['pt_id' => '25', 'nom' => 'Test', 'prenom' => 'Cast']);

        $this->assertIsInt($patient->getId());
        $this->assertEquals(25, $patient->getId());
    }

    /**
     * Vérifie que le namespace de l'entité Patient est correct.
     */
    #[Test]
    public function patientClassHasCorrectNamespace(): void
    {
        $reflection = new \ReflectionClass(Patient::class);
        $this->assertEquals('App\\Models\\Patient\\Entities', $reflection->getNamespaceName());
    }

    /**
     * Vérifie que la classe Patient expose bien les méthodes getters attendues.
     *
     * Si une méthode est renommée ou supprimée, ce test échoue immédiatement.
     */
    #[Test]
    #[DataProvider('getterMethodsProvider')]
    public function patientEntityExposesExpectedGetter(string $methodName): void
    {
        $this->assertTrue(
            method_exists(Patient::class, $methodName),
            "La méthode {$methodName}() n'existe pas sur l'entité Patient"
        );
    }

    public static function getterMethodsProvider(): array
    {
        return [
            ['getId'],
            ['getNom'],
            ['getPrenom'],
            ['getEmail'],
            ['getSexe'],
            ['getGroupeSanguin'],
            ['getDateNaissance'],
            ['getTelephone'],
            ['getAdresse'],
            ['getCodePostal'],
            ['getVille'],
            ['toArray'],
        ];
    }
}
