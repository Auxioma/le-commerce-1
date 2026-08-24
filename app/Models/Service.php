<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Service extends Model
{
    protected static string $table = 'services';

    public static function listAllOrdered(): array
    {
        $stmt = self::db()->query('SELECT * FROM services ORDER BY sort_order ASC, id ASC');
        return $stmt->fetchAll();
    }

    public static function listActiveOrdered(): array
    {
        $stmt = self::db()->query(
            "SELECT * FROM services WHERE status = 'active' ORDER BY sort_order ASC, id ASC"
        );
        return $stmt->fetchAll();
    }

    public static function countByStatus(string $status): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM services WHERE status = :status');
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public static function countCreatedThisMonth(): int
    {
        return (int) self::db()->query(
            "SELECT COUNT(*) FROM services WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())"
        )->fetchColumn();
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM services WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function nextSortOrder(): int
    {
        $max = self::db()->query('SELECT MAX(sort_order) FROM services')->fetchColumn();
        return $max !== false && $max !== null ? ((int) $max) + 1 : 1;
    }

    /**
     * Génère un slug unique à partir du nom du service
     * (ex: "Photocopies & impressions" -> "services_photocopies_impressions"),
     * en évitant les collisions avec les slugs existants.
     */
    public static function generateUniqueSlug(string $name): string
    {
        $base = 'services_' . self::slugify($name);
        $slug = $base;
        $i = 2;
        while (self::findBySlug($slug) !== null) {
            $slug = $base . '_' . $i;
            $i++;
        }
        return $slug;
    }

    private static function slugify(string $text): string
    {
        $text = strtr($text, [
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ö' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'À' => 'a', 'É' => 'e', 'È' => 'e',
        ]);
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '_', $text);
        return trim($text, '_') ?: 'service';
    }
}
