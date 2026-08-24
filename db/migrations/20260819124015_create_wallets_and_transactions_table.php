<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateWalletsAndTransactionsTable extends AbstractMigration
{
    public function change(): void
    {
        $wallets = $this->table('wallets', [
            'id' => 'id',
            'signed' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        $wallets
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('balance', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0.00])
            ->addColumn('qr_code', 'string', ['limit' => 64])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['user_id'], ['unique' => true])
            ->addIndex(['qr_code'], ['unique' => true])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();

        $transactions = $this->table('wallet_transactions', [
            'id' => 'id',
            'signed' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        $transactions
            ->addColumn('wallet_id', 'integer', ['signed' => false])
            ->addColumn('type', 'enum', ['values' => ['recharge', 'debit', 'remboursement']])
            ->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2])
            ->addColumn('payment_method', 'enum', [
                'values' => ['carte_bancaire', 'especes', 'apple_pay', 'google_pay', 'portefeuille'],
            ])
            ->addColumn('status', 'enum', ['values' => ['reussi', 'echoue', 'en_attente'], 'default' => 'reussi'])
            ->addColumn('label', 'string', ['limit' => 120, 'null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('wallet_id', 'wallets', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();
    }
}
