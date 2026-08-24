<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Offer extends Model
{
    protected static string $table = 'offers';

    public const TYPE_LABELS = [
        'gratuite'              => 'Offre gratuite',
        'reduction_pourcentage' => 'Réduction en %',
        'x_plus_1'              => 'X+1 offert',
        'montant_minimum'       => 'Montant minimum',
        'personnalisee'         => 'Personnalisée',
    ];

    public const SEGMENT_LABELS = [
        'tous'         => 'Tous les clients',
        'fideles'      => 'Clients fidèles',
        'nouveaux'     => 'Nouveaux clients',
        'occasionnels' => 'Clients occasionnels',
    ];

    /**
     * Liste des offres avec leur nombre d'utilisations, filtrable par statut.
     */
    public static function listWithUsage(?string $status = null): array
    {
        $sql = "SELECT o.*, COUNT(r.id) AS usage_count
                FROM offers o
                LEFT JOIN offer_redemptions r ON r.offer_id = o.id AND r.status = 'utilisee'";
        $params = [];
        if ($status) {
            $sql .= ' WHERE o.status = :status';
            $params['status'] = $status;
        }
        $sql .= ' GROUP BY o.id ORDER BY o.created_at DESC';

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function countByStatus(string $status): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM offers WHERE status = :status');
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public static function countCreatedThisMonth(): int
    {
        return (int) self::db()->query(
            "SELECT COUNT(*) FROM offers WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())"
        )->fetchColumn();
    }

    /**
     * Offres actives et non expirées, utilisables pour générer un code.
     */
    public static function activeForSelect(): array
    {
        $stmt = self::db()->query(
            "SELECT id, title FROM offers WHERE status = 'active' AND valid_until >= CURDATE() ORDER BY title ASC"
        );
        return $stmt->fetchAll();
    }
}
