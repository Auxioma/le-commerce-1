<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateProximityCampaignsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('proximity_campaigns', [
            'id' => 'id',
            'signed' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        $table
            ->addColumn('name', 'string', ['limit' => 120])
            ->addColumn('radius_m', 'integer', ['signed' => false, 'default' => 500])
            ->addColumn('start_time', 'time')
            ->addColumn('end_time', 'time')
            ->addColumn('days', 'string', ['limit' => 40, 'comment' => 'ex: lun,mar,mer,jeu,ven'])
            ->addColumn('target_segment', 'enum', [
                'values' => ['tous', 'fideles', 'nouveaux', 'occasionnels'],
                'default' => 'tous',
            ])
            ->addColumn('offer_id', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('message', 'string', ['limit' => 160])
            ->addColumn('status', 'enum', ['values' => ['active', 'en_pause', 'terminee'], 'default' => 'active'])
            ->addColumn('sent_count', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('used_count', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('offer_id', 'offers', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
            ->create();
    }
}
