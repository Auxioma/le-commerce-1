<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\GoogleAnalyticsClient;
use App\Core\Middleware;
use App\Models\ContactMessage;
use App\Models\GoogleReview;
use App\Models\Lottery;
use App\Models\Offer;
use App\Models\OfferRedemption;
use App\Models\Poll;
use App\Models\Reservation;
use App\Models\Statistic;
use App\Models\User;
use App\Models\UserLocation;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WhatsappMessage;

class AdminGoogleAnalyticsController extends Controller
{
    public function index(): void
    {
        Middleware::requireRole('admin');

        $ga4 = GoogleAnalyticsClient::fromSettings();
        $ga4Data = $ga4 ? $this->fetchGa4Summary($ga4) : null;

        $this->view('admin/google-analytics/index', [
            'title'     => 'Google Analytics — Administration Le Commerce',
            'pageTitle' => 'Google Analytics',

            // --- KPIs ---
            'totalClients'        => User::countAll(),
            'newClientsToday'     => User::countToday(),
            'walletsActive'       => Wallet::count(),
            'walletsNewToday'     => Wallet::countCreatedThisMonth(),
            'reservationsCount'   => Reservation::countUpcoming(),
            'reservationsToday'   => Reservation::countToday(),
            'offersActive'        => Offer::countByStatus('active'),
            'lotteryEntries'      => Lottery::totalEntries(),
            'lotteryEntriesToday' => Lottery::totalEntriesToday(),
            'pollsActive'         => Poll::countByStatus('actif'),
            'messagesToday'       => ContactMessage::countToday(),
            'whatsappToday'       => WhatsappMessage::countSentToday(),
            'nearbyClients'       => UserLocation::countRecent(60),
            'reviewsThisMonth'    => GoogleReview::countThisMonth(),
            'newClientsThisWeek'  => User::countThisWeek(),

            // --- Graphiques ---
            'clientRegistrations'   => Statistic::clientRegistrationsLastDays(7),
            'registrationsBySource' => User::registrationsBySource(),
            'nearbyLocations'        => UserLocation::recentWithUser(60),

            // --- Blocs bas ---
            'topOffers'          => array_slice(
                (function () {
                    $offers = Offer::listWithUsage('active');
                    usort($offers, fn($a, $b) => $b['usage_count'] <=> $a['usage_count']);
                    return $offers;
                })(),
                0,
                5
            ),
            'totalBalance'       => Wallet::totalBalance(),
            'rechargesThisMonth' => WalletTransaction::countByType('recharge', 'this_month'),
            'latestRecharges'    => WalletTransaction::latestWithUser(5),

            'qrUsage' => [
                ['label' => 'QR Offres & Avantages', 'count' => OfferRedemption::countAll()],
                ['label' => 'QR Portefeuille Client', 'count' => WalletTransaction::countByType('recharge', 'this_month')],
                ['label' => 'QR Loterie',             'count' => Lottery::totalEntries()],
                ['label' => 'QR Avis Google',         'count' => GoogleReview::count()],
            ],

            'ga4Configured' => $ga4 !== null,
            'ga4'           => $ga4Data,
        ], 'admin');
    }

    /**
     * Interroge l'API GA4 Data pour les métriques clés (7 derniers jours)
     * et une série temporelle pour le mini-graphique.
     */
    private function fetchGa4Summary(GoogleAnalyticsClient $ga4): ?array
    {
        $summary = $ga4->runReport([
            'dateRanges' => [['startDate' => '7daysAgo', 'endDate' => 'today']],
            'metrics'    => [
                ['name' => 'activeUsers'],
                ['name' => 'newUsers'],
                ['name' => 'sessions'],
                ['name' => 'screenPageViews'],
                ['name' => 'eventCount'],
                ['name' => 'bounceRate'],
                ['name' => 'averageSessionDuration'],
                ['name' => 'conversions'],
            ],
        ]);

        if ($summary === null) {
            return null;
        }

        $values = $summary['rows'][0]['metricValues'] ?? [];
        $get = fn (int $i) => isset($values[$i]) ? (float) $values[$i]['value'] : 0.0;

        $timeseries = $ga4->runReport([
            'dateRanges' => [['startDate' => '7daysAgo', 'endDate' => 'today']],
            'dimensions' => [['name' => 'date']],
            'metrics'    => [['name' => 'sessions']],
            'orderBys'   => [['dimension' => ['dimensionName' => 'date']]],
        ]);

        $series = [];
        foreach ($timeseries['rows'] ?? [] as $row) {
            $series[] = [
                'date'     => $row['dimensionValues'][0]['value'] ?? '',
                'sessions' => (int) ($row['metricValues'][0]['value'] ?? 0),
            ];
        }

        return [
            'users'        => (int) $get(0),
            'newUsers'     => (int) $get(1),
            'sessions'     => (int) $get(2),
            'pageViews'    => (int) $get(3),
            'eventCount'   => (int) $get(4),
            'bounceRate'   => round($get(5) * 100, 1),
            'avgDuration'  => (int) $get(6),
            'conversions'  => (int) $get(7),
            'series'       => $series,
            'topPages'     => $this->fetchTopPages($ga4),
            'channels'     => $this->fetchByDimension($ga4, 'sessionDefaultChannelGroup', 'sessions', 6),
            'devices'      => $this->fetchByDimension($ga4, 'deviceCategory', 'sessions', 5),
        ];
    }

    /**
     * Pages les plus consultées (7 derniers jours), triées par vues décroissantes.
     */
    private function fetchTopPages(GoogleAnalyticsClient $ga4, int $limit = 5): array
    {
        $report = $ga4->runReport([
            'dateRanges' => [['startDate' => '7daysAgo', 'endDate' => 'today']],
            'dimensions' => [['name' => 'pagePath']],
            'metrics'    => [['name' => 'screenPageViews']],
            'orderBys'   => [['metric' => ['metricName' => 'screenPageViews'], 'desc' => true]],
            'limit'      => $limit,
        ]);

        $rows = [];
        foreach ($report['rows'] ?? [] as $row) {
            $rows[] = [
                'label' => $row['dimensionValues'][0]['value'] ?? '/',
                'value' => (int) ($row['metricValues'][0]['value'] ?? 0),
            ];
        }

        return $rows;
    }

    /**
     * Répartition d'une métrique par dimension (canaux d'acquisition,
     * catégories d'appareil...), triée par valeur décroissante.
     */
    private function fetchByDimension(GoogleAnalyticsClient $ga4, string $dimension, string $metric, int $limit): array
    {
        $report = $ga4->runReport([
            'dateRanges' => [['startDate' => '7daysAgo', 'endDate' => 'today']],
            'dimensions' => [['name' => $dimension]],
            'metrics'    => [['name' => $metric]],
            'orderBys'   => [['metric' => ['metricName' => $metric], 'desc' => true]],
            'limit'      => $limit,
        ]);

        $rows = [];
        foreach ($report['rows'] ?? [] as $row) {
            $rows[] = [
                'label' => $row['dimensionValues'][0]['value'] ?? 'Autre',
                'value' => (int) ($row['metricValues'][0]['value'] ?? 0),
            ];
        }

        return $rows;
    }
}
