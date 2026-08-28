<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `wallets`, identifiants inclus.
 */
final class WalletSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['UserSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `wallets` LIMIT 1')) {
            return;
        }

        $this->table('wallets')->insert([
            ['id' => 1, 'user_id' => 1, 'balance' => '58.40', 'qr_code' => 'QR-JEAN-MARTIN-001', 'created_at' => '2026-08-19 15:42:57', 'updated_at' => '2026-08-19 15:42:57'],
            ['id' => 2, 'user_id' => 2, 'balance' => '43.80', 'qr_code' => 'QR-SOPHIE-PETIT-002', 'created_at' => '2026-08-19 15:42:57', 'updated_at' => '2026-08-19 15:42:57'],
            ['id' => 3, 'user_id' => 3, 'balance' => '47.30', 'qr_code' => 'QR-LUCAS-DUBOIS-003', 'created_at' => '2026-08-19 15:42:57', 'updated_at' => '2026-08-19 15:42:57'],
            ['id' => 4, 'user_id' => 4, 'balance' => '32.15', 'qr_code' => 'QR-CLAIRE-BERNARD-004', 'created_at' => '2026-08-19 15:42:57', 'updated_at' => '2026-08-19 15:42:57'],
            ['id' => 6, 'user_id' => 8, 'balance' => '0.00', 'qr_code' => 'QR-2122E0524496', 'created_at' => '2026-08-23 13:58:23', 'updated_at' => '2026-08-23 13:58:23'],
            ['id' => 7, 'user_id' => 9, 'balance' => '0.00', 'qr_code' => 'QR-C131D4B25CB7', 'created_at' => '2026-08-26 23:55:19', 'updated_at' => '2026-08-26 23:55:19'],
            ['id' => 9, 'user_id' => 11, 'balance' => '0.00', 'qr_code' => 'QR-A2BC4BEA44E0', 'created_at' => '2026-08-27 08:27:38', 'updated_at' => '2026-08-27 08:27:38'],
            ['id' => 10, 'user_id' => 12, 'balance' => '0.50', 'qr_code' => 'QR-40B0A2D11850', 'created_at' => '2026-08-27 22:41:41', 'updated_at' => '2026-08-27 22:42:52'],
            ['id' => 11, 'user_id' => 13, 'balance' => '60.30', 'qr_code' => 'QR-DA75279E7B99', 'created_at' => '2026-08-28 12:22:37', 'updated_at' => '2026-08-28 12:35:51'],
            ['id' => 12, 'user_id' => 14, 'balance' => '0.00', 'qr_code' => 'QR-67F7E6D6313E', 'created_at' => '2026-08-28 12:41:07', 'updated_at' => '2026-08-28 12:41:07'],
            ['id' => 13, 'user_id' => 15, 'balance' => '0.00', 'qr_code' => 'QR-9688D69149AC', 'created_at' => '2026-08-28 12:42:29', 'updated_at' => '2026-08-28 12:42:29'],
            ['id' => 14, 'user_id' => 16, 'balance' => '0.00', 'qr_code' => 'QR-165B8DDFD4A1', 'created_at' => '2026-08-28 12:58:52', 'updated_at' => '2026-08-28 12:58:52'],
            ['id' => 15, 'user_id' => 17, 'balance' => '0.00', 'qr_code' => 'QR-27101E41ECC4', 'created_at' => '2026-08-28 21:12:32', 'updated_at' => '2026-08-28 21:12:32'],
        ])->saveData();
    }
}
