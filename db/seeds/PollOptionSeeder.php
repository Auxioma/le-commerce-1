<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `poll_options`, identifiants inclus.
 */
final class PollOptionSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['PollSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `poll_options` LIMIT 1')) {
            return;
        }

        $this->table('poll_options')->insert([
            ['id' => 1, 'poll_id' => 1, 'label' => 'Leffe', 'votes_count' => 12],
            ['id' => 2, 'poll_id' => 1, 'label' => 'Paix Dieu', 'votes_count' => 9],
            ['id' => 3, 'poll_id' => 1, 'label' => 'Triple Karmeliet', 'votes_count' => 5],
            ['id' => 4, 'poll_id' => 1, 'label' => 'Chouffe', 'votes_count' => 3],
            ['id' => 5, 'poll_id' => 2, 'label' => 'PSG - OM', 'votes_count' => 9],
            ['id' => 6, 'poll_id' => 2, 'label' => 'Real - Barça', 'votes_count' => 6],
            ['id' => 7, 'poll_id' => 2, 'label' => 'Finale Champions League', 'votes_count' => 4],
            ['id' => 8, 'poll_id' => 3, 'label' => 'Ambiance lounge', 'votes_count' => 3],
            ['id' => 9, 'poll_id' => 3, 'label' => 'Rock/Pop', 'votes_count' => 5],
            ['id' => 10, 'poll_id' => 3, 'label' => 'Musique française', 'votes_count' => 2],
            ['id' => 11, 'poll_id' => 4, 'label' => 'Planche fromage', 'votes_count' => 14],
            ['id' => 12, 'poll_id' => 4, 'label' => 'Planche charcuterie', 'votes_count' => 18],
            ['id' => 13, 'poll_id' => 4, 'label' => 'Planche mixte', 'votes_count' => 22],
            ['id' => 14, 'poll_id' => 5, 'label' => 'oui', 'votes_count' => 1],
            ['id' => 15, 'poll_id' => 5, 'label' => 'non', 'votes_count' => 0],
        ])->saveData();
    }
}
