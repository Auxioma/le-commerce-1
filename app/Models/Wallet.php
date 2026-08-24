<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Wallet extends Model
{
    protected static string $table = 'wallets';

    public static function findByUser(int $userId): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM wallets WHERE user_id = :user_id LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Crée le portefeuille d'un nouveau client avec un QR code unique.
     */
    public static function createForUser(int $userId): int
    {
        $qrCode = 'QR-' . strtoupper(bin2hex(random_bytes(6)));

        return self::create([
            'user_id' => $userId,
            'balance' => 0,
            'qr_code' => $qrCode,
        ]);
    }

    /**
     * Crédite/débite le solde de manière atomique (au niveau SQL, pas de
     * lecture-modification-écriture applicative qui pourrait créer une
     * situation de concurrence entre deux requêtes simultanées).
     */
    public static function adjustBalance(int $walletId, float $delta): void
    {
        $stmt = self::db()->prepare('UPDATE wallets SET balance = balance + :delta WHERE id = :id');
        $stmt->execute(['delta' => $delta, 'id' => $walletId]);
    }

    public static function totalBalance(): float
    {
        return (float) self::db()->query('SELECT COALESCE(SUM(balance), 0) FROM wallets')->fetchColumn();
    }

    public static function countCreatedThisMonth(): int
    {
        return (int) self::db()->query(
            "SELECT COUNT(*) FROM wallets WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())"
        )->fetchColumn();
    }

    /**
     * Top N clients par solde de portefeuille, avec leurs informations.
     */
    public static function topByBalance(int $limit = 5): array
    {
        $stmt = self::db()->prepare(
            'SELECT w.balance, u.id AS user_id, u.first_name, u.last_name, u.phone_whatsapp
             FROM wallets w
             JOIN users u ON u.id = w.user_id
             ORDER BY w.balance DESC
             LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
