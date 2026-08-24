<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateEmployeesTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('employees', [
            'id' => 'id',
            'signed' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        $table
            ->addColumn('user_id', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('first_name', 'string', ['limit' => 80])
            ->addColumn('last_name', 'string', ['limit' => 80])
            ->addColumn('email', 'string', ['limit' => 150, 'null' => true])
            ->addColumn('phone', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('role', 'string', ['limit' => 80])
            ->addColumn('status', 'enum', ['values' => ['actif', 'inactif'], 'default' => 'actif'])
            ->addColumn('hired_at', 'date', ['null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION', 'constraint' => 'fk_employees_user'])
            ->create();
    }
}
