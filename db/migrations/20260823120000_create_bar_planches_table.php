<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateBarPlanchesTable extends AbstractMigration
{
    public function change(): void
    {
        // Planches à partager affichées sur /le-bar, gérées en CRUD complet
        // depuis /admin/bar (remplace les 3 planches figées auparavant
        // stockées en clé/valeur dans `settings`).
        $this->table('bar_planches', [
            'id' => 'id',
            'signed' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ])
            ->addColumn('name', 'string', ['limit' => 120])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('price', 'decimal', ['precision' => 5, 'scale' => 2, 'null' => true])
            ->addColumn('image', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('display_order', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('status', 'enum', ['values' => ['active', 'inactif'], 'default' => 'active'])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->create();
    }
}
