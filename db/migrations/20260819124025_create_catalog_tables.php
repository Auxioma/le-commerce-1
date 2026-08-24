<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class CreateCatalogTables extends AbstractMigration
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

        // Bons plans (Happy Hour, promos du moment)
        $this->table('deals', $commonOpts)
            ->addColumn('title', 'string', ['limit' => 120])
            ->addColumn('subtitle', 'string', ['limit' => 160, 'null' => true])
            ->addColumn('starts_at', 'time', ['null' => true])
            ->addColumn('ends_at', 'time', ['null' => true])
            ->addColumn('active', 'boolean', ['default' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->create();

        // Boissons (bières à la carte)
        $this->table('drinks', $commonOpts)
            ->addColumn('name', 'string', ['limit' => 80])
            ->addColumn('category', 'enum', [
                'values' => ['biere_blonde', 'biere_brune', 'biere_ambree', 'autre'],
                'default' => 'biere_blonde',
            ])
            ->addColumn('degree', 'decimal', ['precision' => 3, 'scale' => 1, 'null' => true])
            ->addColumn('image', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('price', 'decimal', ['precision' => 5, 'scale' => 2, 'null' => true])
            ->addColumn('display_order', 'integer', ['signed' => false, 'limit' => MysqlAdapter::INT_SMALL, 'default' => 0])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->create();

        // Messages du formulaire de contact
        $this->table('contact_messages', $commonOpts)
            ->addColumn('name', 'string', ['limit' => 120])
            ->addColumn('email', 'string', ['limit' => 150])
            ->addColumn('subject', 'string', ['limit' => 160, 'null' => true])
            ->addColumn('message', 'text')
            ->addColumn('is_read', 'boolean', ['default' => false])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->create();

        // Catalogue "services du quotidien" (relais colis, factures, etc.)
        $this->table('services', $commonOpts)
            ->addColumn('slug', 'string', ['limit' => 60])
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('description', 'text', ['null' => true])
            // VARCHAR(1000) et non 255 (comme dans migration_lot18_services_catalog.sql) : certains
            // tracés SVG de démonstration dépassent 255 caractères (jusqu'à ~480), ce qui aurait fait
            // échouer l'INSERT d'origine si ce fichier avait jamais été exécuté tel quel.
            ->addColumn('icon', 'string', ['limit' => 1000, 'default' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'])
            ->addColumn('sort_order', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('status', 'enum', ['values' => ['active', 'inactif'], 'default' => 'active'])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['slug'], ['unique' => true])
            ->create();

        // Paramètres modifiables (clé/valeur), édités depuis /admin/parametres
        $this->table('settings', [
            'id' => false,
            'primary_key' => ['key'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ])
            ->addColumn('key', 'string', ['limit' => 60, 'null' => false])
            ->addColumn('value', 'text', ['null' => true])
            ->create();
    }
}
