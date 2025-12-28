<?php

namespace Tests\Integration\Models;

use Models\User;
use Core\Database;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;

/**
 * Tests d'intégration pour la classe User
 *
 * Ces tests nécessitent une connexion à la base de données de test.
 * Assurez-vous d'avoir configuré une base de données de test.
 *
 * @group integration
 * @group database
 */
class UserIntegrationTest extends TestCase
{
    private static string $testEmail = 'test_phpunit@example.com';
    private static ?int $createdUserId = null;

    /**
     * Configuration avant tous les tests
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Nettoyer l'utilisateur de test s'il existe
        try {
            $pdo = Database::getConnection();
            $st = $pdo->prepare('DELETE FROM MEDECIN WHERE email = ?');
            $st->execute([self::$testEmail]);
        } catch (\Exception $e) {
            // Ignorer les erreurs de connexion pour les environnements sans DB
        }
    }

    /**
     * Nettoyage après tous les tests
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        // Supprimer l'utilisateur de test
        try {
            $pdo = Database::getConnection();
            $st = $pdo->prepare('DELETE FROM MEDECIN WHERE email = ?');
            $st->execute([self::$testEmail]);
        } catch (\Exception $e) {
            // Ignorer
        }
    }

    /**
     * Test de création d'un utilisateur
     */
    #[Test]
    public function createUserSuccessfully(): void
    {
        $this->markTestSkipped('Test d\'intégration - nécessite une base de données configurée');

        $result = User::create(
            'Test',
            'User',
            self::$testEmail,
            password_hash('TestPassword123!', PASSWORD_DEFAULT),
            'M',
            'Généraliste'
        );

        $this->assertTrue($result);
    }

    /**
     * Test que l'email existe après création
     */
    #[Test]
    #[Depends('createUserSuccessfully')]
    public function emailExistsAfterCreation(): void
    {
        $this->markTestSkipped('Test d\'intégration - nécessite une base de données configurée');

        $result = User::emailExists(self::$testEmail);
        $this->assertTrue($result);
    }

    /**
     * Test de recherche par email
     */
    #[Test]
    #[Depends('createUserSuccessfully')]
    public function findByEmailReturnsUser(): void
    {
        $this->markTestSkipped('Test d\'intégration - nécessite une base de données configurée');

        $user = User::findByEmail(self::$testEmail);

        $this->assertNotNull($user);
        $this->assertIsArray($user);
        $this->assertEquals(self::$testEmail, $user['email']);
        $this->assertEquals('Test', $user['name']);
        $this->assertEquals('User', $user['last_name']);

        self::$createdUserId = $user['user_id'];
    }

    /**
     * Test de recherche par ID
     */
    #[Test]
    #[Depends('findByEmailReturnsUser')]
    public function findByIdReturnsUser(): void
    {
        $this->markTestSkipped('Test d\'intégration - nécessite une base de données configurée');

        if (self::$createdUserId === null) {
            $this->markTestSkipped('User ID non disponible');
        }

        $user = User::findById(self::$createdUserId);

        $this->assertNotNull($user);
        $this->assertEquals(self::$testEmail, $user['email']);
    }

    /**
     * Test de mise à jour du mot de passe
     */
    #[Test]
    #[Depends('findByIdReturnsUser')]
    public function updatePasswordSuccessfully(): void
    {
        $this->markTestSkipped('Test d\'intégration - nécessite une base de données configurée');

        if (self::$createdUserId === null) {
            $this->markTestSkipped('User ID non disponible');
        }

        $newHash = password_hash('NewPassword456!', PASSWORD_DEFAULT);
        $result = User::updatePassword(self::$createdUserId, $newHash);

        $this->assertTrue($result);

        // Vérifier que le mot de passe a été mis à jour
        $user = User::findById(self::$createdUserId);
        $this->assertTrue(password_verify('NewPassword456!', $user['password']));
    }

    /**
     * Test emailExists pour un email inexistant
     */
    #[Test]
    public function emailExistsReturnsFalseForNonExistentEmail(): void
    {
        $this->markTestSkipped('Test d\'intégration - nécessite une base de données configurée');

        $result = User::emailExists('nonexistent_' . time() . '@example.com');
        $this->assertFalse($result);
    }

    /**
     * Test findByEmail pour un email inexistant
     */
    #[Test]
    public function findByEmailReturnsNullForNonExistentEmail(): void
    {
        $this->markTestSkipped('Test d\'intégration - nécessite une base de données configurée');

        $user = User::findByEmail('nonexistent_' . time() . '@example.com');
        $this->assertNull($user);
    }

    /**
     * Test findById pour un ID inexistant
     */
    #[Test]
    public function findByIdReturnsNullForNonExistentId(): void
    {
        $this->markTestSkipped('Test d\'intégration - nécessite une base de données configurée');

        $user = User::findById(999999999);
        $this->assertNull($user);
    }
}
