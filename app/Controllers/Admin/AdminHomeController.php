<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\HomeCategory;
use App\Models\HomeChip;
use App\Models\HomeQuickService;
use App\Models\Settings;
use App\Service\HomeContentService;

/**
 * Gère tout le contenu éditorial de la page d'accueil publique (/) :
 * textes du hero et des cartes, tuiles de catégories, liste de services
 * du quotidien et suggestions de l'assistant. Les images restent gérées
 * depuis /admin/images (voir config/image_slots.php).
 */
class AdminHomeController extends Controller
{
    /**
     * Icônes proposées pour les tuiles de catégories (contenu SVG interne,
     * sans balise <svg> englobante — voir home/index.twig).
     */
    private const CATEGORY_ICON_CHOICES = [
        '<path d="M5 8h11v9a3 3 0 01-3 3H8a3 3 0 01-3-3V8z"></path><path d="M16 10h1.5a2.5 2.5 0 010 5H16"></path><path d="M8 8V5a1 1 0 011-1h1"></path><line x1="8" y1="12" x2="13" y2="12"></line>' => 'Chope / bar',
        '<rect x="3" y="9" width="18" height="12" rx="1"></rect><line x1="3" y1="13" x2="21" y2="13"></line><line x1="7" y1="9" x2="7" y2="13"></line><line x1="11" y1="9" x2="11" y2="13"></line><path d="M9 9V6a2 2 0 012-2h2a2 2 0 012 2v3"></path>' => 'Paquet / tabac',
        '<path d="M4 20v-3.5c0-.9.5-1.7 1.3-2.1L8 13"></path><path d="M8 13V8c0-2.8 2.2-5.5 5.5-5.5 1 0 1.9.3 2.7.8"></path><path d="M16.2 3.3c1.7 1 3.3 3 3.3 5.7 0 1.4-.4 2.3-1 3.3l-1.2 2v3.2c0 1.4.4 2 1.2 2.5"></path><path d="M8 13l3 1.2"></path><path d="M13 6.5c.6-.3 1.3-.3 1.8 0"></path>' => 'Cheval / PMU',
        '<path d="M12 12c0-2.5-2-4.5-4.5-4.5S3 9.5 3 12s2 4.5 4.5 4.5S12 14.5 12 12z"></path><path d="M12 12c0-2.5 2-4.5 4.5-4.5S21 9.5 21 12s-2 4.5-4.5 4.5S12 14.5 12 12z"></path><path d="M12 12c-2.5 0-4.5-2-4.5-4.5S9.5 3 12 3s4.5 2 4.5 4.5S14.5 12 12 12z"></path><path d="M12 12c-2.5 0-4.5 2-4.5 4.5S9.5 21 12 21s4.5-2 4.5-4.5S14.5 12 12 12z"></path><line x1="12" y1="13" x2="12" y2="19"></line>' => 'Trèfle / jeux',
        '<path d="M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z"></path><line x1="8" y1="8" x2="16" y2="8"></line><line x1="8" y1="12" x2="16" y2="12"></line><line x1="8" y1="16" x2="12" y2="16"></line>' => 'Journal / presse',
        '<polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><path d="M10 12v8M14 12v8"></path>' => 'Boîte / services',
        '<circle cx="12" cy="12" r="9"></circle><path d="M12 8v4l2.5 2.5"></path>' => 'Horloge (autre)',
        '<circle cx="12" cy="12" r="9"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line>' => 'Plus (autre)',
    ];

    /**
     * Icônes proposées pour la liste "Tous vos services du quotidien" —
     * clés utilisées telles quelles par le dictionnaire `serviceIcons` de
     * home/index.twig : ne pas renommer sans mettre à jour la vue.
     */
    private const QUICK_SERVICE_ICON_CHOICES = [
        'clipboard' => 'Presse-papier (factures)',
        'alert'     => 'Alerte (amendes)',
        'card'      => 'Carte (paysafecard)',
        'car'       => 'Voiture (covoiturage)',
        'bus'       => 'Bus',
        'package'   => 'Colis',
        'eye'       => 'Œil (retrait espèces)',
        'plus'      => 'Plus (autre)',
    ];

