<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `tabac_services`, identifiants inclus.
 */
final class TabacServiceSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `tabac_services` LIMIT 1')) {
            return;
        }

        $this->table('tabac_services')->insert([
            ['id' => 1, 'name' => 'Timbres fiscaux (amendes, passeport, titre de séjour...)', 'display_order' => 1, 'created_at' => '2026-08-23 12:58:30'],
            ['id' => 2, 'name' => 'Cartes prépayées de téléphonie mobile', 'display_order' => 2, 'created_at' => '2026-08-23 12:58:30'],
            ['id' => 3, 'name' => 'Recharges Paysafecard et Neosurf', 'display_order' => 3, 'created_at' => '2026-08-23 12:58:30'],
            ['id' => 4, 'name' => 'Vente de timbres postaux', 'display_order' => 4, 'created_at' => '2026-08-23 12:58:30'],
        ])->saveData();
    }
}
