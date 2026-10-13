<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Suivi des ouvertures de l'e-mail d'annonce des loteries : une ligne par
 * client et par loterie, alimentée par le pixel de suivi de l'e-mail
 * (voir EmailTrackingController).
 */
final class CreateLotteryEmailOpensTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('lottery_email_opens', [
            'id' => 'id',
            'signed' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ])
            ->addColumn('lottery_id', 'integer', ['signed' => false])
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('open_count', 'integer', ['signed' => false, 'default' => 1])
            ->addColumn('first_opened_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('last_opened_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['lottery_id', 'user_id'], ['unique' => true, 'name' => 'uniq_lottery_user'])
            ->addForeignKey('lottery_id', 'lotteries', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();
    }
}
