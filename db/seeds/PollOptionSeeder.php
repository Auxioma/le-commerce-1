<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class PollOptionSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['PollSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM poll_options LIMIT 1')) {
            return;
        }

        $poll1 = (int) $this->fetchRow("SELECT id FROM polls WHERE question LIKE 'Quelle bière%' LIMIT 1")['id'];
        $poll2 = (int) $this->fetchRow("SELECT id FROM polls WHERE question LIKE 'Quel match%' LIMIT 1")['id'];
        $poll3 = (int) $this->fetchRow("SELECT id FROM polls WHERE question LIKE 'Quelle ambiance%' LIMIT 1")['id'];
        $poll4 = (int) $this->fetchRow("SELECT id FROM polls WHERE question LIKE 'Quelle planche%' LIMIT 1")['id'];

        $this->table('poll_options')->insert([
            ['poll_id' => $poll1, 'label' => 'Leffe', 'votes_count' => 12],
            ['poll_id' => $poll1, 'label' => 'Paix Dieu', 'votes_count' => 9],
            ['poll_id' => $poll1, 'label' => 'Triple Karmeliet', 'votes_count' => 5],
            ['poll_id' => $poll1, 'label' => 'Chouffe', 'votes_count' => 3],

            ['poll_id' => $poll2, 'label' => 'PSG - OM', 'votes_count' => 8],
            ['poll_id' => $poll2, 'label' => 'Real - Barça', 'votes_count' => 6],
            ['poll_id' => $poll2, 'label' => 'Finale Champions League', 'votes_count' => 4],

            ['poll_id' => $poll3, 'label' => 'Ambiance lounge', 'votes_count' => 3],
            ['poll_id' => $poll3, 'label' => 'Rock/Pop', 'votes_count' => 5],
            ['poll_id' => $poll3, 'label' => 'Musique française', 'votes_count' => 2],

            ['poll_id' => $poll4, 'label' => 'Planche fromage', 'votes_count' => 14],
            ['poll_id' => $poll4, 'label' => 'Planche charcuterie', 'votes_count' => 18],
            ['poll_id' => $poll4, 'label' => 'Planche mixte', 'votes_count' => 22],
        ])->saveData();
    }
}
