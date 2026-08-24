<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\Offer;
use App\Models\OfferRedemption;
use App\Models\User;
use App\Service\NotificationService;

class AdminOfferController extends Controller
{
    private NotificationService $notificationService;

    public function __construct()
    {
        parent::__construct();
        $this->notificationService = new NotificationService();
    }

    public function index(): void
    {
        Middleware::requireRole('admin');

        $statusFilter = (string) $this->input('statut', 'active');
        $statusMap = ['active' => 'active', 'brouillons' => 'brouillon', 'expirees' => 'expiree', 'toutes' => null];
        $status = $statusMap[$statusFilter] ?? 'active';

        $this->view('admin/offers/index', [
            'title'     => 'Offres & Avantages — Administration Le Commerce',
            'pageTitle' => 'Offres & Avantages',

            'offers'         => Offer::listWithUsage($status),
            'activeTab'      => $statusFilter,
            'offersActive'   => Offer::countByStatus('active'),
            'offersDelta'    => Offer::countCreatedThisMonth(),
            'usagesThisMonth'=> OfferRedemption::countUsedThisMonth(),
            'clientsTouched' => OfferRedemption::countDistinctClientsTouched(),
            'savings'        => OfferRedemption::sumSavings(),

            'typeLabels'    => Offer::TYPE_LABELS,
            'segmentLabels' => Offer::SEGMENT_LABELS,
            'clients'       => User::activeClientsForSelect(),
            'activeOffers'  => Offer::activeForSelect(),
        ], 'admin');
    }

