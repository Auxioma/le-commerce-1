<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateNewsArticlesTable extends AbstractMigration
{
    public function change(): void
    {
        // Actualités du commerce (page publique /actualites), gérées depuis
        // /admin/actualites — liste de billets courts avec page dédiée par article.
        $this->table('news_articles', [
            'id' => 'id',
            'signed' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ])
            ->addColumn('slug', 'string', ['limit' => 160])
            ->addColumn('title', 'string', ['limit' => 160])
            ->addColumn('excerpt', 'string', ['limit' => 300, 'null' => true])
            ->addColumn('content', 'text')
            ->addColumn('image', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('status', 'enum', ['values' => ['active', 'inactif'], 'default' => 'inactif'])
            ->addColumn('published_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['slug'], ['unique' => true])
            ->addIndex(['status', 'published_at'])
            ->create();
    }
}
