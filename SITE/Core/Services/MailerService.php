<?php

declare(strict_types=1);

namespace Core\Services;

use App\Interfaces\IMailer;

/**
 * Service d'envoi d'emails implémentant IMailer
 */
final class MailerService implements IMailer
{
    private const FROM_EMAIL = 'dashmed-site@alwaysdata.net';

    public function send(string $to, string $subject, string $templateName, array $vars = []): bool
    {
        $htmlBody = $this->renderTemplate($templateName, $vars);

        $headers = [
            'From: DashMed <' . self::FROM_EMAIL . '>',
            'Reply-To: ' . self::FROM_EMAIL,
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8'
        ];
        $headersStr = implode("\r\n", $headers);

        $sent = @mail($to, $subject, $htmlBody, $headersStr);

        if (!$sent) {
            $this->saveToDisk($to, $subject, $htmlBody);
            return false;
        }
        return true;
    }

    private function renderTemplate(string $templateName, array $vars = []): string
    {
        $file = dirname(__DIR__, 2) . '/App/Views/emails/' . $templateName . '.php';
        if (!file_exists($file)) return '';
        extract($vars);
        ob_start();
        include $file;
        return ob_get_clean();
    }

    private function saveToDisk(string $to, string $subject, string $body): void
    {
        $dir = dirname(__DIR__, 3) . '/storage/mails';
        if (!is_dir($dir)) { mkdir($dir, 0755, true); }
        $filename = $dir . '/mail_' . date('Ymd_His') . '_' . uniqid() . '.eml';
        $content = "To: $to\r\nSubject: $subject\r\n\r\n" . $body;
        file_put_contents($filename, $content);
    }
}