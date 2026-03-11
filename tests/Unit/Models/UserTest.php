<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Doctor\Entities\Doctor;
use App\Models\Doctor\Repositories\DoctorRepository;

/**
 * Tests unitaires pour la classe Doctor
 *
 * L'ancienne classe Models\User a été remplacée par l'entité Doctor
 * et son repository DoctorRepository dans la nouvelle architecture DDD.
 *
 * Ces tests vérifient l'entité et les signatures de méthodes du repository
 * sans connexion réelle à la base de données.
 */
class UserTest extends TestCase
{
    /**
     * Vérifie que l'entité Doctor se construit correctement depuis un tableau de données
     * et que tous ses getters retournent les bonnes valeurs.
     */
    #[Test]
    public function doctorEntityConstructsCorrectlyFromArray(): void
    {
        $data = [
            'med_id'                      => 42,
            'prenom'                      => 'Sophie',
            'nom'                         => 'Martin',
            'email'                       => 'sophie.martin@hopital.fr',
            'mdp'                         => 'hashed_password',
            'sexe'                        => 'F',
            'specialite'                  => 'Cardiologie',
            'email_verified'              => 1,
            'email_verification_token'    => null,
            'email_verification_expires'  => null,
        ];

        $doctor = new Doctor($data);

        $this->assertEquals(42, $doctor->getId());
        $this->assertEquals('Sophie', $doctor->getPrenom());
        $this->assertEquals('Martin', $doctor->getNom());
        $this->assertEquals('sophie.martin@hopital.fr', $doctor->getEmail());
        $this->assertEquals('hashed_password', $doctor->getPasswordHash());
        $this->assertEquals('F', $doctor->getSexe());
        $this->assertEquals('Cardiologie', $doctor->getSpecialite());
        $this->assertTrue($doctor->isEmailVerified());
        $this->assertNull($doctor->getVerificationToken());
        $this->assertNull($doctor->getVerificationExpires());
    }

    /**
     * Vérifie que des champs optionnels absents du tableau valent null (et non une erreur).
     *
     * Pourquoi : la BDD peut retourner des lignes sans tous les champs.
     * L'entité doit rester robuste aux clés manquantes.
     */
    #[Test]
    public function doctorEntityHandlesMissingOptionalFields(): void
    {
        $data = [
            'med_id'  => 1,
            'prenom'  => 'Jean',
            'nom'     => 'Dupont',
            'email'   => 'jean@example.com',
            'mdp'     => 'hash',
        ];

        $doctor = new Doctor($data);

        $this->assertNull($doctor->getSexe());
        $this->assertNull($doctor->getSpecialite());
        $this->assertFalse($doctor->isEmailVerified());
        $this->assertNull($doctor->getVerificationToken());
    }

    /**
     * Vérifie que toSessionArray() retourne les bonnes clés pour stocker le médecin en session.
     *
     * Ce test documente le contrat entre l'entité et la session.
     */
    #[Test]
    public function doctorToSessionArrayContainsExpectedKeys(): void
    {
        $doctor = new Doctor([
            'med_id'         => 5,
            'prenom'         => 'Alice',
            'nom'            => 'Dupont',
            'email'          => 'alice@test.com',
            'mdp'            => 'hash',
            'email_verified' => 1,
        ]);

        $session = $doctor->toSessionArray();

        $this->assertArrayHasKey('id', $session);
        $this->assertArrayHasKey('email', $session);
        $this->assertArrayHasKey('name', $session);
        $this->assertArrayHasKey('last_name', $session);
        $this->assertArrayHasKey('email_verified', $session);
        $this->assertEquals(5, $session['id']);
        $this->assertEquals('alice@test.com', $session['email']);
    }

