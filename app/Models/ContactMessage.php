<?php

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
}
