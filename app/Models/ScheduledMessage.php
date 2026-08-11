<?php

namespace App\Models;

use App\Core\Model;

/**
 * Messages programmés pour un envoi différé (email, SMS ou WhatsApp).
 * Il n'y a pas de tâche planifiée (cron) dans ce projet : la liste sert
 * de carnet de rappels pour l'équipe, qui déclenche l'envoi réel depuis
 * la fiche du client une fois l'échéance arrivée.
 */
class ScheduledMessage extends Model
{
    protected static string $table = 'scheduled_messages';

    public static function countPending(): int
    {
        $stmt = self::db()->query("SELECT COUNT(*) FROM scheduled_messages WHERE status = 'programme'");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Liste des messages programmés à venir, avec les infos du client cible.
     */
    public static function upcomingWithUser(int $limit = 50): array
    {
        $stmt = self::db()->prepare(
            "SELECT sm.*, u.first_name, u.last_name
             FROM scheduled_messages sm
             JOIN users u ON u.id = sm.user_id
             WHERE sm.status = 'programme'
             ORDER BY sm.scheduled_at ASC
             LIMIT {$limit}"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function cancel(int $id): void
    {
        $stmt = self::db()->prepare("UPDATE scheduled_messages SET status = 'annule' WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}
