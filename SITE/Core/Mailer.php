<?php

namespace Core;

/**
 * Gestionnaire d'envoi d'emails
 *
 * Fournit des méthodes pour envoyer des emails automatiques (vérification d'email,
 * réinitialisation de mot de passe, etc.).
 *
 * @package Core
 */
final class Mailer
{
    /**
     * Envoie un email de vérification d'adresse email.
     *
     * Génère un email HTML avec lien de vérification valide 24 heures.
     *
     * Utilise SERVER_NAME pour construire l'URL.
     *
     * @param string $to                 Destinataire
     * @param string $name               Prénom du destinataire
     * @param string $verificationToken  Jeton de vérification
     * @return bool
     */
    public static function sendEmailVerification(string $to, string $name, string $verificationToken): bool
    {
        $from = 'dashmed-site@alwaysdata.net';
        $subject = 'Vérifiez votre adresse email - DashMed';

        $headers = [
            'From: DashMed <' . $from . '>',
            'Reply-To: ' . $from,
            'MIME-Version:  1.0',
            'Content-Type: text/html; charset=UTF-8',
        ];

        // Construction de l'URL de vérification (éviter l'utilisation de HTTP_HOST contrôlable)
        $protocol = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $serverName = $_SERVER['SERVER_NAME'] ?? '';
        $host = $serverName !== '' ? $serverName : 'dashmed-site.alwaysdata.net';
        $verificationUrl = $protocol . '://' . $host . '/verify-email?token=' . urlencode($verificationToken);

        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeUrl = htmlspecialchars($verificationUrl, ENT_QUOTES, 'UTF-8');

        $body = '<!doctype html><html>'
            . '<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">'
            . '<div style="max-width: 600px; margin: 0 auto; padding: 20px; '
            . 'border: 1px solid #ddd; border-radius: 5px;">'
            . '<h2 style="color: #2c5282;">Bienvenue sur DashMed !</h2>'
            . '<p>Bonjour ' . $safeName . ',</p>'
            . '<p>Merci de vous être inscrit sur DashMed. Pour activer votre compte '
            . 'et commencer à utiliser notre plateforme, '
            . 'veuillez vérifier votre adresse email en cliquant ci-dessous :</p>'
            . '<div style="text-align: center; margin: 30px 0;">'
            . '<a href="' . $safeUrl . '" style="display: inline-block; padding: 12px 30px; '
            . 'background-color: #2c5282; color: white; text-decoration: none; '
            . 'border-radius: 5px; font-weight: bold;">Vérifier mon adresse email</a>'
            .  '</div>'
            . '<p>Ou copiez ce lien dans votre navigateur :</p>'
            . '<p style="word-break: break-all; color: #666; font-size:  12px;">'
            . $safeUrl .  '</p>'
            . '<p style="color: #e53e3e; margin-top: 20px;">'
            . '<strong>⚠️ Ce lien expire dans 24 heures.</strong></p>'
            . '<p style="color: #666; font-size: 12px; margin-top: 30px;">'
            . 'Si vous n\'êtes pas à l\'origine de cette inscription, '
            . 'vous pouvez ignorer cet email en toute sécurité.</p>'
            . '<hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">'
            . '<p style="color: #999; font-size:  11px; text-align:  center;">'
            . 'L\'équipe DashMed</p>'
            . '</div>'
            . '</body></html>';

        return self::send($to, $subject, $body, $headers, $from);
    }

