<?php

namespace App\Models;

use App\Core\Model;

class Employee extends Model
{
    protected static string $table = 'employees';

    public static function allOrdered(): array
    {
        return self::db()->query('SELECT * FROM employees ORDER BY status ASC, first_name ASC')->fetchAll();
    }

    public static function countActive(): int
    {
        $stmt = self::db()->query("SELECT COUNT(*) FROM employees WHERE status = 'actif'");
        return (int) $stmt->fetchColumn();
    }
}
