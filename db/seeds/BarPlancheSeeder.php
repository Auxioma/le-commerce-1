<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `bar_planches`, identifiants inclus.
 */
final class BarPlancheSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `bar_planches` LIMIT 1')) {
            return;
        }

        $this->table('bar_planches')->insert([
            ['id' => 1, 'name' => 'Planche à saucisson', 'description' => 'Saucisson sec, cornichons, fromage et pain frais.', 'price' => '8.50', 'image' => '/uploads/images/bar_planche_b51a7d96a1fd.png', 'display_order' => 1, 'status' => 'active', 'created_at' => '2026-08-23 12:41:24', 'updated_at' => '2026-08-23 12:48:14'],
            ['id' => 2, 'name' => 'Planche mixte', 'description' => 'Charcuterie, fromage et crudités de saison.', 'price' => '11.00', 'image' => null, 'display_order' => 2, 'status' => 'active', 'created_at' => '2026-08-23 12:41:24', 'updated_at' => '2026-08-23 12:41:24'],
            ['id' => 3, 'name' => 'Planche fromage', 'description' => 'Sélection de fromages affinés et pain de campagne.', 'price' => '9.00', 'image' => null, 'display_order' => 3, 'status' => 'active', 'created_at' => '2026-08-23 12:41:24', 'updated_at' => '2026-08-23 12:41:24'],
            ['id' => 7, 'name' => 'jnjn', 'description' => ';,,;,m', 'price' => '10.00', 'image' => '/uploads/images/bar_planche_7b8f208967df.png', 'display_order' => 4, 'status' => 'active', 'created_at' => '2026-08-24 10:13:44', 'updated_at' => '2026-08-24 10:13:44'],
        ])->saveData();
    }
}