    /**
     * Envoie un email de réinitialisation de mot de passe.
     *
     * Génère un email HTML avec lien de réinitialisation valide 60 minutes.
     *
     * Utilise SERVER_NAME pour construire l'URL.
     *
     * @param string $to           Destinataire
     * @param string $displayName  Nom affiché
     * @param string $resetUrl     URL de réinitialisation
     * @return bool
     */
    public static function sendPasswordResetEmail(string $to, string $displayName, string $resetUrl): bool
    {
        $from = 'dashmed-site@alwaysdata.net';
        $subject = 'Réinitialisation de votre mot de passe - DashMed';

        $headers = [
            'From: DashMed <' . $from . '>',
            'Reply-To: ' .  $from,
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
        ];

        $safeName = htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8');
        $safeUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');

        $body = '<!doctype html><html>'
            . '<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">'
            . '<div style="max-width: 600px; margin: 0 auto; padding: 20px; '
            . 'border: 1px solid #ddd; border-radius: 5px;">'
            . '<h2 style="color: #2c5282;">Réinitialisation de votre mot de passe</h2>'
            . '<p>Bonjour ' . $safeName . ',</p>'
            . '<p>Vous avez demandé la réinitialisation de votre mot de passe DashMed. '
            .  'Pour définir un nouveau mot de passe, cliquez sur le bouton ci-dessous :</p>'
            . '<div style="text-align: center; margin:  30px 0;">'
            . '<a href="' .  $safeUrl . '" style="display: inline-block; padding: 12px 30px; '
            . 'background-color: #2c5282; color: white; text-decoration:  none; '
            . 'border-radius: 5px; font-weight: bold;">Réinitialiser mon mot de passe</a>'
            . '</div>'
            . '<p>Ou copiez ce lien dans votre navigateur :</p>'
            . '<p style="word-break: break-all; color: #666; font-size:  12px;">'
            .  $safeUrl . '</p>'
            . '<p style="color: #e53e3e; margin-top: 20px;">'
            . '<strong>⚠️ Ce lien expire dans 60 minutes.</strong></p>'
            . '<p style="color: #666; font-size: 12px; margin-top: 30px;">'
            . 'Si vous n\'êtes pas à l\'origine de cette demande, '
            . 'vous pouvez ignorer cet email en toute sécurité.  '
            . 'Votre mot de passe actuel reste inchangé.</p>'
            .  '<hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">'
            .  '<p style="color: #999; font-size: 11px; text-align: center;">'
            . 'L\'équipe DashMed</p>'
            . '</div>'
            . '</body></html>';

        return self::send($to, $subject, $body, $headers, $from);
    }

    /**
     * Envoi centralisé : tente mail() puis fallback vers un fichier .eml si nécessaire.
     *
     * @param string $to
     * @param string $subject
     * @param string $htmlBody
     * @param array $headers
     * @param string $from
     * @return bool
     */
    private static function send(string $to, string $subject, string $htmlBody, array $headers, string $from): bool
    {
        $headersStr = implode("\r\n", $headers);

        $isWindows = (PHP_OS_FAMILY === 'Windows');

        $ok = false;
        if ($isWindows) {
            $ok = @mail($to, $subject, $htmlBody, $headersStr);
        } else {
            $ok = @mail($to, $subject, $htmlBody, $headersStr, '-f ' . $from);
        }

        if (!$ok) {
            $fileOk = self::writeMailToFile($to, $from, $subject, $headers, $htmlBody);
            error_log('[MAILER] mail() failed, fallback to file: ' . ($fileOk ? 'OK' : 'KO'));
            return $fileOk;
        }

        return true;
    }

    /**
     * Sauvegarde un email dans un fichier .eml.
     *
     * Crée le dossier SITE/storage/mails/ s'il n'existe pas.
     *
     * Génère un fichier .eml avec timestamp et identifiant unique.
     *
     * @param string $to
     * @param string $from
     * @param string $subject
     * @param array $headers
     * @param string $body
     * @return bool
     */
    private static function writeMailToFile(
        string $to,
        string $from,
        string $subject,
        array $headers,
        string $body
    ): bool {
        $dir = \Core\Constant::rootDirectory()
            . DIRECTORY_SEPARATOR . 'storage'
            . DIRECTORY_SEPARATOR . 'mails';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (!is_dir($dir)) {
            return false;
        }

        $filename = $dir . '/mail_' . date('YmdHis') . '_' . uniqid() . '.eml';

        $content = "To: {$to}\r\n";
        $content .= "From: {$from}\r\n";
        $content .= "Subject: {$subject}\r\n";
        foreach ($headers as $h) {
            $content .= $h . "\r\n";
        }
        $content .= "\r\n";
        $content .= $body;

        return (bool) file_put_contents($filename, $content);
    }
}
