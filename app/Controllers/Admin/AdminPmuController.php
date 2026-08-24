<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\PmuCategory;
use App\Models\PmuService;

/**
 * Gère le contenu affiché sur la page publique /pmu : catégories de paris
 * (CRUD complet, table `pmu_categories`) et autres services du point PMU
 * (liste ordonnée, table `pmu_services`).
 */
class AdminPmuController extends Controller
{
    /**
     * Choix d'icônes proposés dans le formulaire (mêmes tracés SVG que le
     * catalogue historique de PmuController). Un texte libre reste possible
     * côté modèle si besoin d'un tracé SVG "path d=..." différent, mais le
     * formulaire ne propose que ces choix pour rester simple.
     */
    private const ICON_CHOICES = [
        'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' => 'Simple gagnant / placé',
        'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4' => 'Couplé / Trio',
        'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z' => 'Quinté+',
        'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14' => 'Multi',
        'M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0-6a9 9 0 100 18 9 9 0 000-18z' => 'Autre catégorie',
    ];

    private const ALLOWED_IMAGE_MIME = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    private const MAX_IMAGE_SIZE = 5 * 1024 * 1024; // 5 Mo

    public function index(): void
    {
        Middleware::requireRole('admin');

        $categories = PmuCategory::listAllOrdered();

        $this->view('admin/pmu/index', [
            'title'     => 'PMU — Administration Le Commerce',
            'pageTitle' => 'PMU',
            'categories'    => $categories,
            'activeCount'   => PmuCategory::countByStatus('active'),
            'inactiveCount' => PmuCategory::countByStatus('inactif'),
            'services'      => PmuService::listAllOrdered(),
        ], 'admin');
    }

