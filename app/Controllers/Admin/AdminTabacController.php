<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\TabacCategory;
use App\Models\TabacService;

/**
 * Gère le contenu affiché sur la page publique /tabac : catégories de
 * produits (CRUD complet, table `tabac_categories`) et autres services du
 * bureau de tabac (liste ordonnée, table `tabac_services`).
 */
class AdminTabacController extends Controller
{
    /**
     * Choix d'icônes proposés dans le formulaire (mêmes tracés SVG que le
     * catalogue historique de TabacController). Un texte libre reste
     * possible côté modèle si besoin d'un tracé SVG "path d=..." différent,
     * mais le formulaire ne propose que ces choix pour rester simple.
     */
    private const ICON_CHOICES = [
        'M9 3v2m6-2v2M4 7h16M5 7h14v12a2 2 0 01-2 2H7a2 2 0 01-2-2V7z' => 'Cigarettes / tabac à rouler',
        'M4 12h16M4 12a2 2 0 100 4h10a2 2 0 100-4M4 12a2 2 0 110-4h10a2 2 0 110 4' => 'Cigares / cigarillos',
        'M13 10V3L4 14h7v7l9-11h-7z' => 'Cigarette électronique',
        'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l7-3 7 3z' => 'Papiers / accessoires',
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

        $categories = TabacCategory::listAllOrdered();

        $this->view('admin/tabac/index', [
            'title'     => 'Tabac — Administration Le Commerce',
            'pageTitle' => 'Tabac',
            'categories'    => $categories,
            'activeCount'   => TabacCategory::countByStatus('active'),
            'inactiveCount' => TabacCategory::countByStatus('inactif'),
            'services'      => TabacService::listAllOrdered(),
        ], 'admin');
    }

    public function createCategory(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/tabac/category-create', [
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
            $this->view('admin/tabac/category-create', [
                'title'     => 'Ajouter une catégorie — Administration Le Commerce',
                'pageTitle' => 'Ajouter une catégorie',
                'iconChoices' => self::ICON_CHOICES,
                'errors' => $errors,
                'old'    => $data,
            ], 'admin');
            return;
        }

        TabacCategory::create([
            'name'          => $data['name'],
            'description'   => $data['description'],
            'icon'          => $data['icon'],
            'image'         => $this->uploadCategoryImage(null),
            'display_order' => TabacCategory::nextDisplayOrder(),
            'status'        => $data['publish'] ? 'active' : 'inactif',
        ]);

        $this->setFlash('success', 'La catégorie "' . $data['name'] . '" a bien été ajoutée.');
        $this->redirect('/admin/tabac');
    }

    public function editCategory(int $id): void
    {
        Middleware::requireRole('admin');

        $category = TabacCategory::find($id);
        if (!$category) {
            $this->setFlash('error', 'Catégorie introuvable.');
            $this->redirect('/admin/tabac');
            return;
        }

        $this->view('admin/tabac/category-edit', [
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

        $category = TabacCategory::find($id);
        if (!$category) {
            $this->setFlash('error', 'Catégorie introuvable.');
            $this->redirect('/admin/tabac');
            return;
        }

        [$errors, $data] = $this->validateCategory();

        if ($errors) {
            $this->view('admin/tabac/category-edit', [
                'title'     => 'Modifier une catégorie — Administration Le Commerce',
                'pageTitle' => 'Modifier « ' . $category['name'] . ' »',
                'category' => $category,
                'iconChoices' => self::ICON_CHOICES,
                'errors'  => $errors,
                'old'     => $data,
            ], 'admin');
            return;
        }

        TabacCategory::update($id, [
            'name'        => $data['name'],
            'description' => $data['description'],
            'icon'        => $data['icon'],
            'image'       => $this->uploadCategoryImage($category['image']),
            'status'      => $data['publish'] ? 'active' : 'inactif',
        ]);

        $this->setFlash('success', 'La catégorie "' . $data['name'] . '" a bien été mise à jour.');
        $this->redirect('/admin/tabac');
    }

    public function toggleCategory(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $category = TabacCategory::find($id);
        if (!$category) {
            $this->setFlash('error', 'Catégorie introuvable.');
            $this->redirect('/admin/tabac');
            return;
        }

        $newStatus = $category['status'] === 'active' ? 'inactif' : 'active';
        TabacCategory::update($id, ['status' => $newStatus]);

        $this->setFlash('success', 'Statut de la catégorie mis à jour.');
        $this->redirect('/admin/tabac');
    }

    public function destroyCategory(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $category = TabacCategory::find($id);
        if (!$category) {
            $this->setFlash('error', 'Catégorie introuvable.');
            $this->redirect('/admin/tabac');
            return;
        }

        if ($category['image']) {
            $this->deleteCategoryImageFile($category['image']);
        }
        TabacCategory::delete($id);

        $this->setFlash('success', 'La catégorie "' . $category['name'] . '" a été supprimée.');
        $this->redirect('/admin/tabac');
    }

    public function createService(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/tabac/service-create', [
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
            $this->view('admin/tabac/service-create', [
                'title'     => 'Ajouter un service — Administration Le Commerce',
                'pageTitle' => 'Ajouter un service',
                'errors' => $errors,
                'old'    => $data,
            ], 'admin');
            return;
        }

        TabacService::create([
            'name'          => $data['name'],
            'display_order' => TabacService::nextDisplayOrder(),
        ]);

        $this->setFlash('success', 'Le service "' . $data['name'] . '" a bien été ajouté.');
        $this->redirect('/admin/tabac');
    }

    public function editService(int $id): void
    {
        Middleware::requireRole('admin');

        $service = TabacService::find($id);
        if (!$service) {
            $this->setFlash('error', 'Service introuvable.');
            $this->redirect('/admin/tabac');
            return;
        }

        $this->view('admin/tabac/service-edit', [
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

        $service = TabacService::find($id);
        if (!$service) {
            $this->setFlash('error', 'Service introuvable.');
            $this->redirect('/admin/tabac');
            return;
        }

        [$errors, $data] = $this->validateService();

        if ($errors) {
            $this->view('admin/tabac/service-edit', [
                'title'     => 'Modifier un service — Administration Le Commerce',
                'pageTitle' => 'Modifier « ' . $service['name'] . ' »',
                'service' => $service,
                'errors'  => $errors,
                'old'     => $data,
            ], 'admin');
            return;
        }

        TabacService::update($id, ['name' => $data['name']]);

        $this->setFlash('success', 'Le service "' . $data['name'] . '" a bien été mis à jour.');
        $this->redirect('/admin/tabac');
    }

    public function destroyService(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $service = TabacService::find($id);
        if (!$service) {
            $this->setFlash('error', 'Service introuvable.');
            $this->redirect('/admin/tabac');
            return;
        }

        TabacService::delete($id);

        $this->setFlash('success', 'Le service "' . $service['name'] . '" a été supprimé.');
        $this->redirect('/admin/tabac');
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

        $filename = 'tabac_categorie_' . bin2hex(random_bytes(6)) . '.' . self::ALLOWED_IMAGE_MIME[$mime];
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
