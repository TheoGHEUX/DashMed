<?php

declare(strict_types=1);

namespace Core\Services;

use App\Interfaces\IMailer;

class MailerService implements IMailer
{
    private const FROM_EMAIL = 'dashmed-site@alwaysdata.net';

    public function send(string $to, string $subject, string $templateName, array $data = []): bool
    {
        // 1. Charger la vue HTML
        $htmlBody = $this->renderView($templateName, $data);
        if (!$htmlBody) return false;

        // 2. Préparer les headers
        $headers = [
            'From: DashMed <' . self::FROM_EMAIL . '>',
            'Reply-To: ' . self::FROM_EMAIL,
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
        ];
        $headersStr = implode("\r\n", $headers);

        // 3. Envoyer (Compatible Windows/Linux)
        if (PHP_OS_FAMILY === 'Windows') {
            $sent = @mail($to, $subject, $htmlBody, $headersStr);
        } else {
            $sent = @mail($to, $subject, $htmlBody, $headersStr, '-f ' . self::FROM_EMAIL);
        }

        // 4. Sauvegarde locale si échec (Fallback)
        if (!$sent) {
            $this->saveToStorage($to, $subject, $htmlBody);
        }

        return true;
    }

    private function renderView(string $path, array $data): ?string
    {
        // On remonte de Core/Services vers App/Views
        $file = dirname(__DIR__, 2) . '/App/Views/' . $path . '.php';

        if (!file_exists($file)) {
            error_log("[MAILER] Vue introuvable : $file");
            return null;
        }

        extract($data);
        ob_start();
        include $file;
        return ob_get_clean();
    }

    private function saveToStorage(string $to, string $subject, string $body): void
    {
        $dir = dirname(__DIR__, 2) . '/storage/mails';
        if (!is_dir($dir)) { @mkdir($dir, 0755, true); }

        $filename = $dir . '/mail_' . date('Ymd_His') . '_' . uniqid() . '.html';
        $content = "<!-- To: $to | Subject: $subject -->\n" . $body;

        file_put_contents($filename, $content);
    }
}