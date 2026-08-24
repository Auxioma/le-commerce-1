<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class PmuService extends Model
{
    protected static string $table = 'pmu_services';

    public static function listAllOrdered(): array
    {
        $stmt = self::db()->query('SELECT * FROM pmu_services ORDER BY display_order ASC, id ASC');
        return $stmt->fetchAll();
    }

    public static function nextDisplayOrder(): int
    {
        $max = self::db()->query('SELECT MAX(display_order) FROM pmu_services')->fetchColumn();
        return $max !== false && $max !== null ? ((int) $max) + 1 : 1;
    }
}
