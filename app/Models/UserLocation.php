<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Dernière position GPS connue de chaque client (géolocalisation opt-in),
 * mise à jour à chaque appel de ProximityController::check(). Une seule
 * ligne par client (UNIQUE user_id) : on écrase la position précédente.
 */
class UserLocation extends Model
{
    protected static string $table = 'user_locations';

    /**
     * Enregistre (ou met à jour) la position d'un client.
     */
    public static function upsert(int $userId, float $lat, float $lng): void
    {
        $stmt = self::db()->prepare(
            'INSERT INTO user_locations (user_id, latitude, longitude)
             VALUES (:user_id, :lat, :lng)
             ON DUPLICATE KEY UPDATE latitude = :lat2, longitude = :lng2, updated_at = NOW()'
        );
        $stmt->execute([
            'user_id' => $userId,
            'lat' => $lat, 'lng' => $lng,
            'lat2' => $lat, 'lng2' => $lng,
        ]);
    }

    /**
     * Dernière position connue d'un client, ou null.
     */
    public static function forUser(int $userId): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT latitude, longitude, updated_at FROM user_locations WHERE user_id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Clients dont la position connue date de moins de $withinMinutes,
     * avec leurs informations, pour la carte "Clients à proximité".
     */
    public static function recentWithUser(int $withinMinutes = 60): array
    {
        $stmt = self::db()->prepare(
            "SELECT l.latitude, l.longitude, l.updated_at, u.id AS user_id, u.first_name, u.last_name, u.segment
             FROM user_locations l
             JOIN users u ON u.id = l.user_id
             WHERE l.updated_at >= NOW() - INTERVAL :minutes MINUTE AND u.deleted_at IS NULL
             ORDER BY l.updated_at DESC"
        );
        $stmt->bindValue('minutes', $withinMinutes, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countRecent(int $withinMinutes = 60): int
    {
        $stmt = self::db()->prepare(
            'SELECT COUNT(*) FROM user_locations WHERE updated_at >= NOW() - INTERVAL :minutes MINUTE'
        );
        $stmt->bindValue('minutes', $withinMinutes, \PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}
