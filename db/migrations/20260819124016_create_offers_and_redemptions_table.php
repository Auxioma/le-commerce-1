<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateOffersAndRedemptionsTable extends AbstractMigration
{
    public function change(): void
    {
        $offers = $this->table('offers', [
            'id' => 'id',
            'signed' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        $offers
            ->addColumn('title', 'string', ['limit' => 150])
            ->addColumn('description', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('type', 'enum', [
                'values' => ['gratuite', 'reduction_pourcentage', 'x_plus_1', 'montant_minimum', 'personnalisee'],
            ])
            ->addColumn('value', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'comment' => 'ex: 20 pour -20%'])
            ->addColumn('target_segment', 'enum', [
                'values' => ['tous', 'fideles', 'nouveaux', 'occasionnels'],
                'default' => 'tous',
            ])
            ->addColumn('valid_until', 'date')
            ->addColumn('status', 'enum', ['values' => ['active', 'expiree', 'brouillon'], 'default' => 'active'])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->create();

        $redemptions = $this->table('offer_redemptions', [
            'id' => 'id',
            'signed' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        $redemptions
            ->addColumn('offer_id', 'integer', ['signed' => false])
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('code', 'string', ['limit' => 30])
            ->addColumn('channel', 'enum', [
                'values' => ['whatsapp', 'qr_caisse', 'sms', 'email'],
                'default' => 'qr_caisse',
            ])
            ->addColumn('status', 'enum', ['values' => ['valide', 'utilisee', 'expiree'], 'default' => 'valide'])
            ->addColumn('used_at', 'datetime', ['null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['code'], ['unique' => true])
            ->addForeignKey('offer_id', 'offers', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();
    }
}
