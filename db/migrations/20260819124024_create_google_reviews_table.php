<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class CreateGoogleReviewsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('google_reviews', [
            'id' => 'id',
            'signed' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        $table
            ->addColumn('author_name', 'string', ['limit' => 120])
            ->addColumn('rating', 'integer', ['signed' => false, 'limit' => MysqlAdapter::INT_TINY])
            ->addColumn('comment', 'text', ['null' => true])
            ->addColumn('published_at', 'datetime')
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->create();
    }
}
