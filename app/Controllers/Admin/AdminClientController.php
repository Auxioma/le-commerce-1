<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Service\CsvExportService;
use App\Service\NotificationService;
use App\Service\WalletService;

class AdminClientController extends Controller
{
    private WalletService $walletService;
    private NotificationService $notificationService;
    private CsvExportService $csvExportService;

    public function __construct()
    {
        parent::__construct();
        $this->walletService = new WalletService();
        $this->notificationService = new NotificationService();
        $this->csvExportService = new CsvExportService();
    }

    public function index(): void
    {
        Middleware::requireRole('admin');

        $filters = [
            'q'       => trim((string) $this->input('q', '')),
            'status'  => (string) $this->input('status', 'tous'),
            'segment' => (string) $this->input('segment', 'tous'),
            'from'    => (string) $this->input('from', ''),
            'to'      => (string) $this->input('to', ''),
        ];
        $page = max(1, (int) $this->input('page', 1));

        $result = User::paginateClients($filters, $page, 8);

        $this->view('admin/clients/index', [
            'title'      => 'Clients inscrits — Administration Le Commerce',
            'pageTitle'  => 'Clients inscrits',
            'clients'    => $result['data'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'perPage'    => $result['perPage'],
            'totalPages' => $result['totalPages'],
            'totalAll'   => User::countAll(),
            'newThisMonth' => User::countThisMonth(),
            'filters'    => $filters,
        ], 'admin');
    }

    /**
     * Export CSV de la liste filtrée (tous les résultats, sans pagination).
     */
    public function export(): void
    {
        Middleware::requireRole('admin');

        $filters = [
            'q'       => trim((string) $this->input('q', '')),
            'status'  => (string) $this->input('status', 'tous'),
            'segment' => (string) $this->input('segment', 'tous'),
            'from'    => (string) $this->input('from', ''),
            'to'      => (string) $this->input('to', ''),
        ];

        // perPage volontairement très large pour tout récupérer en un export
        $result = User::paginateClients($filters, 1, 100000);

        $this->csvExportService->streamDownload(
            'clients-le-commerce-' . date('Y-m-d') . '.csv',
            function ($out) use ($result): void {
                $this->csvExportService->writeRow($out, ['Prénom', 'Nom', 'Téléphone WhatsApp', 'E-mail', 'Segment', 'Statut', 'Solde portefeuille', 'Date d\'inscription'], ';');

                foreach ($result['data'] as $client) {
                    $this->csvExportService->writeRow($out, [
                        $client['first_name'],
                        $client['last_name'],
                        $client['phone_whatsapp'],
                        $client['email'],
                        $client['segment'],
                        $client['status'],
                        number_format((float) $client['wallet_balance'], 2, ',', ''),
                        date('d/m/Y', strtotime($client['created_at'])),
                    ], ';');
                }
            }
        );
    }

    /**
     * Fiche détail d'un client : informations, portefeuille, historique des
     * transactions. Point d'entrée pour toutes les actions d'administration
     * (modification, ajustement de solde, suppression).
     */
    public function show(int $id): void
    {
        Middleware::requireRole('admin');

        $client = User::find($id);
        if (!$client || $client['role'] !== 'client') {
            $this->setFlash('error', 'Client introuvable.');
            $this->redirect('/admin/clients');
            return;
        }

        $wallet = Wallet::findByUser($id);
        $page = max(1, (int) $this->input('page', 1));
        $transactions = WalletTransaction::forUserPaginated($id, $page, 10);
        $referralsCount = User::countReferrals($id);

        $this->view('admin/clients/show', [
            'title'          => trim($client['first_name'] . ' ' . $client['last_name']) . ' — Administration Le Commerce',
            'pageTitle'      => 'Fiche client',
            'client'         => $client,
            'wallet'         => $wallet,
            'transactions'   => $transactions,
            'referralsCount' => $referralsCount,
        ], 'admin');
    }

    /**
     * Met à jour les informations d'un client (identité, segment, statut).
     */
    public function update(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $client = User::find($id);
        if (!$client || $client['role'] !== 'client') {
            $this->setFlash('error', 'Client introuvable.');
            $this->redirect('/admin/clients');
            return;
        }

        $firstName = trim((string) $this->input('first_name', ''));
        $lastName  = trim((string) $this->input('last_name', ''));
        $phone     = User::normalizePhone(trim((string) $this->input('phone_whatsapp', '')));
        $email     = trim((string) $this->input('email', ''));
        $segment   = (string) $this->input('segment', $client['segment']);
        $status    = (string) $this->input('status', $client['status']);

        if ($firstName === '' || $lastName === '' || $phone === '') {
            $this->setFlash('error', 'Le prénom, le nom et le téléphone WhatsApp sont obligatoires.');
            $this->redirect('/admin/clients/' . $id);
            return;
        }

        $existing = User::findByPhone($phone);
        if ($existing && (int) $existing['id'] !== $id) {
            $this->setFlash('error', 'Ce numéro WhatsApp est déjà utilisé par un autre client.');
            $this->redirect('/admin/clients/' . $id);
            return;
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'Adresse e-mail invalide.');
            $this->redirect('/admin/clients/' . $id);
            return;
        }

        User::update($id, [
            'first_name'     => $firstName,
            'last_name'      => $lastName,
            'phone_whatsapp' => $phone,
            'email'          => $email !== '' ? $email : null,
            'segment'        => in_array($segment, ['nouveau', 'fidele', 'occasionnel'], true) ? $segment : $client['segment'],
            'status'         => in_array($status, ['actif', 'inactif'], true) ? $status : $client['status'],
        ]);

        $this->setFlash('success', 'Fiche client mise à jour.');
        $this->redirect('/admin/clients/' . $id);
    }

