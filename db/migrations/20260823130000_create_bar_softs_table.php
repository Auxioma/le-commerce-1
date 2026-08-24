<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateBarSoftsTable extends AbstractMigration
{
    public function change(): void
    {
        // Softs, cafés & boissons chaudes affichés sur /le-bar, gérés en CRUD
        // complet depuis /admin/bar (remplace la liste libre auparavant
        // stockée en une seule clé/valeur dans `settings`).
        $this->table('bar_softs', [
            'id' => 'id',
            'signed' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ])
            ->addColumn('name', 'string', ['limit' => 120])
            ->addColumn('display_order', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->create();
    }
}
