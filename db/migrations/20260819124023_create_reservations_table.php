<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateReservationsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('reservations', [
            'id' => 'id',
            'signed' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        $table
            ->addColumn('name', 'string', ['limit' => 120])
            ->addColumn('phone', 'string', ['limit' => 20])
            ->addColumn('email', 'string', ['limit' => 150, 'null' => true])
            ->addColumn('party_size', 'integer', ['signed' => false, 'limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'default' => 2])
            ->addColumn('reservation_date', 'date')
            ->addColumn('reservation_time', 'time')
            ->addColumn('note', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('status', 'enum', ['values' => ['en_attente', 'confirmee', 'annulee'], 'default' => 'en_attente'])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->create();
    }
}
