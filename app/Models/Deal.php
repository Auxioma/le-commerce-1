<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Deal extends Model
{
    protected static string $table = 'deals';

    public static function current(): ?array
    {
        $stmt = self::db()->query("SELECT * FROM deals WHERE active = 1 ORDER BY created_at DESC LIMIT 1");
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
