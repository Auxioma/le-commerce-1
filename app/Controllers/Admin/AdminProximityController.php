<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\Offer;
use App\Models\ProximityCampaign;

class AdminProximityController extends Controller
{
    public function index(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/proximity/index', [
            'title'     => 'Zonage & Proximité — Administration Le Commerce',
            'pageTitle' => 'Zonage & Proximité',

            'campaigns'       => ProximityCampaign::listWithOffer(),
            'campaignsActive' => ProximityCampaign::countByStatus('active'),
            'totalSent'       => ProximityCampaign::totalSent(),
            'totalUsed'       => ProximityCampaign::totalUsed(),

            'segmentLabels' => ProximityCampaign::SEGMENT_LABELS,
            'activeOffers'  => Offer::activeForSelect(),
            'shopLat'       => $this->sharedData['shop']['latitude'],
            'shopLng'       => $this->sharedData['shop']['longitude'],
        ], 'admin');
    }

    public function store(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        [$errors, $data] = $this->validate();

        if ($errors) {
            $this->setFlash('error', implode(' ', $errors));
            $this->redirect('/admin/zonage');
            return;
        }

        ProximityCampaign::create([
            'name'           => $data['name'],
            'radius_m'       => $data['radius'],
            'start_time'     => $data['startTime'],
            'end_time'       => $data['endTime'],
            'days'           => implode(',', $data['days']),
            'target_segment' => $data['segment'],
            'offer_id'       => $data['offerId'] !== '' ? (int) $data['offerId'] : null,
            'message'        => $data['message'],
            'status'         => $data['publish'] ? 'active' : 'en_pause',
        ]);

        $this->setFlash('success', 'La campagne "' . $data['name'] . '" a bien été créée.');
        $this->redirect('/admin/zonage');
    }

    public function edit(int $id): void
    {
        Middleware::requireRole('admin');

        $campaign = ProximityCampaign::find($id);
        if (!$campaign) {
            $this->setFlash('error', 'Campagne introuvable.');
            $this->redirect('/admin/zonage');
            return;
        }

        $this->view('admin/proximity/edit', [
            'title'     => 'Modifier une campagne — Administration Le Commerce',
            'pageTitle' => 'Modifier « ' . $campaign['name'] . ' »',

            'campaign'      => $campaign,
            'segmentLabels' => ProximityCampaign::SEGMENT_LABELS,
            'activeOffers'  => Offer::activeForSelect(),
            'shopLat'       => $this->sharedData['shop']['latitude'],
            'shopLng'       => $this->sharedData['shop']['longitude'],
            'errors'        => [],
        ], 'admin');
    }

    public function update(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $campaign = ProximityCampaign::find($id);
        if (!$campaign) {
            $this->setFlash('error', 'Campagne introuvable.');
            $this->redirect('/admin/zonage');
            return;
        }

        [$errors, $data] = $this->validate();

        if ($errors) {
            $this->setFlash('error', implode(' ', $errors));
            $this->redirect('/admin/zonage/' . $id . '/modifier');
            return;
        }

        ProximityCampaign::update($id, [
            'name'           => $data['name'],
            'radius_m'       => $data['radius'],
            'start_time'     => $data['startTime'],
            'end_time'       => $data['endTime'],
            'days'           => implode(',', $data['days']),
            'target_segment' => $data['segment'],
            'offer_id'       => $data['offerId'] !== '' ? (int) $data['offerId'] : null,
            'message'        => $data['message'],
            'status'         => $data['publish'] ? 'active' : 'en_pause',
        ]);

        $this->setFlash('success', 'La campagne "' . $data['name'] . '" a bien été mise à jour.');
        $this->redirect('/admin/zonage');
    }

    public function destroy(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $campaign = ProximityCampaign::find($id);
        if (!$campaign) {
            $this->setFlash('error', 'Campagne introuvable.');
            $this->redirect('/admin/zonage');
            return;
        }

        ProximityCampaign::delete($id);

        $this->setFlash('success', 'La campagne "' . $campaign['name'] . '" a été supprimée.');
        $this->redirect('/admin/zonage');
    }

    public function toggleStatus(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $campaign = ProximityCampaign::find($id);
        if (!$campaign) {
            $this->setFlash('error', 'Campagne introuvable.');
            $this->redirect('/admin/zonage');
            return;
        }

        $newStatus = $campaign['status'] === 'active' ? 'en_pause' : 'active';
        ProximityCampaign::update($id, ['status' => $newStatus]);

        $this->setFlash('success', 'Statut de la campagne mis à jour.');
        $this->redirect('/admin/zonage');
    }

    /**
     * @return array{0: array<int,string>, 1: array<string,mixed>} [errors, data]
     */
    private function validate(): array
    {
        $name      = trim((string) $this->input('name', ''));
        $radius    = (int) $this->input('radius_m', 500);
        $startTime = (string) $this->input('start_time', '');
        $endTime   = (string) $this->input('end_time', '');
        $days      = array_filter((array) $this->input('days', []));
        $segment   = (string) $this->input('target_segment', 'tous');
        $offerId   = (string) $this->input('offer_id', '');
        $message   = trim((string) $this->input('message', ''));
        $publish   = (bool) $this->input('publish');

        $errors = [];
        if ($name === '' || mb_strlen($name) > 120) {
            $errors[] = 'Le nom de la campagne est obligatoire (120 caractères maximum).';
        }
        if ($radius < 100 || $radius > 5000) {
            $errors[] = 'Le rayon doit être compris entre 100 m et 5 km.';
        }
        if ($startTime === '' || $endTime === '' || $startTime >= $endTime) {
            $errors[] = 'La plage horaire est invalide (heure de fin après heure de début).';
        }
        if (empty($days)) {
            $errors[] = 'Merci de sélectionner au moins un jour de diffusion.';
        }
        if ($message === '' || mb_strlen($message) > 160) {
            $errors[] = 'Le message est obligatoire (160 caractères maximum).';
        }

        return [$errors, compact('name', 'radius', 'startTime', 'endTime', 'days', 'segment', 'offerId', 'message', 'publish')];
    }
}
