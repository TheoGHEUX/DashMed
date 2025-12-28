<?php

namespace Tests\Unit\Core;

use Core\Database;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests unitaires pour la classe Database
 */
class DatabaseTest extends TestCase
{
    /**
     * Test que la classe Database est finale
     */
    #[Test]
    public function databaseClassIsFinal(): void
    {
        $reflection = new \ReflectionClass(Database::class);
        $this->assertTrue($reflection->isFinal());
    }

    /**
     * Test que la méthode getConnection existe et est statique
     */
    #[Test]
    public function getConnectionMethodIsStatic(): void
    {
        $reflection = new \ReflectionMethod(Database::class, 'getConnection');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
    }

    /**
     * Test que getConnection retourne un type PDO
     */
    #[Test]
    public function getConnectionReturnsPdoType(): void
    {
        $reflection = new \ReflectionMethod(Database::class, 'getConnection');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('PDO', $returnType->getName());
    }

    /**
     * Test que la propriété $pdo est privée et statique
     */
    #[Test]
    public function pdoPropertyIsPrivateAndStatic(): void
    {
        $reflection = new \ReflectionClass(Database::class);
        $property = $reflection->getProperty('pdo');

        $this->assertTrue($property->isPrivate());
        $this->assertTrue($property->isStatic());
    }

    /**
     * Test du namespace correct
     */
    #[Test]
    public function databaseClassHasCorrectNamespace(): void
    {
        $reflection = new \ReflectionClass(Database::class);
        $this->assertEquals('Core', $reflection->getNamespaceName());
    }
}
