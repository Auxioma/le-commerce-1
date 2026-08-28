<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `wallet_transactions`, identifiants inclus.
 */
final class WalletTransactionSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['WalletSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `wallet_transactions` LIMIT 1')) {
            return;
        }

        $this->table('wallet_transactions')->insert([
            ['id' => 1, 'wallet_id' => 1, 'type' => 'recharge', 'amount' => '50.00', 'payment_method' => 'carte_bancaire', 'status' => 'reussi', 'label' => 'Recharge portefeuille', 'created_at' => '2026-08-17 13:42:57'],
            ['id' => 2, 'wallet_id' => 1, 'type' => 'debit', 'amount' => '18.60', 'payment_method' => 'portefeuille', 'status' => 'reussi', 'label' => 'Consommation au bar', 'created_at' => '2026-08-18 13:42:57'],
            ['id' => 3, 'wallet_id' => 1, 'type' => 'debit', 'amount' => '14.50', 'payment_method' => 'portefeuille', 'status' => 'reussi', 'label' => 'Consommation au bar', 'created_at' => '2026-08-13 13:42:57'],
            ['id' => 4, 'wallet_id' => 1, 'type' => 'recharge', 'amount' => '30.00', 'payment_method' => 'especes', 'status' => 'reussi', 'label' => 'Recharge en caisse', 'created_at' => '2026-08-09 13:42:57'],
            ['id' => 5, 'wallet_id' => 2, 'type' => 'recharge', 'amount' => '50.00', 'payment_method' => 'carte_bancaire', 'status' => 'reussi', 'label' => 'Recharge portefeuille', 'created_at' => '2026-08-19 10:42:57'],
            ['id' => 6, 'wallet_id' => 2, 'type' => 'debit', 'amount' => '12.50', 'payment_method' => 'portefeuille', 'status' => 'reussi', 'label' => 'Consommation au tabac', 'created_at' => '2026-08-18 13:42:57'],
            ['id' => 7, 'wallet_id' => 3, 'type' => 'recharge', 'amount' => '100.00', 'payment_method' => 'especes', 'status' => 'reussi', 'label' => 'Recharge en caisse', 'created_at' => '2026-08-15 13:42:57'],
            ['id' => 8, 'wallet_id' => 3, 'type' => 'debit', 'amount' => '22.30', 'payment_method' => 'portefeuille', 'status' => 'reussi', 'label' => 'Consommation au bar', 'created_at' => '2026-08-17 13:42:57'],
            ['id' => 9, 'wallet_id' => 4, 'type' => 'remboursement', 'amount' => '20.00', 'payment_method' => 'portefeuille', 'status' => 'reussi', 'label' => 'Remboursement offre annulée', 'created_at' => '2026-08-14 13:42:57'],
            ['id' => 10, 'wallet_id' => 4, 'type' => 'debit', 'amount' => '9.20', 'payment_method' => 'portefeuille', 'status' => 'reussi', 'label' => 'Consommation au tabac', 'created_at' => '2026-08-12 13:42:57'],
            ['id' => 11, 'wallet_id' => 10, 'type' => 'remboursement', 'amount' => '0.50', 'payment_method' => 'portefeuille', 'status' => 'reussi', 'label' => 'Récompense participation sondage', 'created_at' => '2026-08-27 22:42:52'],
            ['id' => 12, 'wallet_id' => 11, 'type' => 'recharge', 'amount' => '50.00', 'payment_method' => 'carte_bancaire', 'status' => 'reussi', 'label' => 'prépaiement', 'created_at' => '2026-08-28 12:27:36'],
            ['id' => 13, 'wallet_id' => 11, 'type' => 'recharge', 'amount' => '2.00', 'payment_method' => 'carte_bancaire', 'status' => 'reussi', 'label' => 'Bonus fidélité', 'created_at' => '2026-08-28 12:27:36'],
            ['id' => 14, 'wallet_id' => 11, 'type' => 'debit', 'amount' => '1.70', 'payment_method' => 'portefeuille', 'status' => 'reussi', 'label' => 'concomation café', 'created_at' => '2026-08-28 12:29:34'],
            ['id' => 15, 'wallet_id' => 11, 'type' => 'remboursement', 'amount' => '10.00', 'payment_method' => 'portefeuille', 'status' => 'reussi', 'label' => 'Récompense participation sondage', 'created_at' => '2026-08-28 12:35:51'],
        ])->saveData();
    }
}
