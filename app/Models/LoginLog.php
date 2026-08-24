<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class LoginLog extends Model
{
    protected static string $table = 'login_logs';

    public static function record(int $userId): void
    {
        self::create([
            'user_id'    => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
    }
}
