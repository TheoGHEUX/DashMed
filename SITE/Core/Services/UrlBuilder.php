<?php

declare(strict_types=1);

namespace Core\Services;

/**
 * Service de construction d'URLs
 * Centralise la génération d'URLs pour éviter la duplication
 */
final class UrlBuilder
{
    /**
     * Construit une URL complète à partir d'un chemin
     *
     * @param string $path Chemin relatif (ex: '/verify-email')
     * @param array $params Paramètres GET optionnels
     * @return string URL complète (ex: https://example.com/verify-email?token=abc)
     */
    public static function build(string $path, array $params = []): string
    {
        $protocol = self::getProtocol();
        $domain = self::getDomain();
        
        $url = "{$protocol}://{$domain}{$path}";
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }

    /**
     * Retourne le protocole (http ou https)
     *
     * @return string 'http' ou 'https'
     */
    public static function getProtocol(): string
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    }

    /**
     * Retourne le domaine actuel
     *
     * @return string Domaine (ex: 'example.com' ou 'localhost:8080')
     */
    public static function getDomain(): string
    {
        return $_SERVER['HTTP_HOST'] ?? 'localhost';
    }

    /**
     * Retourne l'URL de base du site
     *
     * @return string URL de base (ex: 'https://example.com')
     */
    public static function getBaseUrl(): string
    {
        return self::getProtocol() . '://' . self::getDomain();
    }

    /**
     * Construit une URL absolue pour un fichier statique
     *
     * @param string $assetPath Chemin relatif du fichier (ex: '/assets/images/logo.png')
     * @return string URL complète
     */
    public static function asset(string $assetPath): string
    {
        return self::build($assetPath);
    }
}
