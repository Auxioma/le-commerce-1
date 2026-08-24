<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Étiquettes libres posées sur un client (ex: "Client fidèle",
 * "Aime la bière"), utilisées dans la messagerie et sur sa fiche.
 */
class ClientLabel extends Model
{
    protected static string $table = 'client_labels';

    public const COLORS = ['gray', 'amber', 'blue', 'purple', 'green', 'red'];

    public static function forUser(int $userId): array
    {
        $stmt = self::db()->prepare('SELECT * FROM client_labels WHERE user_id = :id ORDER BY created_at ASC');
        $stmt->execute(['id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Table [user_id => [labels...]] pour un ensemble de clients
     * (affichage en liste, évite le N+1).
     */
    public static function forUsers(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $stmt = self::db()->prepare("SELECT * FROM client_labels WHERE user_id IN ({$placeholders}) ORDER BY created_at ASC");
        $stmt->execute(array_values($userIds));

        $byUser = [];
        foreach ($stmt->fetchAll() as $row) {
            $byUser[(int) $row['user_id']][] = $row;
        }
        return $byUser;
    }

    public static function allDistinctLabels(): array
    {
        $stmt = self::db()->query('SELECT DISTINCT label FROM client_labels ORDER BY label ASC');
        return array_column($stmt->fetchAll(), 'label');
    }

    public static function existsForUser(int $userId, string $label): bool
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM client_labels WHERE user_id = :id AND label = :label');
        $stmt->execute(['id' => $userId, 'label' => $label]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
