<?php

namespace App\Models;

use App\Core\Model;

class Lottery extends Model
{
    protected static string $table = 'lotteries';

    /**
     * Liste des loteries avec le nombre de participations et, si tirée,
     * le nom du gagnant.
     */
    public static function listWithStats(?string $status = null): array
    {
        $sql = "SELECT l.*, COUNT(e.id) AS entries_count, w.first_name AS winner_first_name, w.last_name AS winner_last_name
                FROM lotteries l
                LEFT JOIN lottery_entries e ON e.lottery_id = l.id
                LEFT JOIN users w ON w.id = l.winner_user_id";
        $params = [];
        if ($status) {
            $sql .= ' WHERE l.status = :status';
            $params['status'] = $status;
        }
        $sql .= ' GROUP BY l.id ORDER BY l.created_at DESC';

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function activeForClients(): array
    {
        $stmt = self::db()->query(
            "SELECT * FROM lotteries WHERE status = 'active' AND ends_at >= CURDATE() ORDER BY ends_at ASC"
        );
        return $stmt->fetchAll();
    }

    public static function countByStatus(string $status): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM lotteries WHERE status = :status');
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }
}
