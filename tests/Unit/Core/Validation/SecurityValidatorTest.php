<?php

namespace Tests\Unit\Core\Validation;

use Core\Validation\SecurityValidator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests unitaires pour la classe utilitaire SecurityValidator
 */
class SecurityValidatorTest extends TestCase
{
    /**
     * Test la validation d'email de base (similaire à Email ValueObject mais avec des messages différents)
     */
    #[Test]
    public function validateEmailReturnsNullForValidEmail(): void
    {
        $this->assertNull(SecurityValidator::validateEmail('patient@dashmed.fr'));
        $this->assertNull(SecurityValidator::validateEmail('doctor.name@hospital.com'));
    }

    /**
     * Test le rejet des emails formellement invalides
     */
    #[Test]
    public function validateEmailRejectsInvalidFormats(): void
    {
        $this->assertEquals(
            "L'adresse email est invalide.", 
            SecurityValidator::validateEmail('plainaddress')
        );
        
        $this->assertEquals(
            "L'adresse email est invalide.", 
            SecurityValidator::validateEmail('@no-local-part.com')
        );
    }

    /**
     * Test le rejet spécifique des emails sans extension de domaine (ex: localhost)
     */
    #[Test]
    public function validateEmailRejectsLocalhostDomains(): void
    {
        // Valide techniquement pour certains cas, mais rejeté souvent par filter_var ou notre regex
        $this->assertEquals(
            "L'adresse email est invalide.", 
            SecurityValidator::validateEmail('user@localhost')
        );
    }

    /**
     * Test que validatePassword sans erreurs retourne un tableau vide
     */
    #[Test]
    public function validatePasswordReturnsEmptyArrayForANSSIPassword(): void
    {
        // 12 chars, Maj, Min, Chiffre, Spécial => OK ANSSI
        $errors = SecurityValidator::validatePassword('SuperPass123!');
        $this->assertIsArray($errors);
        $this->assertEmpty($errors);
    }

    /**
     * Test que validatePassword cumule correctement les erreurs
     */
    #[Test]
    public function validatePasswordAccumulatesMultipleErrors(): void
    {
        // Manque: majuscule, chiffre, spécial, et trop court (<12)
        $errors = SecurityValidator::validatePassword('short');
        
        $this->assertCount(4, $errors);
        $this->assertContains("Le mot de passe doit faire au moins 12 caractères.", $errors);
        $this->assertContains("Le mot de passe doit contenir au moins une majuscule.", $errors);
        $this->assertContains("Le mot de passe doit contenir au moins un chiffre.", $errors);
        $this->assertContains("Le mot de passe doit contenir au moins un caractère spécial (!, @, #, $, etc.).", $errors);
    }

    /**
     * Test la validation de la confirmation du mot de passe
     */
    #[Test]
    public function validatePasswordChecksConfirmationMatch(): void
    {
        $validPass = 'SuperPass123!';
        
        // Cas succès
        $errorsMatch = SecurityValidator::validatePassword($validPass, $validPass);
        $this->assertEmpty($errorsMatch);

        // Cas erreur (différents)
        $errorsMismatch = SecurityValidator::validatePassword($validPass, 'DifferentPass123!');
        $this->assertCount(1, $errorsMismatch);
        $this->assertContains("La confirmation du mot de passe ne correspond pas.", $errorsMismatch);
    }
}
