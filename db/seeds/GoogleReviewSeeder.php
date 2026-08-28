<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `google_reviews`, identifiants inclus.
 */
final class GoogleReviewSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `google_reviews` LIMIT 1')) {
            return;
        }

        $this->table('google_reviews')->insert([
            ['id' => 1, 'author_name' => 'Marie L.', 'rating' => 5, 'comment' => 'Excellent accueil, toujours un plaisir de venir prendre un café.', 'published_at' => '2026-08-19 13:42:57', 'created_at' => '2026-08-19 15:42:57', 'deleted_at' => null],
            ['id' => 2, 'author_name' => 'Thomas B.', 'rating' => 5, 'comment' => 'Le meilleur bar-tabac du coin, service au top.', 'published_at' => '2026-08-19 13:42:57', 'created_at' => '2026-08-19 15:42:57', 'deleted_at' => null],
        ])->saveData();
    }
}