    public function createCategory(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/pmu/category-create', [
            'title'     => 'Ajouter une catégorie — Administration Le Commerce',
            'pageTitle' => 'Ajouter une catégorie',
            'iconChoices' => self::ICON_CHOICES,
            'errors' => [],
            'old'    => [],
        ], 'admin');
    }

    public function storeCategory(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        [$errors, $data] = $this->validateCategory();

        if ($errors) {
            $this->view('admin/pmu/category-create', [
                'title'     => 'Ajouter une catégorie — Administration Le Commerce',
                'pageTitle' => 'Ajouter une catégorie',
                'iconChoices' => self::ICON_CHOICES,
                'errors' => $errors,
                'old'    => $data,
            ], 'admin');
            return;
        }

        PmuCategory::create([
            'name'          => $data['name'],
            'description'   => $data['description'],
            'icon'          => $data['icon'],
            'image'         => $this->uploadCategoryImage(null),
            'display_order' => PmuCategory::nextDisplayOrder(),
            'status'        => $data['publish'] ? 'active' : 'inactif',
        ]);

        $this->setFlash('success', 'La catégorie "' . $data['name'] . '" a bien été ajoutée.');
        $this->redirect('/admin/pmu');
    }

    public function editCategory(int $id): void
    {
        Middleware::requireRole('admin');

        $category = PmuCategory::find($id);
        if (!$category) {
            $this->setFlash('error', 'Catégorie introuvable.');
            $this->redirect('/admin/pmu');
            return;
        }

        $this->view('admin/pmu/category-edit', [
            'title'     => 'Modifier une catégorie — Administration Le Commerce',
            'pageTitle' => 'Modifier « ' . $category['name'] . ' »',
            'category' => $category,
            'iconChoices' => self::ICON_CHOICES,
            'errors'  => [],
            'old'     => [],
        ], 'admin');
    }

    public function updateCategory(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $category = PmuCategory::find($id);
        if (!$category) {
            $this->setFlash('error', 'Catégorie introuvable.');
            $this->redirect('/admin/pmu');
            return;
        }

        [$errors, $data] = $this->validateCategory();

        if ($errors) {
            $this->view('admin/pmu/category-edit', [
                'title'     => 'Modifier une catégorie — Administration Le Commerce',
                'pageTitle' => 'Modifier « ' . $category['name'] . ' »',
                'category' => $category,
                'iconChoices' => self::ICON_CHOICES,
                'errors'  => $errors,
                'old'     => $data,
            ], 'admin');
            return;
        }

        PmuCategory::update($id, [
            'name'        => $data['name'],
            'description' => $data['description'],
            'icon'        => $data['icon'],
            'image'       => $this->uploadCategoryImage($category['image']),
            'status'      => $data['publish'] ? 'active' : 'inactif',
        ]);

        $this->setFlash('success', 'La catégorie "' . $data['name'] . '" a bien été mise à jour.');
        $this->redirect('/admin/pmu');
    }

    public function toggleCategory(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $category = PmuCategory::find($id);
        if (!$category) {
            $this->setFlash('error', 'Catégorie introuvable.');
            $this->redirect('/admin/pmu');
            return;
        }

        $newStatus = $category['status'] === 'active' ? 'inactif' : 'active';
        PmuCategory::update($id, ['status' => $newStatus]);

        $this->setFlash('success', 'Statut de la catégorie mis à jour.');
        $this->redirect('/admin/pmu');
    }

    public function destroyCategory(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $category = PmuCategory::find($id);
        if (!$category) {
            $this->setFlash('error', 'Catégorie introuvable.');
            $this->redirect('/admin/pmu');
            return;
        }

        if ($category['image']) {
            $this->deleteCategoryImageFile($category['image']);
        }
        PmuCategory::delete($id);

        $this->setFlash('success', 'La catégorie "' . $category['name'] . '" a été supprimée.');
        $this->redirect('/admin/pmu');
    }

    public function createService(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/pmu/service-create', [
            'title'     => 'Ajouter un service — Administration Le Commerce',
            'pageTitle' => 'Ajouter un service',
            'errors' => [],
            'old'    => [],
        ], 'admin');
    }

    public function storeService(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        [$errors, $data] = $this->validateService();

        if ($errors) {
            $this->view('admin/pmu/service-create', [
                'title'     => 'Ajouter un service — Administration Le Commerce',
                'pageTitle' => 'Ajouter un service',
                'errors' => $errors,
                'old'    => $data,
            ], 'admin');
            return;
        }

        PmuService::create([
            'name'          => $data['name'],
            'display_order' => PmuService::nextDisplayOrder(),
        ]);

        $this->setFlash('success', 'Le service "' . $data['name'] . '" a bien été ajouté.');
        $this->redirect('/admin/pmu');
    }

    public function editService(int $id): void
    {
        Middleware::requireRole('admin');

        $service = PmuService::find($id);
        if (!$service) {
            $this->setFlash('error', 'Service introuvable.');
            $this->redirect('/admin/pmu');
            return;
        }

        $this->view('admin/pmu/service-edit', [
            'title'     => 'Modifier un service — Administration Le Commerce',
            'pageTitle' => 'Modifier « ' . $service['name'] . ' »',
            'service' => $service,
            'errors'  => [],
            'old'     => [],
        ], 'admin');
    }

    public function updateService(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $service = PmuService::find($id);
        if (!$service) {
            $this->setFlash('error', 'Service introuvable.');
            $this->redirect('/admin/pmu');
            return;
        }

        [$errors, $data] = $this->validateService();

        if ($errors) {
            $this->view('admin/pmu/service-edit', [
                'title'     => 'Modifier un service — Administration Le Commerce',
                'pageTitle' => 'Modifier « ' . $service['name'] . ' »',
                'service' => $service,
                'errors'  => $errors,
                'old'     => $data,
            ], 'admin');
            return;
        }

        PmuService::update($id, ['name' => $data['name']]);

        $this->setFlash('success', 'Le service "' . $data['name'] . '" a bien été mis à jour.');
        $this->redirect('/admin/pmu');
    }

    public function destroyService(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $service = PmuService::find($id);
        if (!$service) {
            $this->setFlash('error', 'Service introuvable.');
            $this->redirect('/admin/pmu');
            return;
        }

        PmuService::delete($id);

        $this->setFlash('success', 'Le service "' . $service['name'] . '" a été supprimé.');
        $this->redirect('/admin/pmu');
    }

    /**
     * @return array{0: array<string,string>, 1: array<string,mixed>} [errors, data]
     */
    private function validateCategory(): array
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

        $file = $_FILES['image'] ?? null;
        if ($file && $file['error'] !== UPLOAD_ERR_OK && $file['error'] !== UPLOAD_ERR_NO_FILE) {
            $errors['image'] = "Échec de l'envoi de l'image.";
        } elseif ($file && $file['error'] === UPLOAD_ERR_OK) {
            if ($file['size'] > self::MAX_IMAGE_SIZE) {
                $errors['image'] = 'Le fichier dépasse la taille maximale autorisée (5 Mo).';
            } elseif (!isset(self::ALLOWED_IMAGE_MIME[mime_content_type($file['tmp_name'])])) {
                $errors['image'] = 'Format non supporté. Utilisez une image JPEG, PNG ou WEBP.';
            }
        }

        return [$errors, compact('name', 'description', 'icon', 'publish')];
    }

    /**
     * Traite un éventuel nouveau fichier envoyé pour le champ "image" d'une
     * catégorie : si un fichier valide a été fourni, l'enregistre et
     * supprime l'ancien visuel ; sinon conserve $existingImage inchangé.
     */
    private function uploadCategoryImage(?string $existingImage): ?string
    {
        $file = $_FILES['image'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return $existingImage;
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!isset(self::ALLOWED_IMAGE_MIME[$mime])) {
            return $existingImage;
        }

        $publicDir = dirname(__DIR__, 3) . '/public';
        $uploadDir = $publicDir . '/uploads/images';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $filename = 'pmu_categorie_' . bin2hex(random_bytes(6)) . '.' . self::ALLOWED_IMAGE_MIME[$mime];
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename)) {
            return $existingImage;
        }

        if ($existingImage) {
            $this->deleteCategoryImageFile($existingImage);
        }

        return '/uploads/images/' . $filename;
    }

    private function deleteCategoryImageFile(string $image): void
    {
        if (str_starts_with($image, '/uploads/images/')) {
            @unlink(dirname(__DIR__, 3) . '/public' . $image);
        }
    }

    /**
     * @return array{0: array<string,string>, 1: array<string,mixed>} [errors, data]
     */
    private function validateService(): array
    {
        $name = trim((string) $this->input('name', ''));

        $errors = [];
        if ($name === '' || mb_strlen($name) > 160) {
            $errors['name'] = 'Le nom est obligatoire (160 caractères maximum).';
        }

        return [$errors, ['name' => $name]];
    }
}
