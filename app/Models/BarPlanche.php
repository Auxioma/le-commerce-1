<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class BarPlanche extends Model
{
    protected static string $table = 'bar_planches';

    public static function listAllOrdered(): array
    {
        $stmt = self::db()->query('SELECT * FROM bar_planches ORDER BY display_order ASC, id ASC');
        return $stmt->fetchAll();
    }

    public static function listActiveOrdered(): array
    {
        $stmt = self::db()->query(
            "SELECT * FROM bar_planches WHERE status = 'active' ORDER BY display_order ASC, id ASC"
        );
        return $stmt->fetchAll();
    }

    public static function countByStatus(string $status): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM bar_planches WHERE status = :status');
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public static function nextDisplayOrder(): int
    {
        $max = self::db()->query('SELECT MAX(display_order) FROM bar_planches')->fetchColumn();
        return $max !== false && $max !== null ? ((int) $max) + 1 : 1;
    }
}
