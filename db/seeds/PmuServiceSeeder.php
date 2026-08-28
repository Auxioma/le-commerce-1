<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `pmu_services`, identifiants inclus.
 */
final class PmuServiceSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `pmu_services` LIMIT 1')) {
            return;
        }

        $this->table('pmu_services')->insert([
            ['id' => 1, 'name' => 'Toutes les courses du jour, en France et à l\'international', 'display_order' => 1, 'created_at' => '2026-08-23 13:06:26'],
            ['id' => 2, 'name' => 'Retransmission des courses phares en boutique', 'display_order' => 2, 'created_at' => '2026-08-23 13:06:26'],
            ['id' => 3, 'name' => 'Retrait des gains en espèces directement en caisse', 'display_order' => 3, 'created_at' => '2026-08-23 13:06:26'],
            ['id' => 4, 'name' => 'Conseils et pronostics de nos équipes', 'display_order' => 4, 'created_at' => '2026-08-23 13:06:26'],
        ])->saveData();
    }
}
