<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class EmployeeSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM employees LIMIT 1')) {
            return;
        }

        $this->table('employees')->insert([
            ['first_name' => 'Marie', 'last_name' => 'Lefevre', 'email' => 'marie.lefevre@lecommerce.fr', 'phone' => '0612345601', 'role' => 'Responsable de salle', 'status' => 'actif', 'hired_at' => '2023-03-01'],
            ['first_name' => 'Karim', 'last_name' => 'Benali', 'email' => 'karim.benali@lecommerce.fr', 'phone' => '0612345602', 'role' => 'Caissier', 'status' => 'actif', 'hired_at' => '2024-01-15'],
            ['first_name' => 'Julie', 'last_name' => 'Moreau', 'email' => null, 'phone' => '0612345603', 'role' => 'Serveuse', 'status' => 'inactif', 'hired_at' => '2022-09-10'],
        ])->saveData();
    }
}
