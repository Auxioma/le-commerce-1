<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Settings;

/**
 * Mode maintenance : coupe l'accès public au site (page 503) tout en laissant
 * l'administration (/admin/*) et les adresses IP en liste blanche accessibles,
 * pour que le personnel puisse toujours se connecter et travailler.
 */
final class Maintenance
{
    /**
     * Bloque la requête courante (page de maintenance + exit) si le mode est
     * actif, sauf pour /admin/* et les IP de la liste blanche.
     */
    public static function enforce(string $uri): void
    {
        if ((string) Settings::get('maintenance_enabled', '0') !== '1') {
            return;
        }

        if ($uri === '/admin' || str_starts_with($uri, '/admin/')) {
            return;
        }

        if (self::isWhitelisted(self::clientIp())) {
            return;
        }

        self::render();
    }

    private static function clientIp(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    }

    private static function isWhitelisted(string $ip): bool
    {
        return $ip !== '' && in_array($ip, self::whitelist(), true);
    }

    /**
     * @return string[]
     */
    public static function whitelist(): array
    {
        $raw = (string) Settings::get('maintenance_whitelist_ips', '');
        $lines = preg_split('/[\r\n,]+/', $raw) ?: [];

        return array_values(array_filter(array_map('trim', $lines), static fn (string $ip): bool => $ip !== ''));
    }

    private static function render(): void
    {
        http_response_code(503);
        header('Retry-After: 3600');

        $appConfig = require dirname(__DIR__, 2) . '/config/app.php';
        $message = (string) Settings::get('maintenance_message', '');

        if (View::exists('errors/maintenance')) {
            echo View::render('errors/maintenance', [
                'message'  => $message,
                'shopName' => $appConfig['shop']['name'] ?? $appConfig['name'],
            ]);
        } else {
            echo 'Site en maintenance.';
        }

        exit;
    }
}
