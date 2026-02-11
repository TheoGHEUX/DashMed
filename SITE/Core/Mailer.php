<?php

namespace Core;

/**
 * Gestionnaire d'envoi d'emails.
 *
 * Responsable de l'expédition des courriels transactionnels (inscription, mot de passe).
 * Sépare la logique PHP du design en chargeant des templates HTML depuis `Views/emails/`.
 *
 * @package Core
 */
final class Mailer
{
    private const FROM_EMAIL = 'dashmed-site@alwaysdata.net';

    /**
     * Envoie l'email de confirmation d'inscription.
     *
     * Génère l'URL de validation et charge le template HTML associé.
     *
     * @param string $to                Email du destinataire
     * @param string $name              Prénom de l'utilisateur
     * @param string $verificationToken Token unique de validation
     * @return bool                     Succès de l'envoi
     */
    public static function sendEmailVerification(string $to, string $name, string $verificationToken): bool
    {
        // Construction de l'URL absolue vers le contrôleur de vérification
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['SERVER_NAME'] ?? 'dashmed-site.alwaysdata.net';
        $url = $protocol . '://' . $host . '/verify-email?token=' . urlencode($verificationToken);

        // Chargement du design HTML
        $htmlBody = self::loadTemplate('emails/verify-email', [
            'name' => $name,
            'url' => $url
        ]);

        if (!$htmlBody) return false;

        return self::send($to, 'Vérifiez votre adresse email - DashMed', $htmlBody);
    }

    /**
     * Envoie l'email de réinitialisation de mot de passe.
     *
     * @param string $to          Email du destinataire
     * @param string $displayName Nom affiché dans le mail
     * @param string $resetUrl    Lien complet de réinitialisation
     * @return bool               Succès de l'envoi
     */
    public static function sendPasswordResetEmail(string $to, string $displayName, string $resetUrl): bool
    {
        $htmlBody = self::loadTemplate('emails/reset-password', [
            'name' => $displayName,
            'url' => $resetUrl
        ]);

        if (!$htmlBody) return false;

        return self::send($to, 'Réinitialisation de votre mot de passe - DashMed', $htmlBody);
    }

    /**
     * Charge un fichier de vue et retourne son contenu HTML sous forme de chaîne.
     *
     * Utilise la temporisation de sortie (Output Buffering) pour capturer le rendu.
     *
     * @param string $viewPath Chemin relatif (ex: 'emails/verify-email')
     * @param array  $data     Données à extraire dans la vue ($name, $url...)
     * @return string|null     Le HTML généré ou null si fichier introuvable
     */
    private static function loadTemplate(string $viewPath, array $data = []): ?string
    {
        $file = dirname(__DIR__) . '/Views/' . $viewPath . '.php';

        if (!file_exists($file)) {
            error_log("[MAILER] Template introuvable : $file");
            return null;
        }

        extract($data); // Transforme les clés du tableau en variables locales

        ob_start();
        include $file;
        return ob_get_clean();
    }

    /**
     * Envoi interne via la fonction mail() de PHP.
     *
     * Gère les headers MIME pour le HTML et le fallback fichier en cas d'échec (local).
     */
    private static function send(string $to, string $subject, string $htmlBody): bool
    {
        $headers = [
            'From: DashMed <' . self::FROM_EMAIL . '>',
            'Reply-To: ' . self::FROM_EMAIL,
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
        ];

        $headersStr = implode("\r\n", $headers);

        // Adaptation Windows/Linux
        if (PHP_OS_FAMILY === 'Windows') {
            $ok = @mail($to, $subject, $htmlBody, $headersStr);
        } else {
            $ok = @mail($to, $subject, $htmlBody, $headersStr, '-f ' . self::FROM_EMAIL);
        }

        // Si l'envoi échoue, log dans un fichier
        if (!$ok) {
            return self::writeMailToFile($to, self::FROM_EMAIL, $subject, $headers, $htmlBody);
        }

        return true;
    }

    /**
     * Sauvegarde l'email dans un fichier .eml pour le débug en local.
     *
     * @return bool True si l'écriture a réussi
     */
    private static function writeMailToFile(string $to, string $from, string $subject, array $headers, string $body): bool
    {
        $dir = dirname(__DIR__) . '/../storage/mails';
        if (!is_dir($dir)) { @mkdir($dir, 0755, true); }

        $filename = $dir . '/mail_' . date('Ymd_His') . '_' . uniqid() . '.eml';
        $content = "To: $to\r\nSubject: $subject\r\n" . implode("\r\n", $headers) . "\r\n\r\n" . $body;

        return (bool) file_put_contents($filename, $content);
    }
}