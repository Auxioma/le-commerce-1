<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Ajoute un niveau « galerie » (album) entre les catégories et les photos :
 * catégorie (Au bar, Matchs...) -> galeries (« Soirée match OM-PSG »...) -> photos.
 * Les photos existantes sont regroupées dans une galerie par catégorie, puis
 * la catégorie n'est plus portée que par la galerie.
 */
final class CreateGalleriesTable extends AbstractMigration
{
    private const DEFAULT_TITLES = [
        'au-bar'  => 'Ambiance au bar',
        'matchs'  => 'Soirées match',
        'soirees' => 'Nos soirées',
    ];

    public function up(): void
    {
        $this->table('galleries', [
            'id' => 'id',
            'signed' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ])
            ->addColumn('title', 'string', ['limit' => 120])
            ->addColumn('slug', 'string', ['limit' => 140])
            ->addColumn('description', 'string', ['limit' => 300, 'null' => true])
            ->addColumn('category_id', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('event_date', 'date')
            ->addColumn('status', 'enum', ['values' => ['active', 'inactif'], 'default' => 'active'])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('category_id', 'gallery_categories', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
            ->addIndex(['slug'], ['unique' => true])
            ->addIndex(['status', 'event_date'])
            ->create();

        $this->table('gallery_photos')
            ->addColumn('gallery_id', 'integer', ['signed' => false, 'null' => true, 'after' => 'id'])
            ->update();

        // Une galerie par catégorie existante (ou une galerie « sans catégorie »).
        $groups = $this->fetchAll(
            'SELECT p.category_id, c.name, c.slug, MAX(p.taken_at) AS last_date
             FROM gallery_photos p LEFT JOIN gallery_categories c ON c.id = p.category_id
             GROUP BY p.category_id, c.name, c.slug'
        );
        $now = date('Y-m-d H:i:s');
        foreach ($groups as $group) {
            $slug  = $group['slug'] ?? 'photos-du-commerce';
            $title = self::DEFAULT_TITLES[$slug] ?? ($group['name'] ?? 'Photos du Commerce');
            $this->table('galleries')->insert([
                'title'       => $title,
                'slug'        => $slug,
                'category_id' => $group['category_id'],
                'event_date'  => $group['last_date'],
                'status'      => 'active',
                'created_at'  => $now,
                'updated_at'  => $now,
            ])->saveData();

            $galleryId = (int) $this->getAdapter()->getConnection()->lastInsertId();
            $where = $group['category_id'] === null ? 'category_id IS NULL' : 'category_id = ' . (int) $group['category_id'];
            $this->execute("UPDATE gallery_photos SET gallery_id = {$galleryId} WHERE {$where}");
        }

        $this->table('gallery_photos')
            ->dropForeignKey('category_id')
            ->update();
        $this->table('gallery_photos')
            ->removeColumn('category_id')
            ->changeColumn('gallery_id', 'integer', ['signed' => false, 'null' => false])
            ->addForeignKey('gallery_id', 'galleries', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->update();
    }

    public function down(): void
    {
        $this->table('gallery_photos')
            ->addColumn('category_id', 'integer', ['signed' => false, 'null' => true, 'after' => 'image'])
            ->update();
        $this->execute(
            'UPDATE gallery_photos p JOIN galleries g ON g.id = p.gallery_id SET p.category_id = g.category_id'
        );
        $this->table('gallery_photos')
            ->dropForeignKey('gallery_id')
            ->update();
        $this->table('gallery_photos')
            ->removeColumn('gallery_id')
            ->addForeignKey('category_id', 'gallery_categories', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
            ->update();
        $this->table('galleries')->drop()->save();
    }
}
