<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `contact_messages`, identifiants inclus.
 */
final class ContactMessageSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['UserSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `contact_messages` LIMIT 1')) {
            return;
        }

        $this->table('contact_messages')->insert([
            ['id' => 1, 'user_id' => null, 'name' => 'test', 'email' => 'test@test.test', 'subject' => 'Réservation', 'message' => 'cscxwCxwc', 'ip' => null, 'is_read' => 1, 'created_at' => '2026-08-24 08:40:28'],
        ])->saveData();
    }
}
