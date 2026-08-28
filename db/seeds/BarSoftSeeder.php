<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `bar_softs`, identifiants inclus.
 */
final class BarSoftSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `bar_softs` LIMIT 1')) {
            return;
        }

        $this->table('bar_softs')->insert([
            ['id' => 1, 'name' => 'Coca-Cola', 'display_order' => 1, 'created_at' => '2026-08-23 12:49:45'],
            ['id' => 2, 'name' => 'Orangina', 'display_order' => 2, 'created_at' => '2026-08-23 12:49:45'],
            ['id' => 3, 'name' => 'Jus de fruits pressés', 'display_order' => 3, 'created_at' => '2026-08-23 12:49:45'],
            ['id' => 4, 'name' => 'Eaux plates & gazeuses', 'display_order' => 4, 'created_at' => '2026-08-23 12:49:45'],
            ['id' => 5, 'name' => 'Café, thé & chocolat chaud', 'display_order' => 5, 'created_at' => '2026-08-23 12:49:45'],
            ['id' => 7, 'name' => 'sdsqsqdsdq', 'display_order' => 6, 'created_at' => '2026-08-23 12:55:08'],
            ['id' => 8, 'name' => 'cocococococoo', 'display_order' => 7, 'created_at' => '2026-08-24 10:12:54'],
        ])->saveData();
    }
}
