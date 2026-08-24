<?php

declare(strict_types=1);

namespace App\Controllers\Client;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\User;

class ClientDashboardController extends Controller
{
    public function index(): void
    {
        Middleware::requireRole('client');

        $user   = Middleware::user();
        $wallet = Wallet::findByUser($user['id']);

        // Filet de sécurité : un client devrait toujours avoir un portefeuille
        // (créé à l'inscription), mais on le recrée si une incohérence existe.
        if (!$wallet) {
            Wallet::createForUser($user['id']);
            $wallet = Wallet::findByUser($user['id']);
        }

        $transactions = WalletTransaction::forUser($user['id'], 5);
        $lastTransaction = $transactions[0] ?? null;

        $this->view('client/dashboard', [
            'title'     => 'Mon compte — Le Commerce',
            'user'      => $user,
            'wallet'    => $wallet,
            'lastTransaction'   => $lastTransaction,
            'recentTransactions'=> $transactions,
            'spentThisMonth'    => WalletTransaction::sumDebitForUserThisMonth($user['id']),
            'referralCount'     => User::countReferrals($user['id']),
        ], 'client');
    }
}
