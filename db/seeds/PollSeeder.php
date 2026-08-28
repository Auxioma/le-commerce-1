<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `polls`, identifiants inclus.
 */
final class PollSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `polls` LIMIT 1')) {
            return;
        }

        $this->table('polls')->insert([
            ['id' => 1, 'question' => 'Quelle bière souhaitez-vous en pression ?', 'description' => 'Choisissez votre bière préférée que vous aimeriez voir prochainement en pression.', 'image' => null, 'ends_at' => '2026-09-03', 'status' => 'actif', 'reward_type' => 'points', 'reward_value' => '10.00', 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 2, 'question' => 'Quel match voulez-vous voir ?', 'description' => 'Votez pour les matchs que vous souhaitez que nous diffusions au bar.', 'image' => null, 'ends_at' => '2026-08-29', 'status' => 'actif', 'reward_type' => 'credit', 'reward_value' => '0.50', 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 3, 'question' => 'Quelle ambiance musicale préférez-vous ?', 'description' => 'Aidez-nous à choisir l\'ambiance musicale de nos prochaines soirées.', 'image' => null, 'ends_at' => '2026-08-24', 'status' => 'actif', 'reward_type' => 'tirage_sort', 'reward_value' => null, 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 4, 'question' => 'Quelle planche aimeriez-vous découvrir ?', 'description' => 'Quel type de planche aimeriez-vous voir dans notre carte ?', 'image' => null, 'ends_at' => '2026-08-17', 'status' => 'termine', 'reward_type' => 'points', 'reward_value' => '5.00', 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 5, 'question' => 'yyy', 'description' => 'yyyyyyyyy', 'image' => null, 'ends_at' => '2026-08-30', 'status' => 'actif', 'reward_type' => 'credit', 'reward_value' => '10.00', 'created_at' => '2026-08-28 12:35:28'],
        ])->saveData();
    }
}
