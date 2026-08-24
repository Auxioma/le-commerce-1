<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\Invoice;
use App\Service\CsvExportService;

class AdminBillingController extends Controller
{
    private CsvExportService $csvExportService;

    public function __construct()
    {
        parent::__construct();
        $this->csvExportService = new CsvExportService();
    }

    private function filters(): array
    {
        return [
            'q'    => trim((string) $this->input('q', '')),
            'from' => trim((string) $this->input('from', '')),
            'to'   => trim((string) $this->input('to', '')),
        ];
    }

    public function index(): void
    {
        Middleware::requireRole('admin');

        $page = max(1, (int) $this->input('page', 1));
        $filters = $this->filters();
        $result = Invoice::paginate($page, 10, $filters);

        $this->view('admin/billing/index', [
            'title'     => 'Facturation — Administration Le Commerce',
            'pageTitle' => 'Facturation',

            'invoices'   => $result['data'],
            'page'       => $result['page'],
            'totalPages' => $result['totalPages'],
            'total'      => $result['total'],
            'filters'    => $filters,

            'totalRevenue'      => Invoice::totalRevenue(),
            'totalRevenueMonth' => Invoice::totalRevenueThisMonth(),
            'countThisMonth'    => Invoice::countThisMonth(),
        ], 'admin');
    }

    /**
     * Export CSV des factures correspondant aux filtres courants.
     */
    public function export(): void
    {
        Middleware::requireRole('admin');

        $filters = $this->filters();
        $invoices = Invoice::allWithFilters($filters);

        $this->csvExportService->streamDownload(
            'facturation_' . date('Y-m-d') . '.csv',
            function ($output) use ($invoices): void {
                $this->csvExportService->writeRow($output, ['N° Facture', 'Client', 'Email', 'Date', 'Montant (€)']);
                foreach ($invoices as $inv) {
                    $this->csvExportService->writeRow($output, [
                        '#' . str_pad((string) $inv['id'], 6, '0', STR_PAD_LEFT),
                        $inv['first_name'] . ' ' . $inv['last_name'],
                        $inv['email'],
                        date('d/m/Y', strtotime($inv['created_at'])),
                        number_format((float) $inv['amount'], 2, ',', ''),
                    ]);
                }
            }
        );
    }

    /**
     * Facture imprimable (HTML) pour une transaction donnée.
     * L'utilisateur peut l'enregistrer en PDF via "Imprimer" du navigateur,
     * sans dépendance serveur supplémentaire (pas de librairie PDF).
     */
    public function show(int $id): void
    {
        Middleware::requireRole('admin');

        $invoice = Invoice::findWithDetails($id);
        if (!$invoice) {
            $this->setFlash('error', 'Facture introuvable.');
            $this->redirect('/admin/facturation');
            return;
        }

        // Vue autonome (sans layout admin) pour une impression propre
        $this->view('admin/billing/invoice', [
            'title'   => 'Facture #' . str_pad((string) $invoice['id'], 6, '0', STR_PAD_LEFT),
            'invoice' => $invoice,
        ], 'invoice');
    }
}
