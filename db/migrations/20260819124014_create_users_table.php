<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsersTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('users', [
            'id' => 'id',
            'signed' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        $table
            ->addColumn('first_name', 'string', ['limit' => 80])
            ->addColumn('last_name', 'string', ['limit' => 80])
            ->addColumn('phone_whatsapp', 'string', ['limit' => 20])
            ->addColumn('email', 'string', ['limit' => 150, 'null' => true])
            ->addColumn('password_hash', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('role', 'enum', ['values' => ['client', 'admin', 'employe'], 'default' => 'client'])
            ->addColumn('segment', 'enum', ['values' => ['nouveau', 'fidele', 'occasionnel'], 'default' => 'nouveau'])
            ->addColumn('status', 'enum', ['values' => ['actif', 'inactif'], 'default' => 'actif'])
            ->addColumn('loyalty_points', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('referral_code', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('referred_by', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('geolocation_opt_in', 'boolean', ['default' => false])
            ->addColumn('registration_source', 'enum', [
                'values' => ['bar', 'tabac', 'jeux_services', 'pmu', 'nirio'],
                'default' => 'bar',
            ])
            ->addColumn('last_activity_at', 'datetime', ['null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addIndex(['phone_whatsapp'], ['unique' => true])
            ->addIndex(['referral_code'], ['unique' => true])
            ->addForeignKey('referred_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
            ->create();
    }
}
