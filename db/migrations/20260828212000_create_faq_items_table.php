<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateFaqItemsTable extends AbstractMigration
{
    public function change(): void
    {
        // Foire aux questions affichée dans l'espace client (/mon-compte/aide).
        // Contenu 100 % en base : question / réponse, regroupées par catégorie
        // et triées par sort_order.
        $this->table('faq_items', [
            'id' => 'id',
            'signed' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ])
            ->addColumn('category', 'string', ['limit' => 80, 'default' => 'Général'])
            ->addColumn('question', 'string', ['limit' => 255])
            ->addColumn('answer', 'text')
            ->addColumn('sort_order', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('is_published', 'boolean', ['default' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['is_published', 'sort_order'])
            ->create();
    }
}
