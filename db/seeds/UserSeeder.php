<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class UserSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM users LIMIT 1')) {
            return;
        }

        // Mots de passe de démo (migration_lot2_auth.sql) : admin -> "admin123", clients -> "client123"
        $adminHash = '$2y$10$G/o10NdBzAm8fo3BmVaij.57vmcknkyD/r4fY9eUs9OOZkMGk6p1e';
        $clientHash = '$2y$10$V8.3Qjjplb4ptylpo1E.v.fqlNA1pGuYT8XOBcsc9qgdt54z.rBeW';

        $this->table('users')->insert([
            [
                'first_name' => 'Jean', 'last_name' => 'Martin', 'phone_whatsapp' => '0612345678',
                'email' => 'jean.martin@example.com', 'password_hash' => $clientHash,
                'role' => 'client', 'segment' => 'fidele', 'status' => 'actif',
                'loyalty_points' => 120, 'referral_code' => 'JEAN2024', 'geolocation_opt_in' => 1,
                'registration_source' => 'tabac',
            ],
            [
                'first_name' => 'Sophie', 'last_name' => 'Petit', 'phone_whatsapp' => '0723456789',
                'email' => 'sophie.petit@example.com', 'password_hash' => $clientHash,
                'role' => 'client', 'segment' => 'nouveau', 'status' => 'actif',
                'loyalty_points' => 20, 'referral_code' => 'SOPH2024', 'geolocation_opt_in' => 1,
                'registration_source' => 'bar',
            ],
            [
                'first_name' => 'Lucas', 'last_name' => 'Dubois', 'phone_whatsapp' => '0645678901',
                'email' => 'lucas.dubois@example.com', 'password_hash' => $clientHash,
                'role' => 'client', 'segment' => 'fidele', 'status' => 'actif',
                'loyalty_points' => 95, 'referral_code' => 'LUCA2024', 'geolocation_opt_in' => 0,
                'registration_source' => 'pmu',
            ],
            [
                'first_name' => 'Claire', 'last_name' => 'Bernard', 'phone_whatsapp' => '0789012345',
                'email' => 'claire.bernard@example.com', 'password_hash' => $clientHash,
                'role' => 'client', 'segment' => 'nouveau', 'status' => 'actif',
                'loyalty_points' => 15, 'referral_code' => 'CLAI2024', 'geolocation_opt_in' => 1,
                'registration_source' => 'nirio',
            ],
            [
                'first_name' => 'Admin', 'last_name' => 'Le Commerce', 'phone_whatsapp' => '0235905016',
                'email' => 'lecommercetabac@gmail.com', 'password_hash' => $adminHash,
                'role' => 'admin', 'segment' => 'fidele', 'status' => 'actif',
                'loyalty_points' => 0, 'referral_code' => null, 'geolocation_opt_in' => 0,
                'registration_source' => 'bar',
            ],
        ])->saveData();
    }
}
