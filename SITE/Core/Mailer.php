<?php
namespace Core;

final class Mailer
{
    public static function sendRegistrationEmail(string $to, string $name, ?string $from = null): bool
    {
        $from = $from ?: 'dashmed-site@alwaysdata.net';
        $subject = 'Bienvenue sur DashMed';

        $headers = [
            'From: DashMed <' . $from . '>',
            'Reply-To: ' . $from,
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
        ];

        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $body = '<!doctype html><html><body>'
            . '<p>Bonjour ' . $safeName . ',</p>'
            . '<p>Bienvenue sur DashMed ! Votre compte a bien été créé.</p>'
            . '<p>Vous pouvez dès à présent vous connecter et commencer à utiliser notre application pour gérer votre tableau de bord en toute simplicité.</p>'
            . '<p>Si vous n’êtes pas à l’origine de cette inscription, ignorez ce message ou contactez le support.</p>'
            . '<p>À très vite,<br>L’équipe DashMed</p>'
            . '</body></html>';

        return self::send($to, $subject, $body, $headers, $from);
    }

    /**
     * Envoie un email de vérification avec le lien contenant le token
     */
    public static function sendEmailVerification(string $to, string $name, string $verificationToken): bool
    {
        $from = 'dashmed-site@alwaysdata.net';
        $subject = 'Vérifiez votre adresse email - DashMed';

        $headers = [
            'From: DashMed <' . $from . '>',
            'Reply-To: ' . $from,
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
        ];

        // Construction de l'URL de vérification
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'dashmed-site.alwaysdata.net';
        $verificationUrl = $protocol . '://' . $host . '/verify-email?token=' . urlencode($verificationToken);

        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeUrl = htmlspecialchars($verificationUrl, ENT_QUOTES, 'UTF-8');

        $body = '<!doctype html><html><body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">'
            . '<div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">'
            . '<h2 style="color: #2c5282;">Bienvenue sur DashMed !</h2>'
            . '<p>Bonjour ' . $safeName . ',</p>'
            . '<p>Merci de vous être inscrit sur DashMed. Pour activer votre compte et commencer à utiliser notre plateforme, veuillez vérifier votre adresse email en cliquant sur le bouton ci-dessous :</p>'
            . '<div style="text-align: center; margin: 30px 0;">'
            . '<a href="' . $safeUrl . '" style="display: inline-block; padding: 12px 30px; background-color: #2c5282; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Vérifier mon adresse email</a>'
            . '</div>'
            . '<p>Ou copiez ce lien dans votre navigateur :</p>'
            . '<p style="word-break: break-all; color: #666; font-size: 12px;">' . $safeUrl . '</p>'
            . '<p style="color: #e53e3e; margin-top: 20px;"><strong>⚠️ Ce lien expire dans 24 heures.</strong></p>'
            . '<p style="color: #666; font-size: 12px; margin-top: 30px;">Si vous n\'êtes pas à l\'origine de cette inscription, vous pouvez ignorer cet email en toute sécurité.</p>'
            . '<hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">'
            . '<p style="color: #999; font-size: 11px; text-align: center;">L\'équipe DashMed</p>'
            . '</div>'
            . '</body></html>';

        return self::send($to, $subject, $body, $headers, $from);
    }

    //envoie mail pour reset mdp
    public static function sendPasswordResetEmail(string $to, string $displayName, string $resetUrl): bool
    {
        $from = 'dashmed-site@alwaysdata.net'; // adapte à ton domaine
        $subject = 'Réinitialisation de votre mot de passe';
        $headers = [
            'From: DashMed <' . $from . '>',
            'Reply-To: ' . $from,
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
        ];
        $safeName = htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8');
        $safeUrl  = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');

        $body = '<!doctype html><html><body>'
            . "<p>Bonjour {$safeName},</p>"
            . '<p>Vous avez demandé la réinitialisation de votre mot de passe. '
            . 'Cliquez sur le lien ci-dessous (valable 60 minutes):</p>'
            . "<p><a href=\"{$safeUrl}\">Réinitialiser mon mot de passe</a></p>"
            . '<p>Si vous n’êtes pas à l’origine de cette demande, ignorez cet email ou contacter le services clients.</p>'
            . '</body></html>';

        return self::send($to, $subject, $body, $headers, $from);
    }

    /**
     * Envoi centralisé: tente mail() avec les bons paramètres suivant l'OS.
     * Si indisponible (cas courant sous XAMPP/Windows) ou en échec, écrit un fichier .eml dans SITE/storage/mails
     * et retourne true pour débloquer les parcours en dev.
     */
    private static function send(string $to, string $subject, string $htmlBody, array $headers, string $from): bool
    {
        $headersStr = implode("\r\n", $headers);

        // Sous Windows, le 5e paramètre (-f) n'est pas supporté
        $isWindows = (PHP_OS_FAMILY === 'Windows');

        $ok = false;
        if ($isWindows) {
            // Essayer sans paramètre supplémentaire
            $ok = @mail($to, $subject, $htmlBody, $headersStr);
        } else {
            // Sur Unix/Linux, on peut préciser l'enveloppe expéditeur
            $ok = @mail($to, $subject, $htmlBody, $headersStr, '-f ' . $from);
        }

        if (!$ok) {
            // Fallback dev: écrire dans un fichier .eml
            $fileOk = self::writeMailToFile($to, $from, $subject, $headers, $htmlBody);
            error_log(sprintf('[MAIL][FALLBACK->FILE] to=%s from=%s subject="%s" saved=%s', $to, $from, $subject, $fileOk ? 'OK' : 'FAIL'));
            return $fileOk; // on considère succès en dev si écrit
        }

        error_log(sprintf('[MAIL] to=%s from=%s subject="%s" result=OK', $to, $from, $subject));
        return true;
    }

    private static function writeMailToFile(string $to, string $from, string $subject, array $headers, string $htmlBody): bool
    {
        // Répertoire: SITE/storage/mails
        $baseDir = \Constant::rootDirectory() . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'mails';
        if (!is_dir($baseDir)) {
            @mkdir($baseDir, 0777, true);
        }

        $timestamp = date('Ymd-His');
        $uniq = bin2hex(random_bytes(4));
        $file = $baseDir . DIRECTORY_SEPARATOR . $timestamp . '-' . $uniq . '.eml';

        // Construire un contenu EML minimal
        $lines = [];
        $lines[] = 'To: ' . $to;
        $lines[] = 'From: ' . $from;
        $lines[] = 'Subject: ' . $subject;
        foreach ($headers as $h) {
            // Eviter doublons simples pour From/Subject si déjà ajoutés
            if (stripos($h, 'from:') === 0 || stripos($h, 'subject:') === 0 || stripos($h, 'to:') === 0) {
                continue;
            }
            $lines[] = $h;
        }
        $lines[] = '';
        $lines[] = $htmlBody;

        $content = implode("\r\n", $lines);
        return (bool) @file_put_contents($file, $content);
    }
}