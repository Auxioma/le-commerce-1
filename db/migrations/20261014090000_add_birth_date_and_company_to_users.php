<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Informations nécessaires à la segmentation automatique des clients
 * (voir App\Service\ClientSegmentationService) :
 * - birth_date   -> segment « Client anniversaire »
 * - company_name -> segment « Client professionnel »
 */
final class AddBirthDateAndCompanyToUsers extends AbstractMigration
{
    public function change(): void
    {
        $this->table('users')
            ->addColumn('birth_date', 'date', ['null' => true, 'after' => 'last_name'])
            ->addColumn('company_name', 'string', ['limit' => 150, 'null' => true, 'after' => 'city'])
            ->update();
    }
}
