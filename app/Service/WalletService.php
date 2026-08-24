<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\Database;
use App\Models\Wallet;
use App\Models\WalletTransaction;

/**
 * Opérations métier sur les portefeuilles clients : ajustements manuels
 * (recharge/débit), règles de bonus, transaction SQL atomique.
 */
final class WalletService
{
    private const LOYALTY_BONUS_THRESHOLD = 50;
    private const LOYALTY_BONUS_AMOUNT = 2.0;

    /**
     * Crédite ou débite le portefeuille d'un client (transaction atomique).
     *
     * @return array{success: bool, bonus: float}
     */
    public function adjustBalance(array $wallet, string $direction, float $amount, string $label, string $paymentMethod): array
    {
        $bonus = $this->loyaltyBonus($direction, $amount);

        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            Wallet::adjustBalance($wallet['id'], $direction === 'debit' ? -$amount : $amount + $bonus);

            WalletTransaction::create([
                'wallet_id'      => $wallet['id'],
                'type'           => $direction === 'debit' ? 'debit' : 'recharge',
                'amount'         => $amount,
                'payment_method' => $direction === 'debit' ? 'portefeuille' : $paymentMethod,
                'status'         => 'reussi',
                'label'          => $label !== '' ? $label : ($direction === 'debit' ? 'Ajustement manuel (admin)' : 'Recharge en caisse'),
            ]);

            if ($bonus > 0) {
                WalletTransaction::create([
                    'wallet_id'      => $wallet['id'],
                    'type'           => 'recharge',
                    'amount'         => $bonus,
                    'payment_method' => $paymentMethod,
                    'status'         => 'reussi',
                    'label'          => 'Bonus fidélité',
                ]);
            }

            $pdo->commit();
            return ['success' => true, 'bonus' => $bonus];
        } catch (\Throwable) {
            $pdo->rollBack();
            return ['success' => false, 'bonus' => 0.0];
        }
    }

    /**
     * Bonus fidélité : +2 € offerts pour une recharge en caisse de 50 € pile.
     */
    private function loyaltyBonus(string $direction, float $amount): float
    {
        if ($direction === 'credit' && (int) $amount === self::LOYALTY_BONUS_THRESHOLD) {
            return self::LOYALTY_BONUS_AMOUNT;
        }

        return 0.0;
    }
}