    /**
     * Crédite ou débite manuellement le portefeuille d'un client (ex: geste
     * commercial, correction). Opération atomique : le solde et la ligne
     * d'historique sont écrits dans la même transaction SQL.
     */
    public function adjustWallet(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $client = User::find($id);
        $wallet = $client ? Wallet::findByUser($id) : null;
        if (!$client || $client['role'] !== 'client' || !$wallet) {
            $this->setFlash('error', 'Client ou portefeuille introuvable.');
            $this->redirect('/admin/clients');
            return;
        }

        $direction     = (string) $this->input('direction', 'credit');
        $amount        = round((float) $this->input('amount', 0), 2);
        $label         = trim((string) $this->input('label', ''));
        $paymentMethod = in_array($this->input('payment_method'), ['especes', 'carte_bancaire'], true)
            ? $this->input('payment_method')
            : 'especes';

        if ($amount <= 0) {
            $this->setFlash('error', 'Le montant doit être supérieur à 0.');
            $this->redirect('/admin/clients/' . $id);
            return;
        }
        if ($direction === 'debit' && $amount > (float) $wallet['balance']) {
            $this->setFlash('error', 'Le débit dépasse le solde actuel du client.');
            $this->redirect('/admin/clients/' . $id);
            return;
        }

        $result = $this->walletService->adjustBalance($wallet, $direction, $amount, $label, $paymentMethod);

        if ($result['success']) {
            $bonus = $result['bonus'];
            $this->setFlash('success', 'Solde du portefeuille mis à jour.' . ($bonus > 0 ? ' (+' . $bonus . ' € de bonus fidélité offerts)' : ''));
        } else {
            $this->setFlash('error', 'Une erreur est survenue, le solde n\'a pas été modifié.');
        }

        $this->redirect('/admin/clients/' . $id);
    }

    /**
     * Suppression douce d'un compte client : la ligne est marquée
     * deleted_at (portefeuille et historique conservés, le compte disparaît
     * des listes et ne peut plus se connecter).
     */
    public function destroy(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $client = User::find($id);
        if (!$client || $client['role'] !== 'client') {
            $this->setFlash('error', 'Client introuvable.');
            $this->redirect('/admin/clients');
            return;
        }

        User::delete($id);
        $this->setFlash('success', $client['first_name'] . ' ' . $client['last_name'] . ' a été supprimé.');
        $this->redirect('/admin/clients');
    }

    /**
     * Envoi (simulé pour ce lot) d'un message WhatsApp à un client depuis sa fiche.
     * L'intégration réelle à l'API WhatsApp Business sera branchée au Lot 9.
     */
    public function sendMessage(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $client = User::find($id);
        if (!$client || $client['role'] !== 'client') {
            $this->setFlash('error', 'Client introuvable.');
            $this->redirect('/admin/clients');
            return;
        }

        $content = trim((string) $this->input('content', ''));
        if ($content !== '') {
            $this->notificationService->sendWhatsapp($id, $content);
            $this->setFlash('success', 'Message envoyé à ' . $client['first_name'] . ' ' . $client['last_name'] . '.');
        }

        $this->redirect('/admin/clients');
    }
}
