<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\Offer;
use App\Models\OfferRedemption;
use App\Models\Poll;
use App\Models\ProximityCampaign;
use App\Models\Statistic;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;

class AdminDashboardController extends Controller
{
    public function index(): void
    {
        Middleware::requireRole('admin');

        $totalBalance = Wallet::totalBalance();
        $balanceDelta = WalletTransaction::netChange('this_month');

        $clientsWithWallet      = Wallet::count();
        $clientsWithWalletDelta = Wallet::countCreatedThisMonth();

        $rechargesThisMonth = WalletTransaction::countByType('recharge', 'this_month');
        $rechargesLastMonth = WalletTransaction::countByType('recharge', 'last_month');
        $rechargesDelta     = $rechargesThisMonth - $rechargesLastMonth;

        $expensesThisMonth = WalletTransaction::sumByType('debit', 'this_month');
        $expensesLastMonth = WalletTransaction::sumByType('debit', 'last_month');
        $expensesPercent   = $expensesLastMonth > 0
            ? round((($expensesThisMonth - $expensesLastMonth) / $expensesLastMonth) * 100)
            : null;

        // Fil d'activité récent, fusionné et trié par date (inscriptions,
        // recharges, offres validées)
        $activity = [];
        foreach (User::latestClients(4) as $c) {
            $activity[] = ['type' => 'client', 'time' => $c['created_at'], 'label' => trim($c['first_name'] . ' ' . $c['last_name']), 'detail' => 'Nouveau client inscrit'];
        }
        foreach (WalletTransaction::latestWithUser(6) as $t) {
            if ($t['type'] !== 'recharge') {
                continue;
            }
            $activity[] = ['type' => 'wallet', 'time' => $t['created_at'], 'label' => trim($t['first_name'] . ' ' . $t['last_name']), 'detail' => 'Portefeuille rechargé · ' . number_format((float) $t['amount'], 2, ',', ' ') . ' €'];
        }
        foreach (OfferRedemption::latestUsedWithUser(4) as $r) {
            $activity[] = ['type' => 'offer', 'time' => $r['used_at'], 'label' => trim($r['first_name'] . ' ' . $r['last_name']), 'detail' => 'Offre utilisée · ' . $r['offer_title']];
        }
        usort($activity, fn($a, $b) => strtotime($b['time']) <=> strtotime($a['time']));
        $activity = array_slice($activity, 0, 6);

        $this->view('admin/dashboard', [
            'title'     => 'Tableau de bord — Administration Le Commerce',
            'pageTitle' => 'Tableau de bord',

            'totalBalance'   => $totalBalance,
            'balanceDelta'   => $balanceDelta,

            'clientsWithWallet'      => $clientsWithWallet,
            'clientsWithWalletDelta' => $clientsWithWalletDelta,

            'rechargesThisMonth' => $rechargesThisMonth,
            'rechargesDelta'     => $rechargesDelta,

            'expensesThisMonth' => $expensesThisMonth,
            'expensesPercent'   => $expensesPercent,

            'latestTransactions' => WalletTransaction::latestWithUser(5),
            'topClients'         => Wallet::topByBalance(5),

            'totalClients'        => User::countAll(),
            'newClientsThisMonth' => User::countThisMonth(),

            'offersActive'            => Offer::countByStatus('active'),
            'offersCreatedThisMonth'  => Offer::countCreatedThisMonth(),
            'topOffers'               => array_slice(
                (function () {
                    $offers = Offer::listWithUsage('active');
                    usort($offers, fn($a, $b) => $b['usage_count'] <=> $a['usage_count']);
                    return $offers;
                })(),
                0,
                5
            ),

            'pollsActive'         => Poll::countByStatus('actif'),
            'pollsParticipations' => Poll::totalParticipations(),
            'activePolls'         => array_slice(Poll::listWithStats('actif'), 0, 4),

            'campaignsActive' => ProximityCampaign::countByStatus('active'),

            'clientRegistrations' => Statistic::clientRegistrationsLastDays(14),
            'clientsBySegment'    => Statistic::clientsBySegment(),

            'recentActivity' => $activity,
        ], 'admin');
    }
}
