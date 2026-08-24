<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class ProximityCampaign extends Model
{
    protected static string $table = 'proximity_campaigns';

    public const SEGMENT_LABELS = [
        'tous'         => 'Tous les clients',
        'fideles'      => 'Clients fidèles',
        'nouveaux'     => 'Nouveaux clients',
        'occasionnels' => 'Clients occasionnels',
    ];

    public static function listWithOffer(): array
    {
        $stmt = self::db()->query(
            'SELECT c.*, o.title AS offer_title
             FROM proximity_campaigns c
             LEFT JOIN offers o ON o.id = c.offer_id
             ORDER BY c.created_at DESC'
        );
        return $stmt->fetchAll();
    }

    public static function countByStatus(string $status): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM proximity_campaigns WHERE status = :status');
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public static function totalSent(): int
    {
        return (int) self::db()->query('SELECT COALESCE(SUM(sent_count), 0) FROM proximity_campaigns')->fetchColumn();
    }

    public static function totalUsed(): int
    {
        return (int) self::db()->query('SELECT COALESCE(SUM(used_count), 0) FROM proximity_campaigns')->fetchColumn();
    }

    /**
     * Distance en mètres entre deux points GPS (formule de Haversine).
     */
    public static function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // mètres

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Jour courant au format court utilisé dans la colonne `days`
     * (ex: "lun,mar,mer,jeu,ven").
     */
    public static function currentDayCode(): string
    {
        $map = ['Mon' => 'lun', 'Tue' => 'mar', 'Wed' => 'mer', 'Thu' => 'jeu', 'Fri' => 'ven', 'Sat' => 'sam', 'Sun' => 'dim'];
        return $map[date('D')];
    }

    /**
     * Campagnes actives dont la plage horaire et le jour correspondent à
     * maintenant, pour un segment client donné. Le filtrage par distance
     * (dépendant de la position GPS de l'utilisateur) est fait à part,
     * en PHP, via distanceMeters().
     */
    public static function activeNowForSegment(string $segment): array
    {
        $day = self::currentDayCode();
        $now = date('H:i:s');

        $stmt = self::db()->prepare(
            "SELECT c.*, o.title AS offer_title, o.description AS offer_description, o.valid_until
             FROM proximity_campaigns c
             LEFT JOIN offers o ON o.id = c.offer_id
             WHERE c.status = 'active'
               AND FIND_IN_SET(:day, c.days)
               AND :now BETWEEN c.start_time AND c.end_time
               AND (c.target_segment = 'tous' OR c.target_segment = :segment)"
        );
        $stmt->execute(['day' => $day, 'now' => $now, 'segment' => $segment]);
        return $stmt->fetchAll();
    }

    public static function incrementSent(int $id): void
    {
        self::db()->prepare('UPDATE proximity_campaigns SET sent_count = sent_count + 1 WHERE id = :id')
            ->execute(['id' => $id]);
    }

    public static function incrementUsed(int $id): void
    {
        self::db()->prepare('UPDATE proximity_campaigns SET used_count = used_count + 1 WHERE id = :id')
            ->execute(['id' => $id]);
    }
}
