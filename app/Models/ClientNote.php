<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Notes internes admin sur un client (contexte, préférences, historique
 * d'échange), visibles depuis la fiche contact de la messagerie.
 */
class ClientNote extends Model
{
    protected static string $table = 'client_notes';

    public static function forUser(int $userId): array
    {
        $stmt = self::db()->prepare('SELECT * FROM client_notes WHERE user_id = :id ORDER BY created_at DESC');
        $stmt->execute(['id' => $userId]);
        return $stmt->fetchAll();
    }
}
