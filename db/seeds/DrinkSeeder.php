<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `drinks`, identifiants inclus.
 */
final class DrinkSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `drinks` LIMIT 1')) {
            return;
        }

        $this->table('drinks')->insert([
            ['id' => 1, 'name' => 'Leffe Blonde', 'category' => 'biere_blonde', 'degree' => '6.6', 'image' => null, 'price' => null, 'display_order' => 1, 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 2, 'name' => 'Chimay Bleue', 'category' => 'biere_brune', 'degree' => '9.0', 'image' => null, 'price' => null, 'display_order' => 2, 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 3, 'name' => 'La Paix Dieu', 'category' => 'biere_ambree', 'degree' => '10.0', 'image' => null, 'price' => null, 'display_order' => 3, 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 4, 'name' => 'Rince Cochon', 'category' => 'biere_ambree', 'degree' => '8.5', 'image' => null, 'price' => null, 'display_order' => 4, 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 5, 'name' => 'Corbeau 9°', 'category' => 'biere_brune', 'degree' => '9.0', 'image' => null, 'price' => null, 'display_order' => 5, 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 6, 'name' => 'Chouffe Rouge', 'category' => 'biere_ambree', 'degree' => '8.0', 'image' => null, 'price' => null, 'display_order' => 6, 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 7, 'name' => 'Liefumme Pêche', 'category' => 'biere_blonde', 'degree' => '5.2', 'image' => null, 'price' => null, 'display_order' => 7, 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 8, 'name' => 'Liefmans Pêche', 'category' => 'biere_brune', 'degree' => '5.0', 'image' => null, 'price' => null, 'display_order' => 8, 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 9, 'name' => 'Grimbergen Blonde', 'category' => 'biere_blonde', 'degree' => '6.7', 'image' => null, 'price' => null, 'display_order' => 9, 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 10, 'name' => 'Brooklyn Lager', 'category' => 'biere_ambree', 'degree' => '5.2', 'image' => null, 'price' => null, 'display_order' => 10, 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 14, 'name' => 'toto', 'category' => 'biere_brune', 'degree' => '10.0', 'image' => null, 'price' => '10.00', 'display_order' => 9, 'created_at' => '2026-08-23 12:37:55'],
        ])->saveData();
    }
}
