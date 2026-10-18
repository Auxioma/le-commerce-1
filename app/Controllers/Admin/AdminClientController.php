<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Core\View;
use App\Models\PasswordReset;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Service\ClientSegmentationService;
use App\Service\CsvExportService;
use App\Service\Mailer;
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

        $filters = $this->listFilters();
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
            'segments'        => ClientSegmentationService::computeAll(),
            'segmentDefs'     => ClientSegmentationService::definitions(),
            'segmentCounts'   => ClientSegmentationService::counts(),
        ], 'admin');
    }

    /**
     * Filtres de la liste (et de l'export). Le segment est calculé
     * automatiquement : on le traduit en liste d'ids de clients.
     */
    private function listFilters(): array
    {
        $segment = (string) $this->input('segment', 'tous');
        if (!array_key_exists($segment, ClientSegmentationService::allDefinitions())) {
            $segment = 'tous';
        }

        $filters = [
            'q'       => trim((string) $this->input('q', '')),
            'status'  => (string) $this->input('status', 'tous'),
            'segment' => $segment,
            'from'    => (string) $this->input('from', ''),
            'to'      => (string) $this->input('to', ''),
        ];
        if ($segment !== 'tous') {
            $filters['ids'] = ClientSegmentationService::idsInSegment($segment);
        }

        return $filters;
    }

    /** Libellés des segments d'un client, ex. « Client fidèle, Client afterwork ». */
    private function segmentLabels(int $clientId): string
    {
        $segments = ClientSegmentationService::forUser($clientId);
        if (!$segments) {
            return '';
        }
        $defs = ClientSegmentationService::allDefinitions();
        return implode(', ', array_map(
            static fn(string $key) => $defs[$key]['label'],
            array_merge([$segments['lifecycle']], $segments['profiles'])
        ));
    }

    /**
     * Export CSV de la liste filtrée (tous les résultats, sans pagination).
     */
    public function export(): void
    {
        Middleware::requireRole('admin');

        $filters = $this->listFilters();

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
                        $this->segmentLabels((int) $client['id']),
                        $client['status'],
                        number_format((float) $client['wallet_balance'], 2, ',', ''),
                        date('d/m/Y', strtotime($client['created_at'])),
                    ], ';');
                }
            }
        );
    }

    /**
     * Formulaire d'ajout d'un client par un employé (au comptoir, par
     * téléphone...). Volontairement réduit au strict nécessaire : le client
     * complète lui-même le reste de sa fiche depuis son espace "Mes
     * informations", une fois son mot de passe défini.
     */
    public function create(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/clients/create', [
            'title'     => 'Ajouter un client — Administration Le Commerce',
            'pageTitle' => 'Ajouter un client',
            'errors' => [],
            'old'    => [],
        ], 'admin');
    }

    public function store(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        [$errors, $data] = $this->validateNewClient();

        if ($errors) {
            $this->view('admin/clients/create', [
                'title'     => 'Ajouter un client — Administration Le Commerce',
                'pageTitle' => 'Ajouter un client',
                'errors' => $errors,
                'old'    => $data,
            ], 'admin');
            return;
        }

        $userId = User::createByAdmin($data);
        Wallet::createForUser($userId);

        $this->dispatchWelcomeEmail(User::find($userId));

        $this->setFlash('success', $data['first_name'] . ' ' . $data['last_name'] . ' a bien été ajouté(e). Un e-mail vient de lui être envoyé pour définir son mot de passe.');
        $this->redirect('/admin/clients/' . $userId);
    }

    /**
     * Envoie au client fraîchement créé un lien (jeton à usage unique, 60
     * minutes) pour choisir son propre mot de passe — l'administrateur n'en
     * définit ni n'en connaît aucun, pour des raisons de sécurité.
     */
    private function dispatchWelcomeEmail(array $user): void
    {
        $token = PasswordReset::createForUser((int) $user['id']);

        $appUrl = rtrim((string) ($this->sharedData['app']['url'] ?? ''), '/');
        $setUrl = $appUrl . '/reinitialiser-mot-de-passe/' . $token;

        $html = View::render('emails/client-account-created', [
            'shop'       => $this->sharedData['shop'],
            'firstName'  => $user['first_name'],
            'setUrl'     => $setUrl,
            'ttlMinutes' => PasswordReset::TTL_MINUTES,
        ]);

        (new Mailer())->send(
            (string) $user['email'],
            'Bienvenue chez ' . $this->sharedData['shop']['name'] . ' — définissez votre mot de passe',
            $html
        );
    }

    /**
     * @return array{0: array<string,string>, 1: array<string,mixed>} [errors, data]
     */
    private function validateNewClient(): array
    {
        $firstName = trim((string) $this->input('first_name', ''));
        $lastName  = trim((string) $this->input('last_name', ''));
        $phone     = User::normalizePhone((string) $this->input('phone_whatsapp', ''));
        $email     = trim((string) $this->input('email', ''));

        $errors = [];
        if ($firstName === '' || mb_strlen($firstName) > 80) {
            $errors['first_name'] = 'Le prénom est obligatoire.';
        }
        if ($lastName === '' || mb_strlen($lastName) > 80) {
            $errors['last_name'] = 'Le nom est obligatoire.';
        }
        if (!preg_match('/^0[1-9]\d{8}$/', $phone)) {
            $errors['phone_whatsapp'] = 'Numéro WhatsApp invalide (format attendu : 06 12 34 56 78).';
        } elseif (User::phoneExists($phone)) {
            $errors['phone_whatsapp'] = 'Ce numéro est déjà associé à un compte.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Une adresse e-mail valide est obligatoire : elle permet au client de définir son mot de passe.";
        } elseif (User::emailExists($email)) {
            $errors['email'] = 'Cette adresse e-mail est déjà utilisée par un autre compte.';
        }

        return [$errors, ['first_name' => $firstName, 'last_name' => $lastName, 'phone_whatsapp' => $phone, 'email' => $email]];
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
            'segmentation'   => ClientSegmentationService::forUser($id),
            'segmentDefs'    => ClientSegmentationService::allDefinitions(),
        ], 'admin');
    }

    /**
     * Met à jour les informations d'un client (identité, date de naissance,
     * entreprise, statut). Le segment n'est plus saisi : il est calculé
     * automatiquement par ClientSegmentationService.
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
        $birthDate = trim((string) $this->input('birth_date', ''));
        $company   = trim((string) $this->input('company_name', ''));
        $status    = (string) $this->input('status', $client['status']);

        if ($birthDate !== '') {
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $birthDate);
            if (!$date || $date->format('Y-m-d') !== $birthDate || $date > new \DateTimeImmutable('today') || (int) $date->format('Y') < 1900) {
                $this->setFlash('error', 'Date de naissance invalide.');
                $this->redirect('/admin/clients/' . $id);
                return;
            }
        }
        if (mb_strlen($company) > 150) {
            $this->setFlash('error', 'Le nom de l\'entreprise ne peut pas dépasser 150 caractères.');
            $this->redirect('/admin/clients/' . $id);
            return;
        }

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
            'birth_date'     => $birthDate !== '' ? $birthDate : null,
            'company_name'   => $company !== '' ? $company : null,
            'status'       => in_array($status, ['actif', 'inactif'], true) ? $status : $client['status'],
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

        User::anonymizeAndDelete($id);
        $this->setFlash('success', $client['first_name'] . ' ' . $client['last_name'] . ' a été supprimé (e-mail et téléphone anonymisés).');
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
