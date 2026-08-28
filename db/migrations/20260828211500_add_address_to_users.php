<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddAddressToUsers extends AbstractMigration
{
    public function change(): void
    {
        $this->table('users')
            ->addColumn('address_line', 'string', ['limit' => 255, 'null' => true, 'after' => 'registration_source'])
            ->addColumn('postal_code', 'string', ['limit' => 10, 'null' => true, 'after' => 'address_line'])
            ->addColumn('city', 'string', ['limit' => 120, 'null' => true, 'after' => 'postal_code'])
            ->update();
    }
}
