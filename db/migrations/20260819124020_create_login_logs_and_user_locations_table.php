<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateLoginLogsAndUserLocationsTable extends AbstractMigration
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

        // Journal des connexions (audit léger)
        $this->table('login_logs', $commonOpts)
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => true])
            ->addColumn('user_agent', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();

        // Dernière position GPS connue des clients (géolocalisation opt-in)
        $this->table('user_locations', $commonOpts)
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('latitude', 'decimal', ['precision' => 10, 'scale' => 7])
            ->addColumn('longitude', 'decimal', ['precision' => 10, 'scale' => 7])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['user_id'], ['unique' => true])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();
    }
}
