<?php

namespace Tests\Unit\ValueObjects;

use App\Exceptions\ValidationException;
use App\ValueObjects\Email;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests unitaires pour l'email
 */
class EmailTest extends TestCase
{
    /**
     * Test qu'un email valide est bien accepté et converti en minuscule
     */
    #[Test]
    public function expectedValidEmailsAreAccepted(): void
    {
        $email1 = new Email('test@example.com');
        $this->assertEquals('test@example.com', $email1->getValue());

        // Doit supprimer les espaces et convertir en minuscules
        $email2 = new Email('  UsEr@eXAmple.COM  ');
        $this->assertEquals('user@example.com', $email2->getValue());
    }

    /**
     * Test que les emails invalides lèvent bien une ValidationException
     */
    #[Test]
    #[DataProvider('invalidEmailProvider')]
    public function invalidEmailsThrowValidationException(string $invalidEmail): void
    {
        try {
            new Email($invalidEmail);
            $this->fail('Une ValidationException était attendue.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->getErrors());
            $this->assertEquals('Format d\'email invalide.', $e->getErrors()['email']);
        }
    }

    public static function invalidEmailProvider(): array
    {
        return [
            ['not_an_email'],
            ['test@'],
            ['@example.com'],
            ['test@example'], // Bien que techniquement valide en RFC strict (localhost), souvent rejeté en web
            ['test @example.com'],
            [''],
        ];
    }

    /**
     * Test de la méthode magique __toString
     */
    #[Test]
    public function toStringReturnsEmailValue(): void
    {
        $email = new Email('contact@dashmed.fr');
        $this->assertEquals('contact@dashmed.fr', (string) $email);
    }

    /**
     * Test de la méthode equals pour la comparaison d'objets Email
     */
    #[Test]
    public function equalsReturnsTrueForSameEmails(): void
    {
        $email1 = new Email('test@dashmed.fr');
        $email2 = new Email('TEST@dashmed.fr'); // Sera normalisé
        $email3 = new Email('autre@dashmed.fr');

        $this->assertTrue($email1->equals($email2));
        $this->assertFalse($email1->equals($email3));
    }
}
