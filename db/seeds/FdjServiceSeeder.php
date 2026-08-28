<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `fdj_services`, identifiants inclus.
 */
final class FdjServiceSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `fdj_services` LIMIT 1')) {
            return;
        }

        $this->table('fdj_services')->insert([
            ['id' => 1, 'name' => 'Vérification de vos tickets gagnants en caisse', 'display_order' => 1, 'created_at' => '2026-08-23 13:37:09'],
            ['id' => 2, 'name' => 'Suivi des jackpots en cours et des prochains tirages', 'display_order' => 2, 'created_at' => '2026-08-23 13:37:09'],
            ['id' => 3, 'name' => 'Retrait des gains jusqu\'au montant autorisé en boutique', 'display_order' => 3, 'created_at' => '2026-08-23 13:37:09'],
            ['id' => 4, 'name' => 'Conseils sur les jeux et abonnements', 'display_order' => 4, 'created_at' => '2026-08-23 13:37:09'],
        ])->saveData();
    }
}
