<?php

namespace Tests\Unit\ValueObjects;

use App\Exceptions\ValidationException;
use App\ValueObjects\Password;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests unitaires pour le mot de passe
 */
class PasswordTest extends TestCase
{
    /**
     * Test de succès avec un mot de passe fort
     */
    #[Test]
    public function strongPasswordIsAccepted(): void
    {
        // Contient 8+ char, majuscule, minuscule, chiffre
        $password = new Password('StrongPass123!');
        
        $this->assertInstanceOf(Password::class, $password);
    }

    /**
     * Test les règles de validation du mot de passe
     */
    #[Test]
    #[DataProvider('invalidPasswordProvider')]
    public function invalidPasswordsThrowException(string $invalidPassword, string $expectedErrorMsg): void
    {
        try {
            new Password($invalidPassword);
            $this->fail('Une ValidationException était attendue.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('password', $e->getErrors());
            $this->assertEquals($expectedErrorMsg, $e->getErrors()['password']);
        }
    }

    public static function invalidPasswordProvider(): array
    {
        return [
            'trop_court' => [
                'Short1!', 
                'Le mot de passe doit contenir au moins 8 caractères.'
            ],
            'sans_majuscule' => [
                'nouppercase123!', 
                'Le mot de passe doit contenir au moins une majuscule.'
            ],
            'sans_minuscule' => [
                'NOLOWERCASE123!', 
                'Le mot de passe doit contenir au moins une minuscule.'
            ],
            'sans_chiffre' => [
                'NoNumbersHere!', 
                'Le mot de passe doit contenir au moins un chiffre.'
            ],
        ];
    }

    /**
     * Test que la méthode hash() retourne bien un hash bcrypt valide
     */
    #[Test]
    public function hashReturnsValidBcryptString(): void
    {
        $password = new Password('ValidPass123');
        $hash = $password->hash();

        // Le hash bcrypt typique commence par $2y$
        $this->assertStringStartsWith('$2y$', $hash);
        $this->assertNotEquals('ValidPass123', $hash);
    }

    /**
     * Test que la méthode verify() fonctionne correctement
     */
    #[Test]
    public function verifyWorksCorrectlyWithHash(): void
    {
        $password = new Password('SecretPass123');
        
        // Simule un vrai hash généré préalablement (ou généré via hash())
        $hash = $password->hash();

        $this->assertTrue($password->verify($hash));
        
        // Vérifie qu'un faux hash échoue
        $fakeHash = password_hash('WrongPass123', PASSWORD_DEFAULT);
        $this->assertFalse($password->verify($fakeHash));
    }
}
