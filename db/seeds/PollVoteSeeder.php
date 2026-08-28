<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `poll_votes`, identifiants inclus.
 */
final class PollVoteSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['PollSeeder', 'PollOptionSeeder', 'UserSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `poll_votes` LIMIT 1')) {
            return;
        }

        $this->table('poll_votes')->insert([
            ['id' => 1, 'poll_id' => 1, 'option_id' => 1, 'user_id' => 3, 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 2, 'poll_id' => 2, 'option_id' => 5, 'user_id' => 4, 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 3, 'poll_id' => 2, 'option_id' => 5, 'user_id' => 12, 'created_at' => '2026-08-27 22:42:52'],
            ['id' => 4, 'poll_id' => 5, 'option_id' => 14, 'user_id' => 13, 'created_at' => '2026-08-28 12:35:51'],
        ])->saveData();
    }
}
