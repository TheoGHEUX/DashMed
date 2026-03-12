<?php

namespace App\Interfaces;

/**
 * Interface pour l'envoi d'emails dans l'application.
 */
interface IMailer
{
    /**
     * Envoie un email à un destinataire avec un template et des données.
     */
    public function send(string $to, string $subject, string $templateName, array $data = []): bool;
}
