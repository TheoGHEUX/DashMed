<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests unitaires pour la classe User
 *
 * Note: Les tests qui nécessitent une connexion à la base de données
 * sont placés dans les tests d'intégration.
 */
class UserTest extends TestCase
{
    /**
     * Test que la méthode emailExists retourne un booléen
     * Ce test vérifie le type de retour attendu
     */
    #[Test]
    public function emailExistsReturnsBool(): void
    {
        // Cette méthode nécessite une connexion DB
        // On vérifie juste que la méthode existe et a la bonne signature
        $this->assertTrue(method_exists(\Models\User::class, 'emailExists'));

        $reflection = new \ReflectionMethod(\Models\User::class, 'emailExists');
        $this->assertTrue($reflection->isStatic());
        $this->assertEquals('bool', $reflection->getReturnType()?->getName());
    }

    /**
     * Test que la méthode create existe avec les bons paramètres
     */
    #[Test]
    public function createMethodHasCorrectSignature(): void
    {
        $reflection = new \ReflectionMethod(\Models\User::class, 'create');

        $this->assertTrue($reflection->isStatic());
        $this->assertEquals('bool', $reflection->getReturnType()?->getName());
        $this->assertCount(6, $reflection->getParameters());

        $paramNames = array_map(fn($p) => $p->getName(), $reflection->getParameters());
        $this->assertEquals(['name', 'lastName', 'email', 'hash', 'sexe', 'specialite'], $paramNames);
    }

    /**
     * Test que la méthode findByEmail existe avec la bonne signature
     */
    #[Test]
    public function findByEmailMethodHasCorrectSignature(): void
    {
        $reflection = new \ReflectionMethod(\Models\User::class, 'findByEmail');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->getReturnType()?->allowsNull());
        $this->assertCount(1, $reflection->getParameters());
    }

    /**
     * Test que la méthode findById existe avec la bonne signature
     */
    #[Test]
    public function findByIdMethodHasCorrectSignature(): void
    {
        $reflection = new \ReflectionMethod(\Models\User::class, 'findById');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->getReturnType()?->allowsNull());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('int', $params[0]->getType()?->getName());
    }

    /**
     * Test que la méthode updatePassword existe avec la bonne signature
     */
    #[Test]
    public function updatePasswordMethodHasCorrectSignature(): void
    {
        $reflection = new \ReflectionMethod(\Models\User::class, 'updatePassword');

        $this->assertTrue($reflection->isStatic());
        $this->assertEquals('bool', $reflection->getReturnType()?->getName());
        $this->assertCount(2, $reflection->getParameters());
    }

    /**
     * Test que la méthode updateEmail existe avec la bonne signature
     */
    #[Test]
    public function updateEmailMethodHasCorrectSignature(): void
    {
        $reflection = new \ReflectionMethod(\Models\User::class, 'updateEmail');

        $this->assertTrue($reflection->isStatic());
        $this->assertEquals('bool', $reflection->getReturnType()?->getName());
        $this->assertCount(2, $reflection->getParameters());
    }

    /**
     * Test que la méthode generateEmailVerificationToken existe
     */
    #[Test]
    public function generateEmailVerificationTokenMethodExists(): void
    {
        $reflection = new \ReflectionMethod(\Models\User::class, 'generateEmailVerificationToken');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->getReturnType()?->allowsNull());
    }

    /**
     * Test que la méthode verifyEmailToken existe
     */
    #[Test]
    public function verifyEmailTokenMethodHasCorrectSignature(): void
    {
        $reflection = new \ReflectionMethod(\Models\User::class, 'verifyEmailToken');

        $this->assertTrue($reflection->isStatic());
        $this->assertEquals('bool', $reflection->getReturnType()?->getName());
    }

    /**
     * Test que la méthode findByVerificationToken existe
     */
    #[Test]
    public function findByVerificationTokenMethodExists(): void
    {
        $reflection = new \ReflectionMethod(\Models\User::class, 'findByVerificationToken');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->getReturnType()?->allowsNull());
    }

    /**
     * Test de validation d'email - formats valides
     */
    #[Test]
    #[DataProvider('validEmailProvider')]
    public function emailFormatValidation(string $email, bool $isValid): void
    {
        $result = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        $this->assertEquals($isValid, $result);
    }

    /**
     * Provider de données pour les tests d'email
     */
    public static function validEmailProvider(): array
    {
        return [
            'valid_email' => ['test@example.com', true],
            'valid_email_with_subdomain' => ['user@mail.example.com', true],
            'valid_email_with_plus' => ['user+tag@example.com', true],
            'invalid_no_at' => ['testexample.com', false],
            'invalid_no_domain' => ['test@', false],
            'invalid_spaces' => ['test @example.com', false],
            'invalid_double_at' => ['test@@example.com', false],
        ];
    }

    /**
     * Test que la classe User est finale
     */
    #[Test]
    public function userClassIsFinal(): void
    {
        $reflection = new \ReflectionClass(\Models\User::class);
        $this->assertTrue($reflection->isFinal());
    }

    /**
     * Test du namespace correct
     */
    #[Test]
    public function userClassHasCorrectNamespace(): void
    {
        $reflection = new \ReflectionClass(\Models\User::class);
        $this->assertEquals('Models', $reflection->getNamespaceName());
    }
}
