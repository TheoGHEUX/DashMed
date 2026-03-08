<?php

declare(strict_types=1);

namespace Core\Services;

class MailerService
{
    public function send(string $to, string $subject, string $body): bool
    {
        // 1. Essai d'envoi réel (SMTP)
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: no-reply@dashmed.com' . "\r\n";

        // Le @ cache les erreurs PHP, mais renvoie false si ça échoue
        $sent = @mail($to, $subject, $body, $headers);

        // 2. Si l'envoi échoue OU si on est en local (souvent le cas sur XAMPP)
        // On sauvegarde dans un fichier
        if (!$sent) {
            return $this->saveToDisk($to, $subject, $body);
        }

        return true;
    }

    private function saveToDisk(string $to, string $subject, string $body): bool
    {
        // On remonte de 2 niveaux : Core/Services -> Core -> SITE
        // Le chemin absolu est plus sûr
        $rootDir = dirname(__DIR__, 2);
        $storageDir = $rootDir . '/storage/mails';

        // Création du dossier s'il n'existe pas
        if (!is_dir($storageDir)) {
            if (!mkdir($storageDir, 0777, true)) {
                error_log("[MailerService] Impossible de créer le dossier : $storageDir");
                return false;
            }
        }

        // Nettoyage du sujet pour le nom de fichier
        $safeSubject = preg_replace('/[^a-zA-Z0-9_-]/', '_', substr($subject, 0, 50));
        $filename = sprintf(
            '%s/%s_%s.html',
            $storageDir,
            date('Y-m-d_H-i-s'),
            $safeSubject
        );

        // Contenu du fichier
        $content = "<!-- \n";
        $content .= "TO: $to \n";
        $content .= "SUBJECT: $subject \n";
        $content .= "DATE: " . date('Y-m-d H:i:s') . "\n";
        $content .= "-->\n\n";
        $content .= $body;

        $result = file_put_contents($filename, $content);

        if ($result === false) {
            error_log("[MailerService] Erreur d'écriture dans le fichier : $filename");
            return false;
        }

        return true;
    }
}