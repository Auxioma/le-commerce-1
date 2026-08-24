<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class GoogleReviewSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM google_reviews LIMIT 1')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $this->table('google_reviews')->insert([
            ['author_name' => 'Marie L.', 'rating' => 5, 'comment' => 'Excellent accueil, toujours un plaisir de venir prendre un café.', 'published_at' => $now],
            ['author_name' => 'Thomas B.', 'rating' => 5, 'comment' => 'Le meilleur bar-tabac du coin, service au top.', 'published_at' => $now],
        ])->saveData();
    }
}
