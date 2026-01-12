<?php

namespace Tests\Unit\Core;

use Core\Csrf;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests unitaires pour la classe Csrf
 */
class CsrfTest extends TestCase
{
    /**
     * Nettoyage de la session avant chaque test
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Nettoyer les variables de session CSRF
        unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
    }

    /**
     * Test que token() génère un jeton
     */
    #[Test]
    public function tokenGeneratesNewToken(): void
    {
        $token = Csrf::token();

        $this->assertNotEmpty($token);
        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token)); // 32 bytes = 64 caractères hex
    }

    /**
     * Test que le jeton est stocké en session
     */
    #[Test]
    public function tokenIsStoredInSession(): void
    {
        $token = Csrf::token();

        $this->assertArrayHasKey('csrf_token', $_SESSION);
        $this->assertArrayHasKey('csrf_token_time', $_SESSION);
        $this->assertEquals($token, $_SESSION['csrf_token']);
    }

    /**
     * Test que token() retourne le même jeton si déjà existant
     */
    #[Test]
    public function tokenReturnsSameTokenIfExists(): void
    {
        $token1 = Csrf::token();
        $token2 = Csrf::token();

        $this->assertEquals($token1, $token2);
    }

    /**
     * Test que validate() accepte un jeton valide
     */
    #[Test]
    public function validateAcceptsValidToken(): void
    {
        $token = Csrf::token();
        $result = Csrf::validate($token);

        $this->assertTrue($result);
    }

    /**
     * Test que validate() rejette un jeton invalide
     */
    #[Test]
    public function validateRejectsInvalidToken(): void
    {
        Csrf::token(); // Générer un jeton valide
        $result = Csrf::validate('invalid_token_123456');

        $this->assertFalse($result);
    }

    /**
     * Test que validate() rejette un jeton vide
     */
    #[Test]
    public function validateRejectsEmptyToken(): void
    {
        Csrf::token();
        $result = Csrf::validate('');

        $this->assertFalse($result);
    }

    /**
     * Test que validate() consomme le jeton (une seule utilisation)
     */
    #[Test]
    public function validateConsumesToken(): void
    {
        $token = Csrf::token();

        // Première validation : succès
        $this->assertTrue(Csrf::validate($token));

        // Le jeton a été consommé, donc la session est vidée
        $this->assertArrayNotHasKey('csrf_token', $_SESSION);
        $this->assertArrayNotHasKey('csrf_token_time', $_SESSION);
    }

    /**
     * Test que validate() rejette un jeton expiré
     */
    #[Test]
    public function validateRejectsExpiredToken(): void
    {
        $token = Csrf::token();

        // Simuler un jeton créé il y a plus de 2 heures
        $_SESSION['csrf_token_time'] = time() - 7201;

        $result = Csrf::validate($token);

        $this->assertFalse($result);
    }

    /**
     * Test que validate() accepte un jeton non expiré
     */
    #[Test]
    public function validateAcceptsNonExpiredToken(): void
    {
        $token = Csrf::token();

        // Simuler un jeton créé il y a 1 heure (dans la limite de 2h)
        $_SESSION['csrf_token_time'] = time() - 3600;

        $result = Csrf::validate($token);

        $this->assertTrue($result);
    }

    /**
     * Test avec un TTL personnalisé
     */
    #[Test]
    public function validateWithCustomTtl(): void
    {
        $token = Csrf::token();

        // Jeton créé il y a 30 minutes
        $_SESSION['csrf_token_time'] = time() - 1800;

        // Avec TTL de 1 heure (3600s), devrait être valide
        $this->assertTrue(Csrf::validate($token, 3600));
    }

    /**
     * Test avec un TTL personnalisé expiré
     */
    #[Test]
    public function validateWithCustomTtlExpired(): void
    {
        $token = Csrf::token();

        // Jeton créé il y a 30 minutes
        $_SESSION['csrf_token_time'] = time() - 1800;

        // Avec TTL de 15 minutes (900s), devrait être expiré
        $result = Csrf::validate($token, 900);

        $this->assertFalse($result);
    }

    /**
     * Test que le jeton est bien hexadécimal
     */
    #[Test]
    public function tokenIsHexadecimal(): void
    {
        $token = Csrf::token();

        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/i', $token);
    }

    /**
     * Test que les jetons générés sont uniques
     */
    #[Test]
    public function tokensAreUnique(): void
    {
        $tokens = [];

        for ($i = 0; $i < 10; $i++) {
            // Nettoyer la session pour générer un nouveau jeton
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
            $tokens[] = Csrf::token();
        }

        // Tous les jetons devraient être uniques
        $uniqueTokens = array_unique($tokens);
        $this->assertCount(10, $uniqueTokens);
    }

    /**
     * Test que validate() retourne false si aucun jeton n'existe en session
     */
    #[Test]
    public function validateReturnsFalseWithNoSessionToken(): void
    {
        // Pas de jeton en session
        $result = Csrf::validate('some_random_token');

        $this->assertFalse($result);
    }

    /**
     * Test de la classe Csrf est finale
     */
    #[Test]
    public function csrfClassIsFinal(): void
    {
        $reflection = new \ReflectionClass(Csrf::class);
        $this->assertTrue($reflection->isFinal());
    }
}
