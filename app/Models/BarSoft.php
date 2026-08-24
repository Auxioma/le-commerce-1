<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class BarSoft extends Model
{
    protected static string $table = 'bar_softs';

    public static function listAllOrdered(): array
    {
        $stmt = self::db()->query('SELECT * FROM bar_softs ORDER BY display_order ASC, id ASC');
        return $stmt->fetchAll();
    }

    public static function nextDisplayOrder(): int
    {
        $max = self::db()->query('SELECT MAX(display_order) FROM bar_softs')->fetchColumn();
        return $max !== false && $max !== null ? ((int) $max) + 1 : 1;
    }
}
