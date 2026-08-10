<?php

namespace App\Models;

use App\Core\Model;

class WhatsappMessage extends Model
{
    protected static string $table = 'whatsapp_messages';

    public static function countByDirection(string $direction): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM whatsapp_messages WHERE direction = :direction');
        $stmt->execute(['direction' => $direction]);
        return (int) $stmt->fetchColumn();
    }

    public static function countSentToday(): int
    {
        return (int) self::db()->query(
            "SELECT COUNT(*) FROM whatsapp_messages WHERE direction = 'sortant' AND DATE(sent_at) = CURDATE()"
        )->fetchColumn();
    }

    public static function latest(int $limit = 5): array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM whatsapp_messages ORDER BY sent_at DESC LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Une ligne par client ayant échangé au moins un message WhatsApp,
     * avec l'aperçu du dernier message — pour la boîte de réception admin.
     */
    public static function threads(int $limit = 100): array
    {
        $stmt = self::db()->prepare(
            "SELECT wm.user_id, u.first_name, u.last_name, u.phone_whatsapp, u.segment,
                    (SELECT content FROM whatsapp_messages w2 WHERE w2.user_id = wm.user_id ORDER BY w2.sent_at DESC, w2.id DESC LIMIT 1) AS last_content,
                    (SELECT sent_at FROM whatsapp_messages w2 WHERE w2.user_id = wm.user_id ORDER BY w2.sent_at DESC, w2.id DESC LIMIT 1) AS last_sent_at,
                    COUNT(*) AS message_count
             FROM whatsapp_messages wm
             JOIN users u ON u.id = wm.user_id
             GROUP BY wm.user_id, u.first_name, u.last_name, u.phone_whatsapp, u.segment
             ORDER BY last_sent_at DESC
             LIMIT {$limit}"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Historique complet (chronologique) des messages échangés avec un client,
     * pour l'affichage du fil de conversation.
     */
    public static function forUserChronological(int $userId): array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM whatsapp_messages WHERE user_id = :id ORDER BY sent_at ASC, id ASC'
        );
        $stmt->execute(['id' => $userId]);
        return $stmt->fetchAll();
    }
}
