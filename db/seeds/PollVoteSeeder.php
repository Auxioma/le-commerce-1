<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class PollVoteSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['PollOptionSeeder', 'UserSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM poll_votes LIMIT 1')) {
            return;
        }

        $poll1 = (int) $this->fetchRow("SELECT id FROM polls WHERE question LIKE 'Quelle bière%' LIMIT 1")['id'];
        $poll2 = (int) $this->fetchRow("SELECT id FROM polls WHERE question LIKE 'Quel match%' LIMIT 1")['id'];
        $leffeOption = (int) $this->fetchRow("SELECT id FROM poll_options WHERE poll_id = {$poll1} AND label = 'Leffe' LIMIT 1")['id'];
        $psgOption = (int) $this->fetchRow("SELECT id FROM poll_options WHERE poll_id = {$poll2} AND label = 'PSG - OM' LIMIT 1")['id'];

        $this->table('poll_votes')->insert([
            ['poll_id' => $poll1, 'option_id' => $leffeOption, 'user_id' => 3],
            ['poll_id' => $poll2, 'option_id' => $psgOption, 'user_id' => 4],
        ])->saveData();
    }
}
