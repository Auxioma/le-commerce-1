<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Employee extends Model
{
    protected static string $table = 'employees';
    protected static bool $softDeletes = true;

    public static function allOrdered(): array
    {
        return self::db()->query('SELECT * FROM employees WHERE deleted_at IS NULL ORDER BY status ASC, first_name ASC')->fetchAll();
    }

    public static function countActive(): int
    {
        $stmt = self::db()->query("SELECT COUNT(*) FROM employees WHERE status = 'actif' AND deleted_at IS NULL");
        return (int) $stmt->fetchColumn();
    }
}
