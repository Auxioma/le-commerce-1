<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class NewsArticle extends Model
{
    protected static string $table = 'news_articles';

    public static function listAllOrdered(): array
    {
        $stmt = self::db()->query('SELECT * FROM news_articles ORDER BY published_at DESC, id DESC');
        return $stmt->fetchAll();
    }

    public static function listPublished(?int $limit = null): array
    {
        $sql = "SELECT * FROM news_articles WHERE status = 'active' ORDER BY published_at DESC, id DESC";
        if ($limit !== null) {
            $sql .= ' LIMIT ' . $limit;
        }
        return self::db()->query($sql)->fetchAll();
    }

    public static function countByStatus(string $status): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM news_articles WHERE status = :status');
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public static function countCreatedThisMonth(): int
    {
        return (int) self::db()->query(
            "SELECT COUNT(*) FROM news_articles WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())"
        )->fetchColumn();
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM news_articles WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findPublishedBySlug(string $slug): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM news_articles WHERE slug = :slug AND status = 'active' LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Génère un slug unique à partir du titre
     * (ex: "Nouvelle carte des bières" -> "nouvelle_carte_des_bieres"),
     * en évitant les collisions avec les slugs existants.
     */
    public static function generateUniqueSlug(string $title): string
    {
        $base = self::slugify($title);
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
        return trim($text, '_') ?: 'actualite';
    }
}
