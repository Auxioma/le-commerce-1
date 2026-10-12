<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class GalleryPhoto extends Model
{
    protected static string $table = 'gallery_photos';

    private const SELECT_WITH_GALLERY =
        'SELECT p.*, g.title AS gallery_title, g.slug AS gallery_slug,
                c.name AS category_name, c.slug AS category_slug
         FROM gallery_photos p
         JOIN galleries g ON g.id = p.gallery_id
         LEFT JOIN gallery_categories c ON c.id = g.category_id';

    public static function listByGallery(int $galleryId): array
    {
        $stmt = self::db()->prepare(self::SELECT_WITH_GALLERY . ' WHERE p.gallery_id = :id ORDER BY p.taken_at DESC, p.id DESC');
        $stmt->execute(['id' => $galleryId]);
        return $stmt->fetchAll();
    }

    /**
     * Photos publiées des galeries publiées, les plus récentes d'abord.
     */
    public static function listPublished(?int $limit = null): array
    {
        $sql = self::SELECT_WITH_GALLERY . " WHERE p.status = 'active' AND g.status = 'active' ORDER BY p.taken_at DESC, p.id DESC";
        if ($limit !== null) {
            $sql .= ' LIMIT ' . $limit;
        }
        return self::db()->query($sql)->fetchAll();
    }

    /**
     * Photos publiées regroupées par galerie : [gallery_id => [photos...]].
     */
    public static function listPublishedGroupedByGallery(): array
    {
        $grouped = [];
        foreach (self::listPublished() as $photo) {
            $grouped[(int) $photo['gallery_id']][] = $photo;
        }
        return $grouped;
    }

    public static function countByStatus(string $status): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM gallery_photos WHERE status = :status');
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }
}
