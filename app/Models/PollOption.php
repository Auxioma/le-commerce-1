<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class PollOption extends Model
{
    protected static string $table = 'poll_options';

    public static function forPoll(int $pollId): array
    {
        $stmt = self::db()->prepare('SELECT * FROM poll_options WHERE poll_id = :poll_id ORDER BY id ASC');
        $stmt->execute(['poll_id' => $pollId]);
        return $stmt->fetchAll();
    }

    public static function createMany(int $pollId, array $labels): void
    {
        $stmt = self::db()->prepare('INSERT INTO poll_options (poll_id, label, votes_count) VALUES (:poll_id, :label, 0)');
        foreach ($labels as $label) {
            $label = trim($label);
            if ($label === '') {
                continue;
            }
            $stmt->execute(['poll_id' => $pollId, 'label' => $label]);
        }
    }
}
