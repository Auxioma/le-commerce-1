<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class LotterySeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM lotteries LIMIT 1')) {
            return;
        }

        $this->table('lotteries')->insert([
            [
                'title' => 'Tirage de rentrée',
                'description' => 'Participez pour tenter de remporter un panier gourmand.',
                'prize' => 'Panier gourmand (valeur 40€)',
                'ends_at' => date('Y-m-d', strtotime('+14 days')),
                'status' => 'active',
            ],
        ])->saveData();
    }
}
