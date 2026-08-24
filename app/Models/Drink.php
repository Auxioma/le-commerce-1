<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Drink extends Model
{
    protected static string $table = 'drinks';

    public const CATEGORIES = [
        'biere_blonde' => 'Bière blonde',
        'biere_brune'  => 'Bière brune',
        'biere_ambree' => 'Bière ambrée',
        'autre'        => 'Autre boisson',
    ];

    public static function featured(int $limit = 10): array
    {
        $stmt = self::db()->prepare('SELECT * FROM drinks ORDER BY display_order ASC LIMIT :limit');
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Toutes les boissons, groupées par catégorie puis triées par ordre
     * d'affichage — utilisé par l'admin (contrairement à featured(), qui
     * limite le volume pour la page publique /le-bar).
     */
    public static function listAllOrdered(): array
    {
        $stmt = self::db()->query(
            "SELECT * FROM drinks ORDER BY FIELD(category, 'biere_blonde', 'biere_brune', 'biere_ambree', 'autre'), display_order ASC, id ASC"
        );
        return $stmt->fetchAll();
    }

    public static function nextDisplayOrder(string $category): int
    {
        $stmt = self::db()->prepare('SELECT MAX(display_order) FROM drinks WHERE category = :category');
        $stmt->execute(['category' => $category]);
        $max = $stmt->fetchColumn();
        return $max !== false && $max !== null ? ((int) $max) + 1 : 1;
    }
}
