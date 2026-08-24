<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\BarPlanche;
use App\Models\BarSoft;
use App\Models\Drink;

/**
 * Gère le contenu affiché sur la page publique /le-bar : bières à la carte,
 * planches à partager et softs & boissons chaudes — CRUD complet sur les
 * tables `drinks`, `bar_planches` et `bar_softs`.
 */
class AdminBarController extends Controller
{
    private const ALLOWED_IMAGE_MIME = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    private const MAX_IMAGE_SIZE = 5 * 1024 * 1024; // 5 Mo

    public function index(): void
    {
        Middleware::requireRole('admin');

        $drinks = Drink::listAllOrdered();
        $countByCategory = [];
        foreach (Drink::CATEGORIES as $key => $label) {
            $countByCategory[$key] = 0;
        }
        foreach ($drinks as $drink) {
            $countByCategory[$drink['category']] = ($countByCategory[$drink['category']] ?? 0) + 1;
        }

        $this->view('admin/bar/index', [
            'title'     => 'Le Bar — Administration Le Commerce',
            'pageTitle' => 'Le Bar',
            'drinks'          => $drinks,
            'categories'      => Drink::CATEGORIES,
            'countByCategory' => $countByCategory,
            'totalDrinks'     => count($drinks),
            'planches'        => BarPlanche::listAllOrdered(),
            'softs'           => BarSoft::listAllOrdered(),
        ], 'admin');
    }

