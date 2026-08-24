<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class DrinkSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM drinks LIMIT 1')) {
            return;
        }

        $this->table('drinks')->insert([
            ['name' => 'Leffe Blonde', 'category' => 'biere_blonde', 'degree' => 6.6, 'display_order' => 1],
            ['name' => 'Chimay Bleue', 'category' => 'biere_brune', 'degree' => 9.0, 'display_order' => 2],
            ['name' => 'La Paix Dieu', 'category' => 'biere_ambree', 'degree' => 10.0, 'display_order' => 3],
            ['name' => 'Rince Cochon', 'category' => 'biere_ambree', 'degree' => 8.5, 'display_order' => 4],
            ['name' => 'Corbeau 9°', 'category' => 'biere_brune', 'degree' => 9.0, 'display_order' => 5],
            ['name' => 'Chouffe Rouge', 'category' => 'biere_ambree', 'degree' => 8.0, 'display_order' => 6],
            ['name' => 'Liefumme Pêche', 'category' => 'biere_blonde', 'degree' => 5.2, 'display_order' => 7],
            ['name' => 'Liefmans Pêche', 'category' => 'biere_brune', 'degree' => 5.0, 'display_order' => 8],
            ['name' => 'Grimbergen Blonde', 'category' => 'biere_blonde', 'degree' => 6.7, 'display_order' => 9],
            ['name' => 'Brooklyn Lager', 'category' => 'biere_ambree', 'degree' => 5.2, 'display_order' => 10],
        ])->saveData();
    }
}
