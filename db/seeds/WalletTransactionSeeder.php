<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class WalletTransactionSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['WalletSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM wallet_transactions LIMIT 1')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $ago = fn(string $interval) => date('Y-m-d H:i:s', strtotime("-{$interval}"));

        $this->table('wallet_transactions')->insert([
            ['wallet_id' => 1, 'type' => 'recharge', 'amount' => 50.00, 'payment_method' => 'carte_bancaire', 'status' => 'reussi', 'label' => 'Recharge portefeuille', 'created_at' => $ago('2 days')],
            ['wallet_id' => 1, 'type' => 'debit', 'amount' => 18.60, 'payment_method' => 'portefeuille', 'status' => 'reussi', 'label' => 'Consommation au bar', 'created_at' => $ago('1 day')],
            ['wallet_id' => 1, 'type' => 'debit', 'amount' => 14.50, 'payment_method' => 'portefeuille', 'status' => 'reussi', 'label' => 'Consommation au bar', 'created_at' => $ago('6 days')],
            ['wallet_id' => 1, 'type' => 'recharge', 'amount' => 30.00, 'payment_method' => 'especes', 'status' => 'reussi', 'label' => 'Recharge en caisse', 'created_at' => $ago('10 days')],

            ['wallet_id' => 2, 'type' => 'recharge', 'amount' => 50.00, 'payment_method' => 'carte_bancaire', 'status' => 'reussi', 'label' => 'Recharge portefeuille', 'created_at' => $ago('3 hours')],
            ['wallet_id' => 2, 'type' => 'debit', 'amount' => 12.50, 'payment_method' => 'portefeuille', 'status' => 'reussi', 'label' => 'Consommation au tabac', 'created_at' => $ago('1 day')],

            ['wallet_id' => 3, 'type' => 'recharge', 'amount' => 100.00, 'payment_method' => 'especes', 'status' => 'reussi', 'label' => 'Recharge en caisse', 'created_at' => $ago('4 days')],
            ['wallet_id' => 3, 'type' => 'debit', 'amount' => 22.30, 'payment_method' => 'portefeuille', 'status' => 'reussi', 'label' => 'Consommation au bar', 'created_at' => $ago('2 days')],

            ['wallet_id' => 4, 'type' => 'remboursement', 'amount' => 20.00, 'payment_method' => 'portefeuille', 'status' => 'reussi', 'label' => 'Remboursement offre annulée', 'created_at' => $ago('5 days')],
            ['wallet_id' => 4, 'type' => 'debit', 'amount' => 9.20, 'payment_method' => 'portefeuille', 'status' => 'reussi', 'label' => 'Consommation au tabac', 'created_at' => $ago('7 days')],
        ])->saveData();
    }
}