    public function createDrink(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/bar/create', [
            'title'     => 'Ajouter une boisson — Administration Le Commerce',
            'pageTitle' => 'Ajouter une boisson',
            'categories' => Drink::CATEGORIES,
            'errors' => [],
            'old'    => [],
        ], 'admin');
    }

    public function storeDrink(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        [$errors, $data] = $this->validateDrink();

        if ($errors) {
            $this->view('admin/bar/create', [
                'title'     => 'Ajouter une boisson — Administration Le Commerce',
                'pageTitle' => 'Ajouter une boisson',
                'categories' => Drink::CATEGORIES,
                'errors' => $errors,
                'old'    => $data,
            ], 'admin');
            return;
        }

        Drink::create([
            'name'          => $data['name'],
            'category'      => $data['category'],
            'degree'        => $data['degree'],
            'price'         => $data['price'],
            'display_order' => Drink::nextDisplayOrder($data['category']),
        ]);

        $this->setFlash('success', 'La boisson "' . $data['name'] . '" a bien été ajoutée.');
        $this->redirect('/admin/bar');
    }

    public function editDrink(int $id): void
    {
        Middleware::requireRole('admin');

        $drink = Drink::find($id);
        if (!$drink) {
            $this->setFlash('error', 'Boisson introuvable.');
            $this->redirect('/admin/bar');
            return;
        }

        $this->view('admin/bar/edit', [
            'title'     => 'Modifier une boisson — Administration Le Commerce',
            'pageTitle' => 'Modifier « ' . $drink['name'] . ' »',
            'drink'      => $drink,
            'categories' => Drink::CATEGORIES,
            'errors'  => [],
            'old'     => [],
        ], 'admin');
    }

    public function updateDrink(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $drink = Drink::find($id);
        if (!$drink) {
            $this->setFlash('error', 'Boisson introuvable.');
            $this->redirect('/admin/bar');
            return;
        }

        [$errors, $data] = $this->validateDrink();

        if ($errors) {
            $this->view('admin/bar/edit', [
                'title'     => 'Modifier une boisson — Administration Le Commerce',
                'pageTitle' => 'Modifier « ' . $drink['name'] . ' »',
                'drink'      => $drink,
                'categories' => Drink::CATEGORIES,
                'errors'  => $errors,
                'old'     => $data,
            ], 'admin');
            return;
        }

        Drink::update($id, [
            'name'     => $data['name'],
            'category' => $data['category'],
            'degree'   => $data['degree'],
            'price'    => $data['price'],
        ]);

        $this->setFlash('success', 'La boisson "' . $data['name'] . '" a bien été mise à jour.');
        $this->redirect('/admin/bar');
    }

    public function destroyDrink(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $drink = Drink::find($id);
        if (!$drink) {
            $this->setFlash('error', 'Boisson introuvable.');
            $this->redirect('/admin/bar');
            return;
        }

        Drink::delete($id);

        $this->setFlash('success', 'La boisson "' . $drink['name'] . '" a été supprimée.');
        $this->redirect('/admin/bar');
    }

    public function createPlanche(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/bar/planche-create', [
            'title'     => 'Ajouter une planche — Administration Le Commerce',
            'pageTitle' => 'Ajouter une planche',
            'errors' => [],
            'old'    => [],
        ], 'admin');
    }

    public function storePlanche(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        [$errors, $data] = $this->validatePlanche();

        if ($errors) {
            $this->view('admin/bar/planche-create', [
                'title'     => 'Ajouter une planche — Administration Le Commerce',
                'pageTitle' => 'Ajouter une planche',
                'errors' => $errors,
                'old'    => $data,
            ], 'admin');
            return;
        }

        BarPlanche::create([
            'name'          => $data['name'],
            'description'   => $data['description'],
            'price'         => $data['price'],
            'image'         => $this->uploadPlancheImage(null),
            'display_order' => BarPlanche::nextDisplayOrder(),
            'status'        => $data['publish'] ? 'active' : 'inactif',
        ]);

        $this->setFlash('success', 'La planche "' . $data['name'] . '" a bien été ajoutée.');
        $this->redirect('/admin/bar');
    }

    public function editPlanche(int $id): void
    {
        Middleware::requireRole('admin');

        $planche = BarPlanche::find($id);
        if (!$planche) {
            $this->setFlash('error', 'Planche introuvable.');
            $this->redirect('/admin/bar');
            return;
        }

        $this->view('admin/bar/planche-edit', [
            'title'     => 'Modifier une planche — Administration Le Commerce',
            'pageTitle' => 'Modifier « ' . $planche['name'] . ' »',
            'planche' => $planche,
            'errors'  => [],
            'old'     => [],
        ], 'admin');
    }

    public function updatePlanche(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $planche = BarPlanche::find($id);
        if (!$planche) {
            $this->setFlash('error', 'Planche introuvable.');
            $this->redirect('/admin/bar');
            return;
        }

        [$errors, $data] = $this->validatePlanche();

        if ($errors) {
            $this->view('admin/bar/planche-edit', [
                'title'     => 'Modifier une planche — Administration Le Commerce',
                'pageTitle' => 'Modifier « ' . $planche['name'] . ' »',
                'planche' => $planche,
                'errors'  => $errors,
                'old'     => $data,
            ], 'admin');
            return;
        }

        BarPlanche::update($id, [
            'name'        => $data['name'],
            'description' => $data['description'],
            'price'       => $data['price'],
            'image'       => $this->uploadPlancheImage($planche['image']),
            'status'      => $data['publish'] ? 'active' : 'inactif',
        ]);

        $this->setFlash('success', 'La planche "' . $data['name'] . '" a bien été mise à jour.');
        $this->redirect('/admin/bar');
    }

    public function togglePlanche(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $planche = BarPlanche::find($id);
        if (!$planche) {
            $this->setFlash('error', 'Planche introuvable.');
            $this->redirect('/admin/bar');
            return;
        }

        $newStatus = $planche['status'] === 'active' ? 'inactif' : 'active';
        BarPlanche::update($id, ['status' => $newStatus]);

        $this->setFlash('success', 'Statut de la planche mis à jour.');
        $this->redirect('/admin/bar');
    }

    public function destroyPlanche(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $planche = BarPlanche::find($id);
        if (!$planche) {
            $this->setFlash('error', 'Planche introuvable.');
            $this->redirect('/admin/bar');
            return;
        }

        if ($planche['image']) {
            $this->deletePlancheImageFile($planche['image']);
        }
        BarPlanche::delete($id);

        $this->setFlash('success', 'La planche "' . $planche['name'] . '" a été supprimée.');
        $this->redirect('/admin/bar');
    }

    public function createSoft(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/bar/soft-create', [
            'title'     => 'Ajouter un soft — Administration Le Commerce',
            'pageTitle' => 'Ajouter un soft',
            'errors' => [],
            'old'    => [],
        ], 'admin');
    }

    public function storeSoft(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        [$errors, $data] = $this->validateSoft();

        if ($errors) {
            $this->view('admin/bar/soft-create', [
                'title'     => 'Ajouter un soft — Administration Le Commerce',
                'pageTitle' => 'Ajouter un soft',
                'errors' => $errors,
                'old'    => $data,
            ], 'admin');
            return;
        }

        BarSoft::create([
            'name'          => $data['name'],
            'display_order' => BarSoft::nextDisplayOrder(),
        ]);

        $this->setFlash('success', 'Le soft "' . $data['name'] . '" a bien été ajouté.');
        $this->redirect('/admin/bar');
    }

    public function editSoft(int $id): void
    {
        Middleware::requireRole('admin');

        $soft = BarSoft::find($id);
        if (!$soft) {
            $this->setFlash('error', 'Soft introuvable.');
            $this->redirect('/admin/bar');
            return;
        }

        $this->view('admin/bar/soft-edit', [
            'title'     => 'Modifier un soft — Administration Le Commerce',
            'pageTitle' => 'Modifier « ' . $soft['name'] . ' »',
            'soft'    => $soft,
            'errors'  => [],
            'old'     => [],
        ], 'admin');
    }

    public function updateSoft(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $soft = BarSoft::find($id);
        if (!$soft) {
            $this->setFlash('error', 'Soft introuvable.');
            $this->redirect('/admin/bar');
            return;
        }

        [$errors, $data] = $this->validateSoft();

        if ($errors) {
            $this->view('admin/bar/soft-edit', [
                'title'     => 'Modifier un soft — Administration Le Commerce',
                'pageTitle' => 'Modifier « ' . $soft['name'] . ' »',
                'soft'    => $soft,
                'errors'  => $errors,
                'old'     => $data,
            ], 'admin');
            return;
        }

        BarSoft::update($id, ['name' => $data['name']]);

        $this->setFlash('success', 'Le soft "' . $data['name'] . '" a bien été mis à jour.');
        $this->redirect('/admin/bar');
    }

    public function destroySoft(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $soft = BarSoft::find($id);
        if (!$soft) {
            $this->setFlash('error', 'Soft introuvable.');
            $this->redirect('/admin/bar');
            return;
        }

        BarSoft::delete($id);

        $this->setFlash('success', 'Le soft "' . $soft['name'] . '" a été supprimé.');
        $this->redirect('/admin/bar');
    }

    /**
     * @return array{0: array<string,string>, 1: array<string,mixed>} [errors, data]
     */
    private function validateSoft(): array
    {
        $name = trim((string) $this->input('name', ''));

        $errors = [];
        if ($name === '' || mb_strlen($name) > 120) {
            $errors['name'] = 'Le nom est obligatoire (120 caractères maximum).';
        }

        return [$errors, ['name' => $name]];
    }

    /**
     * @return array{0: array<string,string>, 1: array<string,mixed>} [errors, data]
     */
    private function validatePlanche(): array
    {
        $name        = trim((string) $this->input('name', ''));
        $description = trim((string) $this->input('description', ''));
        $price       = trim((string) $this->input('price', ''));
        $publish     = (bool) $this->input('publish');

        $errors = [];
        if ($name === '' || mb_strlen($name) > 120) {
            $errors['name'] = 'Le nom est obligatoire (120 caractères maximum).';
        }
        if ($price === '' || !is_numeric(str_replace(',', '.', $price))) {
            $errors['price'] = 'Le prix est obligatoire et doit être un nombre.';
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

        return [$errors, [
            'name'        => $name,
            'description' => $description,
            'price'       => $price !== '' && is_numeric(str_replace(',', '.', $price))
                ? number_format((float) str_replace(',', '.', $price), 2, '.', '')
                : null,
            'publish'     => $publish,
        ]];
    }

    /**
     * Traite un éventuel nouveau fichier envoyé pour le champ "image" d'une
     * planche : si un fichier valide a été fourni, l'enregistre et supprime
     * l'ancien visuel ; sinon conserve $existingImage inchangé.
     */
    private function uploadPlancheImage(?string $existingImage): ?string
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

        $filename = 'bar_planche_' . bin2hex(random_bytes(6)) . '.' . self::ALLOWED_IMAGE_MIME[$mime];
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename)) {
            return $existingImage;
        }

        if ($existingImage) {
            $this->deletePlancheImageFile($existingImage);
        }

        return '/uploads/images/' . $filename;
    }

    private function deletePlancheImageFile(string $image): void
    {
        if (str_starts_with($image, '/uploads/images/')) {
            @unlink(dirname(__DIR__, 3) . '/public' . $image);
        }
    }

    /**
     * @return array{0: array<string,string>, 1: array<string,mixed>} [errors, data]
     */
    private function validateDrink(): array
    {
        $name     = trim((string) $this->input('name', ''));
        $category = (string) $this->input('category', '');
        $degree   = trim((string) $this->input('degree', ''));
        $price    = trim((string) $this->input('price', ''));

        $errors = [];
        if ($name === '' || mb_strlen($name) > 80) {
            $errors['name'] = 'Le nom est obligatoire (80 caractères maximum).';
        }
        if (!array_key_exists($category, Drink::CATEGORIES)) {
            $errors['category'] = 'Merci de choisir une catégorie valide.';
        }
        if ($degree !== '' && !is_numeric(str_replace(',', '.', $degree))) {
            $errors['degree'] = 'Le degré doit être un nombre.';
        }
        if ($price !== '' && !is_numeric(str_replace(',', '.', $price))) {
            $errors['price'] = 'Le prix doit être un nombre.';
        }

        return [$errors, [
            'name'     => $name,
            'category' => $category,
            'degree'   => $degree !== '' ? number_format((float) str_replace(',', '.', $degree), 1, '.', '') : null,
            'price'    => $price !== '' ? number_format((float) str_replace(',', '.', $price), 2, '.', '') : null,
        ]];
    }
}
