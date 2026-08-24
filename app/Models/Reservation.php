<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Reservation extends Model
{
    protected static string $table = 'reservations';
    protected static bool $softDeletes = true;

    /**
     * Liste filtrable (statut, date), triée par date/heure de réservation.
     *
     * @param array{status?:string, date?:string} $filters
     */
    public static function filtered(array $filters = []): array
    {
        $where  = ['deleted_at IS NULL'];
        $params = [];

        if (!empty($filters['status']) && $filters['status'] !== 'tous') {
            $where[] = 'status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['date'])) {
            $where[] = 'reservation_date = :date';
            $params['date'] = $filters['date'];
        }

        $sql = 'SELECT * FROM reservations WHERE ' . implode(' AND ', $where);
        $sql .= ' ORDER BY reservation_date ASC, reservation_time ASC';

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function countByStatus(string $status): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM reservations WHERE status = :status AND deleted_at IS NULL');
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Réservations à venir (aujourd'hui ou plus tard), non annulées.
     */
    public static function countUpcoming(): int
    {
        $stmt = self::db()->query(
            "SELECT COUNT(*) FROM reservations WHERE reservation_date >= CURDATE() AND status != 'annulee' AND deleted_at IS NULL"
        );
        return (int) $stmt->fetchColumn();
    }

    public static function countToday(): int
    {
        return (int) self::db()->query(
            "SELECT COUNT(*) FROM reservations WHERE reservation_date = CURDATE() AND status != 'annulee' AND deleted_at IS NULL"
        )->fetchColumn();
    }
}
