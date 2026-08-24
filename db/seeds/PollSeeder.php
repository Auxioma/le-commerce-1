<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class PollSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM polls LIMIT 1')) {
            return;
        }

        $in = fn(string $interval) => date('Y-m-d', strtotime("+{$interval}"));
        $ago = fn(string $interval) => date('Y-m-d', strtotime("-{$interval}"));

        $this->table('polls')->insert([
            ['question' => 'Quelle bière souhaitez-vous en pression ?', 'description' => 'Choisissez votre bière préférée que vous aimeriez voir prochainement en pression.', 'ends_at' => $in('15 days'), 'status' => 'actif', 'reward_type' => 'points', 'reward_value' => 10],
            ['question' => 'Quel match voulez-vous voir ?', 'description' => 'Votez pour les matchs que vous souhaitez que nous diffusions au bar.', 'ends_at' => $in('10 days'), 'status' => 'actif', 'reward_type' => 'credit', 'reward_value' => 0.50],
            ['question' => 'Quelle ambiance musicale préférez-vous ?', 'description' => "Aidez-nous à choisir l'ambiance musicale de nos prochaines soirées.", 'ends_at' => $in('5 days'), 'status' => 'actif', 'reward_type' => 'tirage_sort', 'reward_value' => null],
            ['question' => 'Quelle planche aimeriez-vous découvrir ?', 'description' => 'Quel type de planche aimeriez-vous voir dans notre carte ?', 'ends_at' => $ago('2 days'), 'status' => 'termine', 'reward_type' => 'points', 'reward_value' => 5],
        ])->saveData();
    }
}
