<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePollsTables extends AbstractMigration
{
    public function change(): void
    {
        $polls = $this->table('polls', [
            'id' => 'id',
            'signed' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        $polls
            ->addColumn('question', 'string', ['limit' => 180])
            ->addColumn('description', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('image', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('ends_at', 'date')
            ->addColumn('status', 'enum', ['values' => ['actif', 'programme', 'termine'], 'default' => 'actif'])
            ->addColumn('reward_type', 'enum', [
                'values' => ['points', 'credit', 'tirage_sort', 'aucune'],
                'default' => 'points',
            ])
            ->addColumn('reward_value', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->create();

        $options = $this->table('poll_options', [
            'id' => 'id',
            'signed' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        $options
            ->addColumn('poll_id', 'integer', ['signed' => false])
            ->addColumn('label', 'string', ['limit' => 120])
            ->addColumn('votes_count', 'integer', ['signed' => false, 'default' => 0])
            ->addForeignKey('poll_id', 'polls', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();

        $votes = $this->table('poll_votes', [
            'id' => 'id',
            'signed' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        $votes
            ->addColumn('poll_id', 'integer', ['signed' => false])
            ->addColumn('option_id', 'integer', ['signed' => false])
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['poll_id', 'user_id'], ['unique' => true, 'name' => 'uniq_vote'])
            ->addForeignKey('poll_id', 'polls', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addForeignKey('option_id', 'poll_options', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();
    }
}
