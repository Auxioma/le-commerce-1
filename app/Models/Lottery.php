<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Lottery extends Model
{
    protected static string $table = 'lotteries';
    protected static bool $softDeletes = true;

    /**
     * Liste des loteries avec le nombre de participations et, si tirée,
     * le nom du gagnant.
     */
    public static function listWithStats(?string $status = null): array
    {
        $sql = "SELECT l.*, COUNT(e.id) AS entries_count, w.first_name AS winner_first_name, w.last_name AS winner_last_name
                FROM lotteries l
                LEFT JOIN lottery_entries e ON e.lottery_id = l.id
                LEFT JOIN users w ON w.id = l.winner_user_id
                WHERE l.deleted_at IS NULL";
        $params = [];
        if ($status) {
            $sql .= ' AND l.status = :status';
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
            "SELECT * FROM lotteries WHERE status = 'active' AND ends_at >= CURDATE() AND deleted_at IS NULL ORDER BY ends_at ASC"
        );
        return $stmt->fetchAll();
    }

    public static function countByStatus(string $status): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM lotteries WHERE status = :status AND deleted_at IS NULL');
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Prochaine loterie active (la plus proche de sa fin).
     */
    public static function nextActive(): ?array
    {
        $stmt = self::db()->query(
            "SELECT * FROM lotteries WHERE status = 'active' AND ends_at >= CURDATE() AND deleted_at IS NULL ORDER BY ends_at ASC LIMIT 1"
        );
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function totalEntries(): int
    {
        return (int) self::db()->query('SELECT COUNT(*) FROM lottery_entries')->fetchColumn();
    }

    public static function totalEntriesToday(): int
    {
        return (int) self::db()->query(
            "SELECT COUNT(*) FROM lottery_entries WHERE DATE(created_at) = CURDATE()"
        )->fetchColumn();
    }

    public static function findByQrToken(string $token): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM lotteries WHERE qr_token = :token AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['token' => $token]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Jeton public unique encodé dans le QR code de la loterie (URL :
     * /loterie/{qr_token}) — généré une fois à la création, jamais réutilisé.
     */
    public static function generateUniqueQrToken(): string
    {
        do {
            $token = bin2hex(random_bytes(10));
            $stmt = self::db()->prepare('SELECT 1 FROM lotteries WHERE qr_token = :token');
            $stmt->execute(['token' => $token]);
        } while ($stmt->fetchColumn());

        return $token;
    }
}
