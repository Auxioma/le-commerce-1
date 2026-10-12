<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Galerie (album) de photos : « Soirée match OM-PSG », « Ambiance au bar »...
 * Chaque galerie appartient (facultativement) à une catégorie de
 * gallery_categories et contient les photos de gallery_photos.
 */
class Gallery extends Model
{
    protected static string $table = 'galleries';

    /** Couverture = photo la plus récente de la galerie (publiée si possible). */
    private const SELECT_WITH_STATS =
        "SELECT g.*, c.name AS category_name, c.slug AS category_slug,
                (SELECT COUNT(*) FROM gallery_photos p WHERE p.gallery_id = g.id) AS photos_count,
                (SELECT COUNT(*) FROM gallery_photos p WHERE p.gallery_id = g.id AND p.status = 'active') AS published_count,
                (SELECT p.image FROM gallery_photos p WHERE p.gallery_id = g.id
                 ORDER BY p.status = 'active' DESC, p.taken_at DESC, p.id DESC LIMIT 1) AS cover_image
         FROM galleries g LEFT JOIN gallery_categories c ON c.id = g.category_id";

    public static function listAllOrdered(): array
    {
        return self::db()->query(self::SELECT_WITH_STATS . ' ORDER BY g.event_date DESC, g.id DESC')->fetchAll();
    }

    public static function listForSelect(): array
    {
        return self::db()->query('SELECT id, title, status FROM galleries ORDER BY event_date DESC, id DESC')->fetchAll();
    }

    /**
     * Galeries publiées contenant au moins une photo publiée, les plus
     * récentes d'abord.
     */
    public static function listPublished(): array
    {
        $sql = 'SELECT * FROM (' . self::SELECT_WITH_STATS . " WHERE g.status = 'active') t
                WHERE t.published_count > 0 ORDER BY t.event_date DESC, t.id DESC";
        return self::db()->query($sql)->fetchAll();
    }

    public static function findWithStats(int $id): ?array
    {
        $stmt = self::db()->prepare(self::SELECT_WITH_STATS . ' WHERE g.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM galleries WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function countByStatus(string $status): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM galleries WHERE status = :status');
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    /** $ignoreId exclut la galerie en cours d'édition. */
    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = GalleryCategory::slugify($title, 'galerie');
        $slug = $base;
        $i = 2;
        while (($existing = self::findBySlug($slug)) !== null && (int) $existing['id'] !== $ignoreId) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }
}