    public function create(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/offers/create', [
            'title'     => 'Créer une offre — Administration Le Commerce',
            'pageTitle' => 'Créer une nouvelle offre',
            'formAction'    => '/admin/offres',
            'submitLabel'   => "Créer l'offre",
            'typeLabels'    => Offer::TYPE_LABELS,
            'segmentLabels' => Offer::SEGMENT_LABELS,
            'errors' => [],
            'old'    => [],
        ], 'admin');
    }

    public function store(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        [$errors, $data] = $this->validate();

        if ($errors) {
            $this->view('admin/offers/create', [
                'title'     => 'Créer une offre — Administration Le Commerce',
                'pageTitle' => 'Créer une nouvelle offre',
                'formAction'    => '/admin/offres',
                'submitLabel'   => "Créer l'offre",
                'typeLabels'    => Offer::TYPE_LABELS,
                'segmentLabels' => Offer::SEGMENT_LABELS,
                'errors' => $errors,
                'old'    => $data,
            ], 'admin');
            return;
        }

        Offer::create([
            'title'          => $data['title'],
            'description'    => $data['description'] ?: null,
            'type'           => $data['type'],
            'value'          => (float) $data['value'],
            'target_segment' => $data['target_segment'],
            'valid_until'    => date('Y-m-d', strtotime($data['valid_until'])),
            'status'         => $data['publish'] ? 'active' : 'brouillon',
        ]);

        $this->setFlash('success', 'L\'offre "' . $data['title'] . '" a bien été créée.');
        $this->redirect('/admin/offres');
    }

    public function edit(int $id): void
    {
        Middleware::requireRole('admin');

        $offer = Offer::find($id);
        if (!$offer) {
            $this->setFlash('error', 'Offre introuvable.');
            $this->redirect('/admin/offres');
            return;
        }

        $this->view('admin/offers/edit', [
            'title'     => 'Modifier une offre — Administration Le Commerce',
            'pageTitle' => 'Modifier « ' . $offer['title'] . ' »',
            'formAction'    => '/admin/offres/' . $id,
            'submitLabel'   => 'Enregistrer les modifications',
            'offer'         => $offer,
            'typeLabels'    => Offer::TYPE_LABELS,
            'segmentLabels' => Offer::SEGMENT_LABELS,
            'errors' => [],
            'old'    => [
                'title'       => $offer['title'],
                'description' => $offer['description'],
                'type'        => $offer['type'],
                'value'       => $offer['value'],
                'segment'     => $offer['target_segment'],
                'validUntil'  => $offer['valid_until'],
                'publish'     => $offer['status'] === 'active',
            ],
        ], 'admin');
    }

    public function update(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $offer = Offer::find($id);
        if (!$offer) {
            $this->setFlash('error', 'Offre introuvable.');
            $this->redirect('/admin/offres');
            return;
        }

        [$errors, $data] = $this->validate();

        if ($errors) {
            $this->view('admin/offers/edit', [
                'title'     => 'Modifier une offre — Administration Le Commerce',
                'pageTitle' => 'Modifier « ' . $offer['title'] . ' »',
                'formAction'    => '/admin/offres/' . $id,
                'submitLabel'   => 'Enregistrer les modifications',
                'offer'         => $offer,
                'typeLabels'    => Offer::TYPE_LABELS,
                'segmentLabels' => Offer::SEGMENT_LABELS,
                'errors' => $errors,
                'old'    => $data,
            ], 'admin');
            return;
        }

        Offer::update($id, [
            'title'          => $data['title'],
            'description'    => $data['description'] ?: null,
            'type'           => $data['type'],
            'value'          => (float) $data['value'],
            'target_segment' => $data['target_segment'],
            'valid_until'    => date('Y-m-d', strtotime($data['valid_until'])),
            'status'         => $data['publish'] ? 'active' : 'brouillon',
        ]);

        $this->setFlash('success', 'L\'offre "' . $data['title'] . '" a bien été mise à jour.');
        $this->redirect('/admin/offres');
    }

    public function destroy(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $offer = Offer::find($id);
        if (!$offer) {
            $this->setFlash('error', 'Offre introuvable.');
            $this->redirect('/admin/offres');
            return;
        }

        Offer::delete($id);

        $this->setFlash('success', 'L\'offre "' . $offer['title'] . '" a été supprimée.');
        $this->redirect('/admin/offres');
    }

    /**
     * @return array{0: array<string,string>, 1: array<string,mixed>} [errors, data]
     */
    private function validate(): array
    {
        $title       = trim((string) $this->input('title', ''));
        $description = trim((string) $this->input('description', ''));
        $type        = (string) $this->input('type', '');
        $value       = $this->input('value', '');
        $segment     = (string) $this->input('target_segment', 'tous');
        $validUntil  = (string) $this->input('valid_until', '');
        $publish     = (bool) $this->input('publish');

        $errors = [];
        if ($title === '' || mb_strlen($title) > 150) {
            $errors['title'] = 'Le titre est obligatoire (150 caractères maximum).';
        }
        if (!array_key_exists($type, Offer::TYPE_LABELS)) {
            $errors['type'] = 'Merci de choisir un type d\'offre.';
        }
        if ($value === '' || !is_numeric($value) || (float) $value < 0) {
            $errors['value'] = 'Merci d\'indiquer une valeur numérique valide.';
        }
        if (!array_key_exists($segment, Offer::SEGMENT_LABELS)) {
            $errors['target_segment'] = 'Segment cible invalide.';
        }
        if ($validUntil === '' || strtotime($validUntil) === false) {
            $errors['valid_until'] = 'La date de validité est invalide.';
        }

        return [$errors, compact('title', 'description', 'type', 'value', 'segment', 'validUntil', 'publish')];
    }

    public function toggleStatus(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $offer = Offer::find($id);
        if (!$offer) {
            $this->setFlash('error', 'Offre introuvable.');
            $this->redirect('/admin/offres');
            return;
        }

        $newStatus = $offer['status'] === 'active' ? 'brouillon' : 'active';
        Offer::update($id, ['status' => $newStatus]);

        $this->setFlash('success', 'Statut de l\'offre mis à jour.');
        $this->redirect('/admin/offres');
    }

    /**
     * Génère (ou récupère) un code/QR pour un couple client/offre donné.
     */
    public function generateCode(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $userId  = (int) $this->input('user_id', 0);
        $offerId = (int) $this->input('offer_id', 0);

        $user  = User::find($userId);
        $offer = Offer::find($offerId);

        if (!$user || !$offer) {
            $this->setFlash('error', 'Client ou offre introuvable.');
            $this->redirect('/admin/offres');
            return;
        }

        $redemption = OfferRedemption::generate($offerId, $userId, 'qr_caisse');

        $this->setFlash('success', 'Code généré pour ' . $user['first_name'] . ' ' . $user['last_name'] . ' : ' . $redemption['code']);
        $this->redirect('/admin/offres');
    }

    /**
     * Génère un code (si besoin) et l'envoie au client par WhatsApp
     * (journalisation ; l'envoi réel via l'API WhatsApp Business sera
     * branché au Lot 9).
     */
    public function sendToClient(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $userId  = (int) $this->input('user_id', 0);
        $offerId = (int) $this->input('offer_id', 0);

        $user  = User::find($userId);
        $offer = Offer::find($offerId);

        if (!$user || !$offer) {
            $this->setFlash('error', 'Client ou offre introuvable.');
            $this->redirect('/admin/offres');
            return;
        }

        $redemption = OfferRedemption::generate($offerId, $userId, 'whatsapp');

        $content = "🎁 Offre spéciale pour vous !\n" . $offer['title'] . "\n"
            . ($offer['description'] ? $offer['description'] . "\n" : '')
            . "Valable jusqu'au " . date('d/m/Y', strtotime($offer['valid_until'])) . "\n"
            . "Code : " . $redemption['code'] . "\n"
            . "Présentez ce code en caisse pour en profiter.";

        $this->notificationService->sendWhatsapp($userId, $content);

        $this->setFlash('success', 'Offre envoyée à ' . $user['first_name'] . ' ' . $user['last_name'] . ' par WhatsApp.');
        $this->redirect('/admin/offres');
    }
}
