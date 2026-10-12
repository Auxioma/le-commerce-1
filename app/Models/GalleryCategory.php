<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class GalleryCategory extends Model
{
    protected static string $table = 'gallery_categories';

    /**
     * Catégories avec leur nombre de galeries (publiées ou non).
     */
    public static function listAllOrdered(): array
    {
        $stmt = self::db()->query(
            'SELECT c.*, (SELECT COUNT(*) FROM galleries g WHERE g.category_id = c.id) AS galleries_count
             FROM gallery_categories c ORDER BY c.display_order ASC, c.id ASC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Catégories actives qui ont au moins une photo visible côté public
     * (galerie publiée + photo publiée) : seules celles-ci servent de filtre.
     */
    public static function listWithPublishedPhotos(): array
    {
        $stmt = self::db()->query(
            "SELECT c.* FROM gallery_categories c
             WHERE c.status = 'active' AND EXISTS (
                 SELECT 1 FROM galleries g JOIN gallery_photos p ON p.gallery_id = g.id
                 WHERE g.category_id = c.id AND g.status = 'active' AND p.status = 'active'
             )
             ORDER BY c.display_order ASC, c.id ASC"
        );
        return $stmt->fetchAll();
    }

    public static function nextDisplayOrder(): int
    {
        $max = self::db()->query('SELECT MAX(display_order) FROM gallery_categories')->fetchColumn();
        return $max !== false && $max !== null ? ((int) $max) + 1 : 1;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM gallery_categories WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Slug unique dérivé du nom (ex: "Soirées" -> "soirees"), utilisé comme
     * clé de filtre côté public. $ignoreId exclut la catégorie en cours d'édition.
     */
    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = self::slugify($name, 'categorie');
        $slug = $base;
        $i = 2;
        while (($existing = self::findBySlug($slug)) !== null && (int) $existing['id'] !== $ignoreId) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }

    public static function slugify(string $text, string $fallback): string
    {
        $text = strtr($text, [
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ö' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'À' => 'a', 'É' => 'e', 'È' => 'e',
        ]);
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-') ?: $fallback;
    }
}
