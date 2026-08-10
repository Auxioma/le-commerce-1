<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\Lottery;
use App\Models\LotteryEntry;
use App\Models\Offer;
use App\Models\OfferRedemption;
use App\Models\Poll;
use App\Models\Reservation;
use App\Models\Statistic;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;

class AdminDashboardController extends Controller
{
    public function index(): void
    {
        Middleware::requireRole('admin');

        // --- KPIs ---
        $totalClients        = User::countAll();
        $newClientsToday     = User::countToday();

        $walletsActive       = Wallet::count();
        $walletsNewToday     = Wallet::countCreatedThisMonth(); // approximation

        $reservationsCount   = Reservation::countUpcoming();
        $reservationsToday   = Reservation::countToday();

        $offersActive        = Offer::countByStatus('active');

        $lotteryEntries      = Lottery::totalEntries();
        $lotteryEntriesToday = Lottery::totalEntriesToday();

        $pollsActive         = Poll::countByStatus('actif');

        // --- Graphique inscriptions (7 derniers jours) ---
        $clientRegistrations = Statistic::clientRegistrationsLastDays(7);

        // --- Répartition inscriptions par outil (Bar, Tabac, PMU...) ---
        $registrationsBySource = User::registrationsBySource();

        // --- Activités récentes ---
        $activity = [];
        foreach (User::latestClients(3) as $c) {
            $activity[] = ['type' => 'client', 'time' => $c['created_at'], 'label' => 'Nouveau client inscrit', 'detail' => trim($c['first_name'] . ' ' . $c['last_name'])];
        }
        foreach (WalletTransaction::latestWithUser(3) as $t) {
            if ($t['type'] !== 'recharge') continue;
            $activity[] = ['type' => 'wallet', 'time' => $t['created_at'], 'label' => 'Portefeuille rechargé', 'detail' => trim($t['first_name'] . ' ' . $t['last_name']) . ' - ' . number_format((float) $t['amount'], 2, ',', ' ') . ' €'];
        }
        foreach (Reservation::filtered(['status' => 'confirmee']) as $r) {
            $activity[] = ['type' => 'reservation', 'time' => $r['created_at'], 'label' => 'Nouvelle réservation', 'detail' => 'Soirée privée - ' . date('d/m/Y', strtotime($r['reservation_date']))];
        }
        foreach (OfferRedemption::latestUsedWithUser(3) as $r) {
            $activity[] = ['type' => 'offer', 'time' => $r['used_at'], 'label' => 'Offre scannée', 'detail' => $r['offer_title']];
        }
        foreach (LotteryEntry::latestWithUser(3) as $e) {
            $activity[] = ['type' => 'lottery', 'time' => $e['created_at'], 'label' => 'Participation à la loterie', 'detail' => 'Ticket ' . $e['code']];
        }
        usort($activity, fn($a, $b) => strtotime($b['time']) <=> strtotime($a['time']));
        $activity = array_slice($activity, 0, 6);

        // --- Inscriptions récentes ---
        $latestClients = User::latestClientsWithEmail(5);

        // --- Portefeuilles clients ---
        $totalBalance          = Wallet::totalBalance();
        $rechargesThisMonth    = WalletTransaction::countByType('recharge', 'this_month');
        $latestRecharges       = WalletTransaction::latestWithUser(4);

        // --- Offres les plus utilisées ---
        $topOffers = array_slice(
            (function () {
                $offers = Offer::listWithUsage('active');
                usort($offers, fn($a, $b) => $b['usage_count'] <=> $a['usage_count']);
                return $offers;
            })(),
            0,
            5
        );

        // --- Sondages en cours ---
        $activePolls = array_slice(Poll::listWithStats('actif'), 0, 4);

        // --- Prochaine loterie ---
        $nextLottery = Lottery::nextActive();

        $this->view('admin/dashboard', [
            'title'     => 'Tableau de bord — Administration Le Commerce',
            'pageTitle' => 'Tableau de bord',

            'totalClients'        => $totalClients,
            'newClientsToday'     => $newClientsToday,
            'walletsActive'       => $walletsActive,
            'walletsNewToday'     => $walletsNewToday,
            'reservationsCount'   => $reservationsCount,
            'reservationsToday'   => $reservationsToday,
            'offersActive'        => $offersActive,
            'lotteryEntries'      => $lotteryEntries,
            'lotteryEntriesToday' => $lotteryEntriesToday,
            'pollsActive'         => $pollsActive,

            'clientRegistrations'    => $clientRegistrations,
            'registrationsBySource'  => $registrationsBySource,
            'recentActivity'         => $activity,

            'latestClients'       => $latestClients,
            'totalBalance'        => $totalBalance,
            'rechargesThisMonth'  => $rechargesThisMonth,
            'latestRecharges'     => $latestRecharges,
            'topOffers'           => $topOffers,
            'activePolls'         => $activePolls,
            'nextLottery'         => $nextLottery,
        ], 'admin');
    }
}
