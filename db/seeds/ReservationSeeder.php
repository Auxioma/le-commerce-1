<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class ReservationSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM reservations LIMIT 1')) {
            return;
        }

        $in = fn(int $days) => date('Y-m-d', strtotime("+{$days} days"));

        $this->table('reservations')->insert([
            ['name' => 'Antoine Rousseau', 'phone' => '0678912345', 'email' => 'antoine.r@example.com', 'party_size' => 4, 'reservation_date' => $in(2), 'reservation_time' => '19:30:00', 'note' => 'Anniversaire, si possible une table près de la fenêtre', 'status' => 'confirmee'],
            ['name' => 'Léa Fontaine', 'phone' => '0656789012', 'email' => null, 'party_size' => 2, 'reservation_date' => $in(1), 'reservation_time' => '20:00:00', 'note' => null, 'status' => 'en_attente'],
        ])->saveData();
    }
}
