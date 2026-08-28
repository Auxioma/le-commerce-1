<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `user_locations`, identifiants inclus.
 */
final class UserLocationSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['UserSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `user_locations` LIMIT 1')) {
            return;
        }

        $this->table('user_locations')->insert([
            ['id' => 1, 'user_id' => 9, 'latitude' => '49.5195390', 'longitude' => '0.1164650', 'updated_at' => '2026-08-26 23:57:50'],
            ['id' => 8, 'user_id' => 11, 'latitude' => '49.5195390', 'longitude' => '0.1164650', 'updated_at' => '2026-08-27 08:53:43'],
            ['id' => 33, 'user_id' => 12, 'latitude' => '49.5195390', 'longitude' => '0.1164650', 'updated_at' => '2026-08-27 22:43:32'],
            ['id' => 41, 'user_id' => 16, 'latitude' => '49.5197260', 'longitude' => '0.1162530', 'updated_at' => '2026-08-28 13:31:20'],
            ['id' => 50, 'user_id' => 17, 'latitude' => '49.5195390', 'longitude' => '0.1164650', 'updated_at' => '2026-08-28 21:36:32'],
        ])->saveData();
    }
}
