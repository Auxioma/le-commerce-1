<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Settings
{
    private static ?array $cache = null;

    /**
     * Retourne tous les paramètres sous forme de tableau clé => valeur.
     * Renvoie un tableau vide (au lieu de planter) si la table n'existe
     * pas encore — utile en transition, avant application de la migration.
     * Mis en cache pour la durée de la requête (le tableau brut est
     * transmis à toutes les vues via ShopSettingsService).
     */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        try {
            $stmt = Database::connection()->query('SELECT `key`, `value` FROM settings');
            $rows = $stmt->fetchAll();
            $result = [];
            foreach ($rows as $row) {
                $result[$row['key']] = $row['value'];
            }
            return self::$cache = $result;
        } catch (\Throwable $e) {
            return self::$cache = [];
        }
    }

    public static function get(string $key, $default = null)
    {
        $all = self::all();
        return $all[$key] ?? $default;
    }

    /**
     * Met à jour plusieurs paramètres en une fois (upsert).
     */
    public static function updateMany(array $data): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO settings (`key`, `value`) VALUES (:key, :value)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
        );
        foreach ($data as $key => $value) {
            $stmt->execute(['key' => $key, 'value' => $value]);
        }
        self::$cache = null;
    }
}
