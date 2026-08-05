<?php

namespace App\Models;

use App\Core\Model;

class GoogleReview extends Model
{
    protected static string $table = 'google_reviews';
    protected static bool $softDeletes = true;

    public static function latest(int $limit = 5): array
    {
        $stmt = self::db()->prepare('SELECT * FROM google_reviews WHERE deleted_at IS NULL ORDER BY published_at DESC LIMIT :limit');
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
