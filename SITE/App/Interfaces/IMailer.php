<?php

declare(strict_types=1);

namespace App\Interfaces;

interface IMailer
{
    /**
     * Envoie un email en utilisant un template HTML situé dans App/Views/
     */
    public function send(string $to, string $subject, string $templateName, array $data = []): bool;
}