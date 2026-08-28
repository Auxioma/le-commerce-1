<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class ContactMessage extends Model
{
    protected static string $table = 'contact_messages';

    public static function countUnread(): int
    {
        $stmt = self::db()->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0');
        return (int) $stmt->fetchColumn();
    }

    public static function countToday(): int
    {
        return (int) self::db()->query(
            'SELECT COUNT(*) FROM contact_messages WHERE DATE(created_at) = CURDATE()'
        )->fetchColumn();
    }

    public static function latest(int $limit = 5): array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function allOrdered(): array
    {
        return self::db()->query('SELECT * FROM contact_messages ORDER BY created_at DESC')->fetchAll();
    }

    public static function markRead(int $id, bool $read = true): void
    {
        $stmt = self::db()->prepare('UPDATE contact_messages SET is_read = :read WHERE id = :id');
        $stmt->execute(['read' => $read ? 1 : 0, 'id' => $id]);
    }

    /**
     * Nombre de messages envoyés depuis cette IP dans les $minutes dernières minutes
     * (anti-spam : limite la fréquence de soumission du formulaire public).
     */
    public static function countRecentByIp(string $ip, int $minutes): int
    {
        $stmt = self::db()->prepare(
            'SELECT COUNT(*) FROM contact_messages WHERE ip = :ip AND created_at >= (NOW() - INTERVAL :minutes MINUTE)'
        );
        $stmt->bindValue('ip', $ip);
        $stmt->bindValue('minutes', $minutes, \PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Demandes d'aide envoyées par un client depuis son espace
     * (/mon-compte/aide) — pour lui afficher l'historique et le statut.
     */
    public static function forUser(int $userId, int $limit = 20): array
    {
        $stmt = self::db()->prepare(
            'SELECT subject, message, is_read, created_at
             FROM contact_messages
             WHERE user_id = :user_id
             ORDER BY created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue('user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countRecentByUser(int $userId, int $minutes): int
    {
        $stmt = self::db()->prepare(
            'SELECT COUNT(*) FROM contact_messages WHERE user_id = :user_id AND created_at >= (NOW() - INTERVAL :minutes MINUTE)'
        );
        $stmt->bindValue('user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('minutes', $minutes, \PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}
