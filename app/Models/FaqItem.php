<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class FaqItem extends Model
{
    protected static string $table = 'faq_items';

    /**
     * Questions publiées, dans l'ordre d'affichage.
     */
    public static function published(): array
    {
        return self::db()->query(
            "SELECT id, category, question, answer
             FROM faq_items
             WHERE is_published = 1
             ORDER BY sort_order ASC, id ASC"
        )->fetchAll();
    }

    /**
     * Mêmes questions, regroupées par catégorie (l'ordre des catégories suit
     * la première question rencontrée) : ['Catégorie' => [ {question}, ... ]].
     *
     * @return array<string, list<array<string,mixed>>>
     */
    public static function groupedByCategory(): array
    {
        $groups = [];
        foreach (self::published() as $item) {
            $groups[$item['category']][] = $item;
        }
        return $groups;
    }
}
