<?php

namespace App\Interfaces;

interface IMailer
{
    public function send(string $to, string $subject, string $templateName, array $data = []): bool;
}
