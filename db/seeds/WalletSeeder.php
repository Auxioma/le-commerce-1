<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class WalletSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['UserSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM wallets LIMIT 1')) {
            return;
        }

        // Soldes finaux tels que fixés par migration_lot5_demo_data.sql (UPDATE après les transactions de démo)
        $this->table('wallets')->insert([
            ['user_id' => 1, 'balance' => 58.40, 'qr_code' => 'QR-JEAN-MARTIN-001'],
            ['user_id' => 2, 'balance' => 43.80, 'qr_code' => 'QR-SOPHIE-PETIT-002'],
            ['user_id' => 3, 'balance' => 47.30, 'qr_code' => 'QR-LUCAS-DUBOIS-003'],
            ['user_id' => 4, 'balance' => 32.15, 'qr_code' => 'QR-CLAIRE-BERNARD-004'],
        ])->saveData();
    }
}
