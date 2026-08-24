<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class DealSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM deals LIMIT 1')) {
            return;
        }

        $this->table('deals')->insert([
            ['title' => 'Happy Hour', 'subtitle' => 'La pinte de Leffe à 5,00 €', 'starts_at' => '17:00:00', 'ends_at' => '19:00:00', 'active' => 1],
        ])->saveData();
    }
}
