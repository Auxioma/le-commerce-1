<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\Service;

class AdminServiceController extends Controller
{
    /**
     * Choix d'icônes proposés dans le formulaire (mêmes tracés SVG que le
     * catalogue historique de ServicesController, réutilisables sur tout
     * nouveau service). Le champ reste un texte libre pour permettre de
     * coller n'importe quel autre chemin SVG "path d=...".
     */
    private const ICON_CHOICES = [
        'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' => 'Colis / livraison',
        'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2z' => 'Facture / paiement',
        'M3 10h18M7 15h1m4 0h1m-9 4h16a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' => 'Carte prépayée',
        'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-2m-9-5h9m0 0l-3-3m3 3l-3 3' => 'Espèces / retrait',
        'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' => 'Transport / réservation',
        'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' => 'Document / impression',
        'M13 10V3L4 14h7v7l9-11h-7z' => 'Recharge / énergie',
        'M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0-6a9 9 0 100 18 9 9 0 000-18z' => 'Autre service',
    ];

    public function index(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/services/index', [
            'title'     => 'Services du quotidien — Administration Le Commerce',
            'pageTitle' => 'Services du quotidien',
            'services'      => Service::listAllOrdered(),
            'activeCount'   => Service::countByStatus('active'),
            'inactiveCount' => Service::countByStatus('inactif'),
            'newThisMonth'  => Service::countCreatedThisMonth(),
        ], 'admin');
    }

    public function create(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/services/create', [
            'title'     => 'Ajouter un service — Administration Le Commerce',
            'pageTitle' => 'Ajouter un service',
            'iconChoices' => self::ICON_CHOICES,
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
            $this->view('admin/services/create', [
                'title'     => 'Ajouter un service — Administration Le Commerce',
                'pageTitle' => 'Ajouter un service',
                'iconChoices' => self::ICON_CHOICES,
                'errors' => $errors,
                'old'    => $data,
            ], 'admin');
            return;
        }

        Service::create([
            'slug'        => Service::generateUniqueSlug($data['name']),
            'name'        => $data['name'],
            'description' => $data['description'] ?: null,
            'icon'        => $data['icon'],
            'sort_order'  => Service::nextSortOrder(),
            'status'      => $data['publish'] ? 'active' : 'inactif',
        ]);

        $this->setFlash('success', 'Le service "' . $data['name'] . '" a bien été créé.');
        $this->redirect('/admin/services');
    }

    public function edit(int $id): void
    {
        Middleware::requireRole('admin');

        $service = Service::find($id);
        if (!$service) {
            $this->setFlash('error', 'Service introuvable.');
            $this->redirect('/admin/services');
            return;
        }

        $this->view('admin/services/edit', [
            'title'     => 'Modifier un service — Administration Le Commerce',
            'pageTitle' => 'Modifier « ' . $service['name'] . ' »',
            'service' => $service,
            'iconChoices' => self::ICON_CHOICES,
            'errors'  => [],
            'old'     => [],
        ], 'admin');
    }

    public function update(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $service = Service::find($id);
        if (!$service) {
            $this->setFlash('error', 'Service introuvable.');
            $this->redirect('/admin/services');
            return;
        }

        [$errors, $data] = $this->validate();

        if ($errors) {
            $this->view('admin/services/edit', [
                'title'     => 'Modifier un service — Administration Le Commerce',
                'pageTitle' => 'Modifier « ' . $service['name'] . ' »',
                'service' => $service,
                'iconChoices' => self::ICON_CHOICES,
                'errors'  => $errors,
                'old'     => $data,
            ], 'admin');
            return;
        }

        Service::update($id, [
            'name'        => $data['name'],
            'description' => $data['description'] ?: null,
            'icon'        => $data['icon'],
            'status'      => $data['publish'] ? 'active' : 'inactif',
        ]);

        $this->setFlash('success', 'Le service "' . $data['name'] . '" a bien été mis à jour.');
        $this->redirect('/admin/services');
    }

    public function toggleStatus(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $service = Service::find($id);
        if (!$service) {
            $this->setFlash('error', 'Service introuvable.');
            $this->redirect('/admin/services');
            return;
        }

        $newStatus = $service['status'] === 'active' ? 'inactif' : 'active';
        Service::update($id, ['status' => $newStatus]);

        $this->setFlash('success', 'Statut du service mis à jour.');
        $this->redirect('/admin/services');
    }

    public function destroy(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $service = Service::find($id);
        if (!$service) {
            $this->setFlash('error', 'Service introuvable.');
            $this->redirect('/admin/services');
            return;
        }

        Service::delete($id);

        $this->setFlash('success', 'Le service "' . $service['name'] . '" a été supprimé.');
        $this->redirect('/admin/services');
    }

    /**
     * @return array{0: array<string,string>, 1: array<string,mixed>} [errors, data]
     */
    private function validate(): array
    {
        $name        = trim((string) $this->input('name', ''));
        $description = trim((string) $this->input('description', ''));
        $icon        = trim((string) $this->input('icon', ''));
        $publish     = (bool) $this->input('publish');

        $errors = [];
        if ($name === '' || mb_strlen($name) > 100) {
            $errors['name'] = 'Le nom est obligatoire (100 caractères maximum).';
        }
        if ($icon === '') {
            $errors['icon'] = 'Merci de choisir une icône.';
        }

        return [$errors, compact('name', 'description', 'icon', 'publish')];
    }
}
