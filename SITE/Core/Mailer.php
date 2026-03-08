<?php

declare(strict_types=1);

namespace Core;

/**
 * Service d'envoi d'emails générique.
 *
 * Responsabilité unique : Envoyer un email HTML via la fonction mail() de PHP.
 * Ne contient aucune logique métier (pas de "sendWelcome" ou "sendReset").
 *
 * @package Core
 */
final class Mailer
{
    private const DEFAULT_FROM = 'no-reply@dashmed.fr';
    private const DEBUG_FOLDER = __DIR__ . '/../../storage/mails';

    /**
     * Envoie un email HTML.
     *
     * @param string $to      Email du destinataire
     * @param string $subject Sujet de l'email
     * @param string $htmlBody Contenu HTML complet
     * @param string|null $from Email de l'expéditeur (optionnel)
     * @return bool Succès de l'opération
     */
    public static function send(string $to, string $subject, string $htmlBody, ?string $from = null): bool
    {
        $fromEmail = $from ?? (getenv('MAIL_FROM') ?: self::DEFAULT_FROM);

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: DashMed <' . $fromEmail . '>',
            'Reply-To: ' . $fromEmail,
            'X-Mailer: PHP/' . phpversion()
        ];

        // Envoi réel
        $success = mail($to, $subject, $htmlBody, implode("\r\n", $headers));

        // En mode DEBUG (local), on sauvegarde aussi dans un fichier pour vérifier sans serveur SMTP
        if ((getenv('APP_DEBUG') === '1') || !$success) {
            self::logMailToFile($to, $subject, $htmlBody);
        }

        return $success;
    }

    /**
     * Charge un template HTML et injecte les variables.
     *
     * @param string $viewPath Chemin relatif depuis Views/ (ex: 'emails/welcome')
     * @param array  $data     Données à injecter ['name' => 'Jean']
     * @return string Le HTML généré
     */
    public static function renderTemplate(string $viewPath, array $data = []): string
    {
        $file = dirname(__DIR__) . '/Views/' . $viewPath . '.php';

        if (!file_exists($file)) {
            throw new \RuntimeException("Template email introuvable : $file");
        }

        extract($data);
        ob_start();
        include $file;
        return ob_get_clean() ?: '';
    }

    private static function logMailToFile(string $to, string $subject, string $body): void
    {
        if (!is_dir(self::DEBUG_FOLDER)) {
            mkdir(self::DEBUG_FOLDER, 0777, true);
        }
        $filename = self::DEBUG_FOLDER . '/mail_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.html';
        $content = "<!-- TO: $to | SUBJECT: $subject -->\n" . $body;
        file_put_contents($filename, $content);
    }
}