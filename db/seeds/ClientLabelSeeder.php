<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class ClientLabelSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['UserSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM client_labels LIMIT 1')) {
            return;
        }

        $this->table('client_labels')->insert([
            ['user_id' => 1, 'label' => 'Client fidèle', 'color' => 'amber'],
            ['user_id' => 1, 'label' => 'Aime la bière', 'color' => 'blue'],
            ['user_id' => 3, 'label' => 'Client fidèle', 'color' => 'amber'],
            ['user_id' => 4, 'label' => 'Participe à la loterie', 'color' => 'purple'],
        ])->saveData();
    }
}
