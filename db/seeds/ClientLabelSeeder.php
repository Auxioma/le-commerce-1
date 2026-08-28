<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `client_labels`, identifiants inclus.
 */
final class ClientLabelSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['UserSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `client_labels` LIMIT 1')) {
            return;
        }

        $this->table('client_labels')->insert([
            ['id' => 1, 'user_id' => 1, 'label' => 'Client fidèle', 'color' => 'amber', 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 2, 'user_id' => 1, 'label' => 'Aime la bière', 'color' => 'blue', 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 3, 'user_id' => 3, 'label' => 'Client fidèle', 'color' => 'amber', 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 4, 'user_id' => 4, 'label' => 'Participe à la loterie', 'color' => 'purple', 'created_at' => '2026-08-19 15:42:57'],
        ])->saveData();
    }
}
