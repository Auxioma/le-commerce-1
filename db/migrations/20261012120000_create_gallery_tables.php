<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Galerie photos « Le Commerce en images » : bloc carrousel de la page
 * d'accueil + page publique /galerie, administrables depuis /admin/galerie.
 * Les catégories servent de filtres (Au bar, Matchs, Soirées...). Les
 * tables sont peuplées avec des photos d'exemple livrées dans
 * public/assets/images/gallery/ (à remplacer par les vraies photos).
 */
final class CreateGalleryTables extends AbstractMigration
{
    public function up(): void
    {
        $commonOpts = [
            'id' => 'id',
            'signed' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ];

        $this->table('gallery_categories', $commonOpts)
            ->addColumn('name', 'string', ['limit' => 60])
            ->addColumn('slug', 'string', ['limit' => 80])
            ->addColumn('display_order', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('status', 'enum', ['values' => ['active', 'inactif'], 'default' => 'active'])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['slug'], ['unique' => true])
            ->create();

        $this->table('gallery_photos', $commonOpts)
            ->addColumn('title', 'string', ['limit' => 120])
            ->addColumn('image', 'string', ['limit' => 255])
            ->addColumn('category_id', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('taken_at', 'date')
            ->addColumn('status', 'enum', ['values' => ['active', 'inactif'], 'default' => 'active'])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('category_id', 'gallery_categories', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
            ->addIndex(['status', 'taken_at'])
            ->create();

        $now = date('Y-m-d H:i:s');

        $categories = [
            ['name' => 'Au bar',  'slug' => 'au-bar'],
            ['name' => 'Matchs',  'slug' => 'matchs'],
            ['name' => 'Soirées', 'slug' => 'soirees'],
        ];
        $rows = [];
        foreach ($categories as $i => $category) {
            $rows[] = $category + ['display_order' => $i + 1, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now];
        }
        $this->table('gallery_categories')->insert($rows)->saveData();

        $ids = [];
        foreach ($this->fetchAll('SELECT id, slug FROM gallery_categories') as $row) {
            $ids[$row['slug']] = (int) $row['id'];
        }

        $photos = [
            ['Le bar',          'salle.jpg',      'au-bar',  '2026-10-12'],
            ['Bières',          'bieres.jpg',     'au-bar',  '2026-10-05'],
            ['Matchs',          'foot-ecran.jpg', 'matchs',  '2026-09-28'],
            ['Planche apéro',   'planche.jpg',    'au-bar',  '2026-09-20'],
            ['Café',            'cafe.jpg',       'au-bar',  '2026-09-15'],
            ['Jeux',            'jeux.jpg',       'soirees', '2026-09-10'],
            ['Soirées',         'soirees.jpg',    'soirees', '2026-09-01'],
            ['Le comptoir',     'comptoir.jpg',   'au-bar',  '2026-08-25'],
            ['Cocktails',       'cocktails.jpg',  'soirees', '2026-08-18'],
            ['Coup d\'envoi',   'matchs.jpg',     'matchs',  '2026-08-10'],
            ['Ambiance supporters', 'concert.jpg', 'matchs', '2026-08-02'],
            ['La salle',        'terrasse.jpg',   'au-bar',  '2026-07-20'],
        ];
        $rows = [];
        foreach ($photos as $i => [$title, $file, $slug, $date]) {
            $rows[] = [
                'title'         => $title,
                'image'         => '/assets/images/gallery/' . $file,
                'category_id'   => $ids[$slug] ?? null,
                'taken_at'      => $date,
                'status'        => 'active',
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        }
        $this->table('gallery_photos')->insert($rows)->saveData();
    }

    public function down(): void
    {
        $this->table('gallery_photos')->drop()->save();
        $this->table('gallery_categories')->drop()->save();
    }
}
