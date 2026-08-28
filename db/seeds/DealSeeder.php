<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `deals`, identifiants inclus.
 */
final class DealSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `deals` LIMIT 1')) {
            return;
        }

        $this->table('deals')->insert([
            ['id' => 1, 'title' => 'Happy Hour', 'subtitle' => 'La pinte de Leffe à 5,00 €', 'starts_at' => '17:00:00', 'ends_at' => '19:00:00', 'active' => 1, 'created_at' => '2026-08-19 15:42:57'],
        ])->saveData();
    }
}
