<?php

namespace App\Interfaces;

/**
 * Interface pour le service d’envoi d’e-mails.
 *
 * Définit la méthode que tout service d’envoi d’e-mails doit implémenter dans l’application.
 */
interface IMailer
{
    /**
     * Envoie un e-mail à un destinataire donné, avec un sujet et un template.
     */
    public function send(string $to, string $subject, string $templateName, array $data = []): bool;
}