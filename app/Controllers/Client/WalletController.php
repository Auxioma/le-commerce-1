<?php

namespace App\Controllers\Client;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\User;
use App\Models\WalletTransaction;

class WalletController extends Controller
{
    /**
     * Historique complet et paginé des transactions du client.
     */
    public function transactions(): void
    {
        Middleware::requireRole('client');

        $user = Middleware::user();
        $page = max(1, (int) $this->input('page', 1));

        $result = WalletTransaction::forUserPaginated($user['id'], $page, 10);

        $this->view('client/transactions', [
            'title'        => 'Mes transactions — Le Commerce',
            'user'         => $user,
            'transactions' => $result['data'],
            'total'        => $result['total'],
            'page'         => $result['page'],
            'totalPages'   => $result['totalPages'],
        ], 'client');
    }

    /**
     * Page avantages fidélité (points, prochain palier).
     * Le catalogue d'offres complet arrive avec le Lot 6.
     */
    public function rewards(): void
    {
        Middleware::requireRole('client');

        $user = Middleware::user();
        $points = (int) $user['loyalty_points'];

        // Paliers simples de démonstration (seront pilotés par l'admin au Lot 6)
        $tiers = [
            10  => 'Café offert',
            50  => 'Planche à saucisson -20 %',
            100 => 'Boisson offerte',
            150 => 'Happy Hour VIP illimité (1 soirée)',
        ];
        $nextTier = null;
        foreach ($tiers as $threshold => $label) {
            if ($points < $threshold) {
                $nextTier = ['threshold' => $threshold, 'label' => $label, 'remaining' => $threshold - $points];
                break;
            }
        }

        $this->view('client/rewards', [
            'title'    => 'Mes avantages — Le Commerce',
            'user'     => $user,
            'points'   => $points,
            'tiers'    => $tiers,
            'nextTier' => $nextTier,
        ], 'client');
    }

    /**
     * Page de parrainage (code personnel + nombre de filleuls).
     */
    public function referral(): void
    {
        Middleware::requireRole('client');

        $user = Middleware::user();

        $this->view('client/referral', [
            'title'         => 'Parrainage — Le Commerce',
            'user'          => $user,
            'referralCount' => User::countReferrals($user['id']),
        ], 'client');
    }
}
