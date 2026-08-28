<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `tabac_categories`, identifiants inclus.
 */
final class TabacCategorySeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `tabac_categories` LIMIT 1')) {
            return;
        }

        $this->table('tabac_categories')->insert([
            ['id' => 1, 'name' => 'Cigarettes & tabac à rouler', 'description' => 'Les grandes marques de cigarettes et de tabac à rouler, au prix officiel en vigueur.', 'icon' => 'M9 3v2m6-2v2M4 7h16M5 7h14v12a2 2 0 01-2 2H7a2 2 0 01-2-2V7z', 'image' => '/uploads/images/tabac_categorie_e46c7ca177dc.png', 'display_order' => 1, 'status' => 'active', 'created_at' => '2026-08-23 12:58:30', 'updated_at' => '2026-08-24 10:14:16'],
            ['id' => 2, 'name' => 'Cigares & cigarillos', 'description' => 'Une sélection de cigares et cigarillos pour les amateurs, à l\'unité ou en boîte.', 'icon' => 'M4 12h16M4 12a2 2 0 100 4h10a2 2 0 100-4M4 12a2 2 0 110-4h10a2 2 0 110 4', 'image' => null, 'display_order' => 2, 'status' => 'active', 'created_at' => '2026-08-23 12:58:30', 'updated_at' => '2026-08-23 12:58:30'],
            ['id' => 3, 'name' => 'Cigarette électronique', 'description' => 'E-cigarettes, résistances et e-liquides dans un large choix de saveurs et de dosages.', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'image' => null, 'display_order' => 3, 'status' => 'active', 'created_at' => '2026-08-23 12:58:30', 'updated_at' => '2026-08-23 12:58:30'],
            ['id' => 4, 'name' => 'Papiers, filtres & accessoires', 'description' => 'Papiers à rouler, filtres, briquets, blagues à tabac et boîtes de rangement.', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l7-3 7 3z', 'image' => null, 'display_order' => 4, 'status' => 'active', 'created_at' => '2026-08-23 12:58:30', 'updated_at' => '2026-08-23 12:58:30'],
        ])->saveData();
    }
}
