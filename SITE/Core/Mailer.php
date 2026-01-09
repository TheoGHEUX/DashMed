<?php

namespace Core;

/**
 * Gestionnaire d'envoi d'emails de l'application.
 *
 * Fournit des méthodes statiques pour envoyer des emails transactionnels
 * (vérification d'email, réinitialisation de mot de passe, etc.).
 * Implémente un mécanisme de fallback :  en cas d'échec de mail(), les emails
 * sont sauvegardés dans des fichiers .eml pour traitement ultérieur.
 *
 * @package Core
 */
final class Mailer
{
    /**
     * Envoie un email de vérification d'adresse email.
     *
     * Génère un email HTML contenant un lien de vérification avec token.
     * Le lien expire après 24 heures.  L'URL est construite automatiquement
     * à partir du protocole et du nom de serveur courants.
     *
     * @param string $to Adresse email du destinataire
     * @param string $name Prénom du destinataire (affiché dans l'email)
     * @param string $verificationToken Token de vérification unique (URL-safe)
     * @return bool True si l'envoi a réussi (ou sauvegardé en fichier), false sinon
     */
    public static function sendEmailVerification(string $to, string $name, string $verificationToken): bool
    {
        $from = 'dashmed-site@alwaysdata.net';
        $subject = 'Vérifiez votre adresse email - DashMed';

        $headers = [
            'From: DashMed <' .  $from . '>',
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

        $body = '<! doctype html><html>'
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
            . '<p>Ou copiez ce lien dans votre navigateur : </p>'
            . '<p style="word-break: break-all; color: #666; font-size:  12px;">'
            .  $safeUrl . '</p>'
            . '<p style="color: #e53e3e; margin-top: 20px;">'
            . '<strong>⚠️ Ce lien expire dans 24 heures.</strong></p>'
            . '<p style="color: #666; font-size: 12px; margin-top: 30px;">'
            . 'Si vous n\'êtes pas à l\'origine de cette inscription, '
            .  'vous pouvez ignorer cet email en toute sécurité.</p>'
            .  '<hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">'
            .  '<p style="color: #999; font-size: 11px; text-align: center;">'
            . 'L\'équipe DashMed</p>'
            . '</div>'
            . '</body></html>';

        return self::send($to, $subject, $body, $headers, $from);
    }

    /**
     * Envoie un email de réinitialisation de mot de passe.
     *
     * Génère un email HTML contenant un lien sécurisé de réinitialisation.
     * Le lien expire après 60 minutes pour des raisons de sécurité.
     *
     * @param string $to Adresse email du destinataire
     * @param string $displayName Nom complet du destinataire (affiché dans l'email)
     * @param string $resetUrl URL complète de réinitialisation avec token inclus
     * @return bool True si l'envoi a réussi (ou sauvegardé en fichier), false sinon
     */
    public static function sendPasswordResetEmail(string $to, string $displayName, string $resetUrl): bool
    {
        $from = 'dashmed-site@alwaysdata. net';
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
            . '<div style="max-width: 600px; margin: 0 auto; padding:  20px; '
            .  'border: 1px solid #ddd; border-radius:  5px;">'
            .  '<h2 style="color:  #2c5282;">Réinitialisation de votre mot de passe</h2>'
            . '<p>Bonjour ' . $safeName . ',</p>'
            . '<p>Vous avez demandé la r��initialisation de votre mot de passe DashMed. '
            .  'Pour définir un nouveau mot de passe, cliquez sur le bouton ci-dessous :</p>'
            . '<div style="text-align: center; margin:  30px 0;">'
            . '<a href="' .  $safeUrl . '" style="display: inline-block; padding: 12px 30px; '
            . 'background-color: #2c5282; color: white; text-decoration:  none; '
            . 'border-radius: 5px; font-weight: bold;">Réinitialiser mon mot de passe</a>'
            . '</div>'
            . '<p>Ou copiez ce lien dans votre navigateur :</p>'
            . '<p style="word-break: break-all; color: #666; font-size: 12px;">'
            . $safeUrl . '</p>'
            . '<p style="color: #e53e3e; margin-top: 20px;">'
            . '<strong>⚠️ Ce lien expire dans 60 minutes.</strong></p>'
            . '<p style="color: #666; font-size: 12px; margin-top: 30px;">'
            . 'Si vous n\'êtes pas à l\'origine de cette demande, '
            . 'vous pouvez ignorer cet email en toute sécurité.  '
            . 'Votre mot de passe actuel reste inchangé.</p>'
            . '<hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">'
            . '<p style="color: #999; font-size: 11px; text-align: center;">'
            . 'L\'équipe DashMed</p>'
            . '</div>'
            . '</body></html>';

        return self::send($to, $subject, $body, $headers, $from);
    }

    /**
     * Méthode centralisée d'envoi d'email avec mécanisme de fallback.
     *
     * Tente d'abord l'envoi via la fonction mail() de PHP.  En cas d'échec,
     * sauvegarde l'email dans un fichier .eml pour traitement manuel ultérieur.
     *
     * Adaptations selon l'OS :
     * - Unix/Linux :  Utilise le paramètre -f pour spécifier l'expéditeur
     * - Windows : N'utilise pas -f (incompatible avec certains MTA Windows)
     *
     * @param string $to Adresse email du destinataire
     * @param string $subject Sujet de l'email
     * @param string $htmlBody Corps de l'email au format HTML
     * @param array<int,string> $headers Tableau des en-têtes (From, Reply-To, MIME, etc.)
     * @param string $from Adresse email de l'expéditeur (pour -f sur Unix)
     * @return bool True si envoyé avec succès ou sauvegardé en fichier, false si échec complet
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
     * Sauvegarde un email dans un fichier .eml en cas d'échec d'envoi.
     *
     * Les fichiers sont stockés dans SITE/storage/mails/ avec un nom unique
     * basé sur la date/heure et un identifiant aléatoire.
     * Le format . eml est compatible avec la plupart des clients de messagerie.
     *
     * Le dossier storage/mails est créé automatiquement s'il n'existe pas.
     *
     * @param string $to Adresse email du destinataire
     * @param string $from Adresse email de l'expéditeur
     * @param string $subject Sujet de l'email
     * @param array<int,string> $headers Tableau des en-têtes
     * @param string $body Corps de l'email (HTML ou texte)
     * @return bool True si le fichier a été créé avec succès, false sinon
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
        if (! is_dir($dir)) {
            return false;
        }

        $filename = $dir . '/mail_' . date('YmdHis') . '_' . uniqid() . '.eml';

        $content = "To:  {$to}\r\n";
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