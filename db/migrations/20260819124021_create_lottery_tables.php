<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateLotteryTables extends AbstractMigration
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

        $this->table('lotteries', $commonOpts)
            ->addColumn('title', 'string', ['limit' => 150])
            ->addColumn('description', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('prize', 'string', ['limit' => 150])
            ->addColumn('ends_at', 'date')
            ->addColumn('status', 'enum', ['values' => ['brouillon', 'active', 'terminee'], 'default' => 'brouillon'])
            ->addColumn('winner_user_id', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('drawn_at', 'datetime', ['null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addForeignKey('winner_user_id', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
            ->create();

        $this->table('lottery_entries', $commonOpts)
            ->addColumn('lottery_id', 'integer', ['signed' => false])
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('code', 'string', ['limit' => 30])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['code'], ['unique' => true])
            ->addIndex(['lottery_id', 'user_id'], ['unique' => true, 'name' => 'uniq_lottery_user'])
            ->addForeignKey('lottery_id', 'lotteries', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();
    }
}
