<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `employees`, identifiants inclus.
 */
final class EmployeeSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['UserSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `employees` LIMIT 1')) {
            return;
        }

        $this->table('employees')->insert([
            ['id' => 1, 'user_id' => null, 'first_name' => 'Marie', 'last_name' => 'Lefevre', 'email' => 'marie.lefevre@lecommerce.fr', 'phone' => '0612345601', 'role' => 'Responsable de salle', 'status' => 'actif', 'hired_at' => '2023-03-01', 'created_at' => '2026-08-19 15:42:57', 'updated_at' => '2026-08-19 15:42:57', 'deleted_at' => null],
            ['id' => 2, 'user_id' => null, 'first_name' => 'Karim', 'last_name' => 'Benali', 'email' => 'karim.benali@lecommerce.fr', 'phone' => '0612345602', 'role' => 'Caissier', 'status' => 'actif', 'hired_at' => '2024-01-15', 'created_at' => '2026-08-19 15:42:57', 'updated_at' => '2026-08-19 15:42:57', 'deleted_at' => null],
            ['id' => 3, 'user_id' => null, 'first_name' => 'Julie', 'last_name' => 'Moreau', 'email' => null, 'phone' => '0612345603', 'role' => 'Serveuse', 'status' => 'inactif', 'hired_at' => '2022-09-10', 'created_at' => '2026-08-19 15:42:57', 'updated_at' => '2026-08-19 15:42:57', 'deleted_at' => null],
        ])->saveData();
    }
}