    public function index(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/home/index', [
            'title'     => 'Page principale — Administration Le Commerce',
            'pageTitle' => 'Page principale',
            'content'   => HomeContentService::get(),
            'categories'      => HomeCategory::listAllOrdered(),
            'categoryIcons'   => self::CATEGORY_ICON_CHOICES,
            'quickServices'       => HomeQuickService::listAllOrdered(),
            'quickServiceIcons'   => self::QUICK_SERVICE_ICON_CHOICES,
            'chips' => HomeChip::listAllOrdered(),
        ], 'admin');
    }

    public function updateContent(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $current = HomeContentService::get();
        $data = [];
        foreach (array_keys($current) as $key) {
            $data[$key] = trim((string) $this->input($key, $current[$key]));
        }

        Settings::updateMany($data);

        $this->setFlash('success', 'Le contenu de la page principale a bien été mis à jour.');
        $this->redirect('/admin/page-principale');
    }

    // ----------------------------------------------------------------
    // Catégories (tuiles sous le hero)
    // ----------------------------------------------------------------

    public function createCategory(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/home/category-create', [
            'title'     => 'Ajouter une catégorie — Administration Le Commerce',
            'pageTitle' => 'Ajouter une catégorie',
            'iconChoices' => self::CATEGORY_ICON_CHOICES,
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
            $this->view('admin/home/category-create', [
                'title'     => 'Ajouter une catégorie — Administration Le Commerce',
                'pageTitle' => 'Ajouter une catégorie',
                'iconChoices' => self::CATEGORY_ICON_CHOICES,
                'errors' => $errors,
                'old'    => $data,
            ], 'admin');
            return;
        }

        HomeCategory::create([
            'name'          => $data['name'],
            'description'   => $data['description'] ?: null,
            'icon'          => $data['icon'],
            'link'          => $data['link'],
            'display_order' => HomeCategory::nextDisplayOrder(),
            'status'        => $data['publish'] ? 'active' : 'inactif',
        ]);

        $this->setFlash('success', 'La catégorie "' . $data['name'] . '" a bien été ajoutée.');
        $this->redirect('/admin/page-principale');
    }

    public function editCategory(int $id): void
    {
        Middleware::requireRole('admin');

        $category = HomeCategory::find($id);
        if (!$category) {
            $this->setFlash('error', 'Catégorie introuvable.');
            $this->redirect('/admin/page-principale');
            return;
        }

        $this->view('admin/home/category-edit', [
            'title'     => 'Modifier une catégorie — Administration Le Commerce',
            'pageTitle' => 'Modifier « ' . $category['name'] . ' »',
            'category' => $category,
            'iconChoices' => self::CATEGORY_ICON_CHOICES,
            'errors'  => [],
            'old'     => [],
        ], 'admin');
    }

    public function updateCategory(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $category = HomeCategory::find($id);
        if (!$category) {
            $this->setFlash('error', 'Catégorie introuvable.');
            $this->redirect('/admin/page-principale');
            return;
        }

        [$errors, $data] = $this->validateCategory();

        if ($errors) {
            $this->view('admin/home/category-edit', [
                'title'     => 'Modifier une catégorie — Administration Le Commerce',
                'pageTitle' => 'Modifier « ' . $category['name'] . ' »',
                'category' => $category,
                'iconChoices' => self::CATEGORY_ICON_CHOICES,
                'errors'  => $errors,
                'old'     => $data,
            ], 'admin');
            return;
        }

        HomeCategory::update($id, [
            'name'        => $data['name'],
            'description' => $data['description'] ?: null,
            'icon'        => $data['icon'],
            'link'        => $data['link'],
            'status'      => $data['publish'] ? 'active' : 'inactif',
        ]);

        $this->setFlash('success', 'La catégorie "' . $data['name'] . '" a bien été mise à jour.');
        $this->redirect('/admin/page-principale');
    }

    public function toggleCategory(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $category = HomeCategory::find($id);
        if (!$category) {
            $this->setFlash('error', 'Catégorie introuvable.');
            $this->redirect('/admin/page-principale');
            return;
        }

        $newStatus = $category['status'] === 'active' ? 'inactif' : 'active';
        HomeCategory::update($id, ['status' => $newStatus]);

        $this->setFlash('success', 'Statut de la catégorie mis à jour.');
        $this->redirect('/admin/page-principale');
    }

    public function destroyCategory(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $category = HomeCategory::find($id);
        if (!$category) {
            $this->setFlash('error', 'Catégorie introuvable.');
            $this->redirect('/admin/page-principale');
            return;
        }

        HomeCategory::delete($id);

        $this->setFlash('success', 'La catégorie "' . $category['name'] . '" a été supprimée.');
        $this->redirect('/admin/page-principale');
    }

    // ----------------------------------------------------------------
    // Services du quotidien (carte "Tous vos services")
    // ----------------------------------------------------------------

    public function createQuickService(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/home/service-create', [
            'title'     => 'Ajouter un service — Administration Le Commerce',
            'pageTitle' => 'Ajouter un service',
            'iconChoices' => self::QUICK_SERVICE_ICON_CHOICES,
            'errors' => [],
            'old'    => [],
        ], 'admin');
    }

    public function storeQuickService(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        [$errors, $data] = $this->validateQuickService();

        if ($errors) {
            $this->view('admin/home/service-create', [
                'title'     => 'Ajouter un service — Administration Le Commerce',
                'pageTitle' => 'Ajouter un service',
                'iconChoices' => self::QUICK_SERVICE_ICON_CHOICES,
                'errors' => $errors,
                'old'    => $data,
            ], 'admin');
            return;
        }

        HomeQuickService::create([
            'label'         => $data['label'],
            'icon_key'      => $data['icon_key'],
            'color'         => $data['color'],
            'display_order' => HomeQuickService::nextDisplayOrder(),
            'status'        => $data['publish'] ? 'active' : 'inactif',
        ]);

        $this->setFlash('success', 'Le service "' . $data['label'] . '" a bien été ajouté.');
        $this->redirect('/admin/page-principale');
    }

    public function editQuickService(int $id): void
    {
        Middleware::requireRole('admin');

        $service = HomeQuickService::find($id);
        if (!$service) {
            $this->setFlash('error', 'Service introuvable.');
            $this->redirect('/admin/page-principale');
            return;
        }

        $this->view('admin/home/service-edit', [
            'title'     => 'Modifier un service — Administration Le Commerce',
            'pageTitle' => 'Modifier « ' . $service['label'] . ' »',
            'service' => $service,
            'iconChoices' => self::QUICK_SERVICE_ICON_CHOICES,
            'errors'  => [],
            'old'     => [],
        ], 'admin');
    }

    public function updateQuickService(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $service = HomeQuickService::find($id);
        if (!$service) {
            $this->setFlash('error', 'Service introuvable.');
            $this->redirect('/admin/page-principale');
            return;
        }

        [$errors, $data] = $this->validateQuickService();

        if ($errors) {
            $this->view('admin/home/service-edit', [
                'title'     => 'Modifier un service — Administration Le Commerce',
                'pageTitle' => 'Modifier « ' . $service['label'] . ' »',
                'service' => $service,
                'iconChoices' => self::QUICK_SERVICE_ICON_CHOICES,
                'errors'  => $errors,
                'old'     => $data,
            ], 'admin');
            return;
        }

        HomeQuickService::update($id, [
            'label'    => $data['label'],
            'icon_key' => $data['icon_key'],
            'color'    => $data['color'],
            'status'   => $data['publish'] ? 'active' : 'inactif',
        ]);

        $this->setFlash('success', 'Le service "' . $data['label'] . '" a bien été mis à jour.');
        $this->redirect('/admin/page-principale');
    }

    public function toggleQuickService(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $service = HomeQuickService::find($id);
        if (!$service) {
            $this->setFlash('error', 'Service introuvable.');
            $this->redirect('/admin/page-principale');
            return;
        }

        $newStatus = $service['status'] === 'active' ? 'inactif' : 'active';
        HomeQuickService::update($id, ['status' => $newStatus]);

        $this->setFlash('success', 'Statut du service mis à jour.');
        $this->redirect('/admin/page-principale');
    }

    public function destroyQuickService(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $service = HomeQuickService::find($id);
        if (!$service) {
            $this->setFlash('error', 'Service introuvable.');
            $this->redirect('/admin/page-principale');
            return;
        }

        HomeQuickService::delete($id);

        $this->setFlash('success', 'Le service "' . $service['label'] . '" a été supprimé.');
        $this->redirect('/admin/page-principale');
    }

    // ----------------------------------------------------------------
    // Suggestions de l'assistant
    // ----------------------------------------------------------------

    public function createChip(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/home/chip-create', [
            'title'     => 'Ajouter une suggestion — Administration Le Commerce',
            'pageTitle' => 'Ajouter une suggestion',
            'errors' => [],
            'old'    => [],
        ], 'admin');
    }

    public function storeChip(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        [$errors, $data] = $this->validateChip();

        if ($errors) {
            $this->view('admin/home/chip-create', [
                'title'     => 'Ajouter une suggestion — Administration Le Commerce',
                'pageTitle' => 'Ajouter une suggestion',
                'errors' => $errors,
                'old'    => $data,
            ], 'admin');
            return;
        }

        HomeChip::create([
            'label'         => $data['label'],
            'display_order' => HomeChip::nextDisplayOrder(),
            'status'        => $data['publish'] ? 'active' : 'inactif',
        ]);

        $this->setFlash('success', 'La suggestion "' . $data['label'] . '" a bien été ajoutée.');
        $this->redirect('/admin/page-principale');
    }

    public function editChip(int $id): void
    {
        Middleware::requireRole('admin');

        $chip = HomeChip::find($id);
        if (!$chip) {
            $this->setFlash('error', 'Suggestion introuvable.');
            $this->redirect('/admin/page-principale');
            return;
        }

        $this->view('admin/home/chip-edit', [
            'title'     => 'Modifier une suggestion — Administration Le Commerce',
            'pageTitle' => 'Modifier « ' . $chip['label'] . ' »',
            'chip'   => $chip,
            'errors' => [],
            'old'    => [],
        ], 'admin');
    }

    public function updateChip(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $chip = HomeChip::find($id);
        if (!$chip) {
            $this->setFlash('error', 'Suggestion introuvable.');
            $this->redirect('/admin/page-principale');
            return;
        }

        [$errors, $data] = $this->validateChip();

        if ($errors) {
            $this->view('admin/home/chip-edit', [
                'title'     => 'Modifier une suggestion — Administration Le Commerce',
                'pageTitle' => 'Modifier « ' . $chip['label'] . ' »',
                'chip'   => $chip,
                'errors' => $errors,
                'old'    => $data,
            ], 'admin');
            return;
        }

        HomeChip::update($id, [
            'label'  => $data['label'],
            'status' => $data['publish'] ? 'active' : 'inactif',
        ]);

        $this->setFlash('success', 'La suggestion "' . $data['label'] . '" a bien été mise à jour.');
        $this->redirect('/admin/page-principale');
    }

    public function toggleChip(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $chip = HomeChip::find($id);
        if (!$chip) {
            $this->setFlash('error', 'Suggestion introuvable.');
            $this->redirect('/admin/page-principale');
            return;
        }

        $newStatus = $chip['status'] === 'active' ? 'inactif' : 'active';
        HomeChip::update($id, ['status' => $newStatus]);

        $this->setFlash('success', 'Statut de la suggestion mis à jour.');
        $this->redirect('/admin/page-principale');
    }

    public function destroyChip(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $chip = HomeChip::find($id);
        if (!$chip) {
            $this->setFlash('error', 'Suggestion introuvable.');
            $this->redirect('/admin/page-principale');
            return;
        }

        HomeChip::delete($id);

        $this->setFlash('success', 'La suggestion "' . $chip['label'] . '" a été supprimée.');
        $this->redirect('/admin/page-principale');
    }

    // ----------------------------------------------------------------
    // Validation & valeurs par défaut
    // ----------------------------------------------------------------

    /**
     * @return array{0: array<string,string>, 1: array<string,mixed>} [errors, data]
     */
    private function validateCategory(): array
    {
        $name        = trim((string) $this->input('name', ''));
        $description = trim((string) $this->input('description', ''));
        $icon        = trim((string) $this->input('icon', ''));
        $link        = trim((string) $this->input('link', ''));
        $publish     = (bool) $this->input('publish');

        $errors = [];
        if ($name === '' || mb_strlen($name) > 100) {
            $errors['name'] = 'Le nom est obligatoire (100 caractères maximum).';
        }
        if ($icon === '') {
            $errors['icon'] = 'Merci de choisir une icône.';
        }
        if ($link === '' || mb_strlen($link) > 255) {
            $errors['link'] = 'Le lien est obligatoire (255 caractères maximum).';
        }

        return [$errors, compact('name', 'description', 'icon', 'link', 'publish')];
    }

    /**
     * @return array{0: array<string,string>, 1: array<string,mixed>} [errors, data]
     */
    private function validateQuickService(): array
    {
        $label    = trim((string) $this->input('label', ''));
        $icon_key = trim((string) $this->input('icon_key', ''));
        $color    = trim((string) $this->input('color', '#c8272c'));
        $publish  = (bool) $this->input('publish');

        $errors = [];
        if ($label === '' || mb_strlen($label) > 160) {
            $errors['label'] = 'Le libellé est obligatoire (160 caractères maximum).';
        }
        if (!isset(self::QUICK_SERVICE_ICON_CHOICES[$icon_key])) {
            $errors['icon_key'] = 'Merci de choisir une icône.';
        }
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $errors['color'] = 'Couleur invalide.';
        }

        return [$errors, compact('label', 'icon_key', 'color', 'publish')];
    }

    /**
     * @return array{0: array<string,string>, 1: array<string,mixed>} [errors, data]
     */
    private function validateChip(): array
    {
        $label   = trim((string) $this->input('label', ''));
        $publish = (bool) $this->input('publish');

        $errors = [];
        if ($label === '' || mb_strlen($label) > 160) {
            $errors['label'] = 'Le texte est obligatoire (160 caractères maximum).';
        }

        return [$errors, compact('label', 'publish')];
    }
}
