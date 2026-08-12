<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\Wallet;
use App\Models\WalletTransaction;

class AdminPlaceholderController extends Controller
{
    /**
     * Libellés humains des sections admin à venir (Lots 6 à 10).
     * Toute section absente de cette liste renvoie une 404 classique.
     */
    private const SECTIONS = [
        'portefeuilles' => 'Portefeuille client',
    ];

    private const SECTION_FEATURES = [
        'portefeuilles' => [
            'Solde et historique de consommation',
            'Recharges et offres dédiées',
            'Segmentation des clients fidèles',
            'Alertes et notifications personnalisées',
        ],
    ];

    public function show(string $section): void
    {
        Middleware::requireRole('admin');

        if (!isset(self::SECTIONS[$section])) {
            http_response_code(404);
            require dirname(__DIR__, 2) . '/Views/errors/404.php';
            return;
        }

        $label = self::SECTIONS[$section];

        if ($section === 'portefeuilles') {
            $filters = [
                'q'    => trim((string) $this->input('q', '')),
                'from' => (string) $this->input('from', ''),
                'to'   => (string) $this->input('to', ''),
            ];

            $this->view('admin/wallets', [
                'title'              => $label . ' — Administration Le Commerce',
                'pageTitle'          => $label,
                'totalBalance'       => Wallet::totalBalance(),
                'walletCount'        => Wallet::count(),
                'rechargesThisMonth' => WalletTransaction::countByType('recharge', 'this_month'),
                'latestTransactions' => WalletTransaction::latestWithUser(20, $filters),
                'topClients'         => Wallet::topByBalance(5),
                'filters'            => $filters,
            ], 'admin');
            return;
        }

        $features = self::SECTION_FEATURES[$section] ?? [];

        $this->view('admin/placeholder', [
            'title'     => $label . ' — Administration Le Commerce',
            'pageTitle' => $label,
            'heading'   => $label,
            'features'  => $features,
        ], 'admin');
    }
}