    /**
     * Vérifie que le token de vérification d'email est bien accessible via le getter.
     */
    #[Test]
    public function doctorEntityExposesVerificationToken(): void
    {
        $doctor = new Doctor([
            'med_id'                     => 1,
            'prenom'                     => 'Bob',
            'nom'                        => 'Test',
            'email'                      => 'bob@test.com',
            'mdp'                        => 'hash',
            'email_verification_token'   => 'abc123token',
            'email_verification_expires' => '2099-12-31 23:59:59',
        ]);

        $this->assertEquals('abc123token', $doctor->getVerificationToken());
        $this->assertEquals('2099-12-31 23:59:59', $doctor->getVerificationExpires());
    }

    /**
     * Vérifie que DoctorRepository::emailExists() est bien une méthode publique
     * retournant un bool — correspondant à l'ancienne User::emailExists().
     */
    #[Test]
    public function emailExistsMethodHasCorrectSignature(): void
    {
        $reflection = new \ReflectionMethod(DoctorRepository::class, 'emailExists');

        $this->assertTrue($reflection->isPublic());
        $returnType = $reflection->getReturnType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertEquals('bool', $returnType->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $paramType = $params[0]->getType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $paramType);
        $this->assertEquals('string', $paramType->getName());
    }

    /**
     * Vérifie la signature de findByEmail() : méthode publique retournant ?Doctor.
     */
    #[Test]
    public function findByEmailMethodHasCorrectSignature(): void
    {
        $reflection = new \ReflectionMethod(DoctorRepository::class, 'findByEmail');

        $this->assertTrue($reflection->isPublic());
        $this->assertCount(1, $reflection->getParameters());

        $returnType = $reflection->getReturnType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertTrue($returnType->allowsNull());
    }

    /**
     * Vérifie la signature de findById() : méthode publique, paramètre int, retourne ?Doctor.
     */
    #[Test]
    public function findByIdMethodHasCorrectSignature(): void
    {
        $reflection = new \ReflectionMethod(DoctorRepository::class, 'findById');

        $this->assertTrue($reflection->isPublic());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $paramType = $params[0]->getType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $paramType);
        $this->assertEquals('int', $paramType->getName());

        $this->assertTrue($reflection->getReturnType()?->allowsNull());
    }

    /**
     * Vérifie la signature de updatePassword() : méthode publique, 2 paramètres, retourne bool.
     */
    #[Test]
    public function updatePasswordMethodHasCorrectSignature(): void
    {
        $reflection = new \ReflectionMethod(DoctorRepository::class, 'updatePassword');

        $this->assertTrue($reflection->isPublic());
        $returnType = $reflection->getReturnType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertEquals('bool', $returnType->getName());
        $this->assertCount(2, $reflection->getParameters());
    }

    /**
     * Vérifie la signature de updateEmail() : méthode publique, 2 paramètres, retourne bool.
     */
    #[Test]
    public function updateEmailMethodHasCorrectSignature(): void
    {
        $reflection = new \ReflectionMethod(DoctorRepository::class, 'updateEmail');

        $this->assertTrue($reflection->isPublic());
        $returnType = $reflection->getReturnType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertEquals('bool', $returnType->getName());
        $this->assertCount(2, $reflection->getParameters());
    }

    /**
     * Vérifie que le namespace de l'entité Doctor est correct.
     */
    #[Test]
    public function doctorClassHasCorrectNamespace(): void
    {
        $reflection = new \ReflectionClass(Doctor::class);
        $this->assertEquals('App\\Models\\Doctor\\Entities', $reflection->getNamespaceName());
    }

    /**
     * Vérifie la validation du format d'email (logique PHP native, sans dépendance).
     */
    #[Test]
    public function emailFormatValidation(): void
    {
        $this->assertNotFalse(filter_var('sophie@hopital.fr', FILTER_VALIDATE_EMAIL));
        $this->assertNotFalse(filter_var('user+tag@example.com', FILTER_VALIDATE_EMAIL));
        $this->assertFalse(filter_var('pas-un-email', FILTER_VALIDATE_EMAIL));
        $this->assertFalse(filter_var('test@', FILTER_VALIDATE_EMAIL));
        $this->assertFalse(filter_var('test @example.com', FILTER_VALIDATE_EMAIL));
    }
}
