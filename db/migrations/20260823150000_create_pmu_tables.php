<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePmuTables extends AbstractMigration
{
    public function change(): void
    {
        $commonOpts = [
            'id' => 'id',
            'signed' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ];

        // Catégories de paris affichées sur /pmu, gérées en CRUD complet
        // depuis /admin/pmu (remplace le catalogue auparavant figé dans
        // PmuController).
        $this->table('pmu_categories', $commonOpts)
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('icon', 'string', ['limit' => 1000, 'default' => 'M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0-6a9 9 0 100 18 9 9 0 000-18z'])
            ->addColumn('image', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('display_order', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('status', 'enum', ['values' => ['active', 'inactif'], 'default' => 'active'])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->create();

        // Ce que l'on trouve en boutique (services PMU), simple liste ordonnée
        // affichée sur /pmu.
        $this->table('pmu_services', $commonOpts)
            ->addColumn('name', 'string', ['limit' => 160])
            ->addColumn('display_order', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->create();
    }
}
