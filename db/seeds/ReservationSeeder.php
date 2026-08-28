<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `reservations`, identifiants inclus.
 */
final class ReservationSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `reservations` LIMIT 1')) {
            return;
        }

        $this->table('reservations')->insert([
            ['id' => 1, 'name' => 'Antoine Rousseau', 'phone' => '0678912345', 'email' => 'antoine.r@example.com', 'party_size' => 4, 'reservation_date' => '2026-08-21', 'reservation_time' => '19:30:00', 'note' => 'Anniversaire, si possible une table près de la fenêtre', 'status' => 'confirmee', 'created_at' => '2026-08-19 15:42:57', 'updated_at' => '2026-08-19 15:42:57', 'deleted_at' => null],
            ['id' => 2, 'name' => 'Léa Fontaine', 'phone' => '0656789012', 'email' => null, 'party_size' => 2, 'reservation_date' => '2026-08-20', 'reservation_time' => '20:00:00', 'note' => null, 'status' => 'confirmee', 'created_at' => '2026-08-19 15:42:57', 'updated_at' => '2026-08-24 21:26:53', 'deleted_at' => null],
            ['id' => 3, 'name' => 'gogo', 'phone' => '0234779933', 'email' => 'toto@free.fr', 'party_size' => 2, 'reservation_date' => '2026-08-28', 'reservation_time' => '21:08:00', 'note' => 'qQsqS', 'status' => 'confirmee', 'created_at' => '2026-08-28 21:07:59', 'updated_at' => '2026-08-28 21:08:06', 'deleted_at' => null],
        ])->saveData();
    }
}
