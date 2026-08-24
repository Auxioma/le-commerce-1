<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class LotteryEntry extends Model
{
    protected static string $table = 'lottery_entries';

    /**
     * Génère (ou récupère) le ticket de participation d'un client pour une
     * loterie — un seul ticket par client et par loterie (contrainte
     * UNIQUE(lottery_id, user_id) en base).
     */
    public static function generate(int $lotteryId, int $userId): array
    {
        $existing = self::db()->prepare(
            'SELECT * FROM lottery_entries WHERE lottery_id = :lottery_id AND user_id = :user_id LIMIT 1'
        );
        $existing->execute(['lottery_id' => $lotteryId, 'user_id' => $userId]);
        if ($row = $existing->fetch()) {
            return $row;
        }

        $code = self::generateUniqueCode();
        $id = self::create([
            'lottery_id' => $lotteryId,
            'user_id'    => $userId,
            'code'       => $code,
        ]);

        return self::find($id);
    }

    private static function generateUniqueCode(): string
    {
        do {
            $code = 'LOT-' . strtoupper(bin2hex(random_bytes(4)));
            $exists = self::db()->prepare('SELECT 1 FROM lottery_entries WHERE code = :code');
            $exists->execute(['code' => $code]);
        } while ($exists->fetchColumn());

        return $code;
    }

    /**
     * Ticket d'un client pour une loterie donnée, s'il existe.
     */
    public static function forUserAndLottery(int $lotteryId, int $userId): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM lottery_entries WHERE lottery_id = :lottery_id AND user_id = :user_id LIMIT 1'
        );
        $stmt->execute(['lottery_id' => $lotteryId, 'user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function countForLottery(int $lotteryId): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM lottery_entries WHERE lottery_id = :lottery_id');
        $stmt->execute(['lottery_id' => $lotteryId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Tire un participant au hasard parmi les tickets de la loterie.
     * Le tirage se fait côté SQL (ORDER BY RAND()) pour rester équitable
     * même avec beaucoup de participants.
     */
    public static function randomEntryForLottery(int $lotteryId): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT e.*, u.first_name, u.last_name, u.phone_whatsapp
             FROM lottery_entries e
             JOIN users u ON u.id = e.user_id
             WHERE e.lottery_id = :lottery_id
             ORDER BY RAND()
             LIMIT 1'
        );
        $stmt->execute(['lottery_id' => $lotteryId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Derniers tickets générés, avec le nom du client — pour le fil
     * d'activité du tableau de bord.
     */
    public static function latestWithUser(int $limit = 3): array
    {
        $stmt = self::db()->prepare(
            'SELECT e.*, u.first_name, u.last_name
             FROM lottery_entries e
             JOIN users u ON u.id = e.user_id
             ORDER BY e.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function forUser(int $userId): array
    {
        $stmt = self::db()->prepare(
            'SELECT e.*, l.title AS lottery_title, l.prize, l.status AS lottery_status, l.ends_at, l.winner_user_id
             FROM lottery_entries e
             JOIN lotteries l ON l.id = e.lottery_id
             WHERE e.user_id = :user_id
             ORDER BY e.created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
