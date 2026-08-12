<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\Statistic;
use App\Models\Wallet;
use App\Models\User;
use App\Models\OfferRedemption;
use App\Models\Poll;

class AdminStatisticsController extends Controller
{
    private const FILTERS = ['from', 'to'];

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

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF"); // BOM UTF-8

        // Section 1 : période
        fputcsv($output, ['Période', $range['from'] . ' — ' . $range['to']]);
        fputcsv($output, []);

        // Section 2 : activité portefeuille
        fputcsv($output, ['Activité portefeuille', 'Recharges (€)', 'Dépenses (€)']);
        foreach ($activity as $row) {
            fputcsv($output, [
                $row['jour'],
                number_format((float) $row['recharges'], 2, ',', ''),
                number_format((float) $row['depenses'], 2, ',', ''),
            ]);
        }
        fputcsv($output, []);

        // Section 3 : moyens de paiement
        fputcsv($output, ['Moyen de paiement', 'Nombre', 'Total (€)']);
        foreach ($payments as $row) {
            fputcsv($output, [
                $row['payment_method'],
                $row['nb'],
                number_format((float) $row['total'], 2, ',', ''),
            ]);
        }
        fputcsv($output, []);

        // Section 4 : nouveaux clients par mois
        fputcsv($output, ['Mois', 'Nouveaux clients']);
        foreach ($clients as $row) {
            fputcsv($output, [$row['mois'], $row['nb']]);
        }
        fputcsv($output, []);

        // Section 5 : top clients
        fputcsv($output, ['Top clients', 'Téléphone', 'Dépenses (€)']);
        foreach ($topClients as $row) {
            fputcsv($output, [
                $row['first_name'] . ' ' . $row['last_name'],
                $row['phone_whatsapp'],
                number_format((float) $row['total_spent'], 2, ',', ''),
            ]);
        }

        fclose($output);
        exit;
    }
}
