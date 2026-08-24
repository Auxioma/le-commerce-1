<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\Statistic;
use App\Models\Wallet;
use App\Models\User;
use App\Models\OfferRedemption;
use App\Models\Poll;
use App\Service\CsvExportService;

class AdminStatisticsController extends Controller
{
    private const FILTERS = ['from', 'to'];

    private CsvExportService $csvExportService;

    public function __construct()
    {
        parent::__construct();
        $this->csvExportService = new CsvExportService();
    }

    private function dateRange(): array
    {
        $from = trim((string) $this->input('from', ''));
        $to   = trim((string) $this->input('to', ''));

        if (!$from || !$to) {
            $from = date('Y-m-d', strtotime('-13 days'));
            $to   = date('Y-m-d');
        }

        if ($from && $to && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        return compact('from', 'to');
    }

    public function index(): void
    {
        Middleware::requireRole('admin');

        $range = $this->dateRange();

        $this->view('admin/statistics/index', [
            'title'     => 'Statistiques — Administration Le Commerce',
            'pageTitle' => 'Statistiques',

            'filters'           => $range,
            'walletActivity'    => Statistic::walletActivityForRange($range['from'], $range['to']),
            'paymentBreakdown'  => Statistic::paymentMethodBreakdownForRange($range['from'], $range['to']),
            'newClientsByMonth' => Statistic::newClientsByRange($range['from'], $range['to']),
            'topClients'        => Statistic::topClientsBySpendForRange($range['from'], $range['to'], 5),

            'totalBalance'        => Wallet::totalBalance(),
            'totalClients'        => User::countAll(),
            'offersUsed'          => OfferRedemption::countUsedThisMonth(),
            'pollsParticipations' => Poll::totalParticipations(),
        ], 'admin');
    }

    public function export(): void
    {
        Middleware::requireRole('admin');

        $range = $this->dateRange();

        $activity   = Statistic::walletActivityForRange($range['from'], $range['to']);
        $payments   = Statistic::paymentMethodBreakdownForRange($range['from'], $range['to']);
        $clients    = Statistic::newClientsByRange($range['from'], $range['to']);
        $topClients = Statistic::topClientsBySpendForRange($range['from'], $range['to'], 20);

        $filename = 'statistiques_' . $range['from'] . '_' . $range['to'] . '.csv';

        $this->csvExportService->streamDownload(
            $filename,
            function ($output) use ($range, $activity, $payments, $clients, $topClients): void {
                // Section 1 : période
                $this->csvExportService->writeRow($output, ['Période', $range['from'] . ' — ' . $range['to']]);
                $this->csvExportService->writeRow($output, []);

                // Section 2 : activité portefeuille
                $this->csvExportService->writeRow($output, ['Activité portefeuille', 'Recharges (€)', 'Dépenses (€)']);
                foreach ($activity as $row) {
                    $this->csvExportService->writeRow($output, [
                        $row['jour'],
                        number_format((float) $row['recharges'], 2, ',', ''),
                        number_format((float) $row['depenses'], 2, ',', ''),
                    ]);
                }
                $this->csvExportService->writeRow($output, []);

                // Section 3 : moyens de paiement
                $this->csvExportService->writeRow($output, ['Moyen de paiement', 'Nombre', 'Total (€)']);
                foreach ($payments as $row) {
                    $this->csvExportService->writeRow($output, [
                        $row['payment_method'],
                        $row['nb'],
                        number_format((float) $row['total'], 2, ',', ''),
                    ]);
                }
                $this->csvExportService->writeRow($output, []);

                // Section 4 : nouveaux clients par mois
                $this->csvExportService->writeRow($output, ['Mois', 'Nouveaux clients']);
                foreach ($clients as $row) {
                    $this->csvExportService->writeRow($output, [$row['mois'], $row['nb']]);
                }
                $this->csvExportService->writeRow($output, []);

                // Section 5 : top clients
                $this->csvExportService->writeRow($output, ['Top clients', 'Téléphone', 'Dépenses (€)']);
                foreach ($topClients as $row) {
                    $this->csvExportService->writeRow($output, [
                        $row['first_name'] . ' ' . $row['last_name'],
                        $row['phone_whatsapp'],
                        number_format((float) $row['total_spent'], 2, ',', ''),
                    ]);
                }
            }
        );
    }
}
