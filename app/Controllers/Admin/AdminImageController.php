<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\Settings;

class AdminImageController extends Controller
{
    private const ALLOWED_MIME = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    private const MAX_SIZE = 5 * 1024 * 1024; // 5 Mo

    public function index(): void
    {
        Middleware::requireRole('admin');

        $catalog = require dirname(__DIR__, 3) . '/config/image_slots.php';
        $values  = [];
        foreach ($catalog as $items) {
            foreach (array_keys($items) as $slug) {
                $values[$slug] = Settings::get('img_' . $slug);
            }
        }

        $this->view('admin/images/index', [
            'title'     => 'Photos du site — Administration Le Commerce',
            'pageTitle' => 'Photos du site',
            'catalog'   => $catalog,
            'values'    => $values,
        ], 'admin');
    }

    public function store(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $slug = (string) $this->input('slug', '');
        if (!$this->slugExists($slug)) {
            $this->setFlash('error', "Emplacement d'image inconnu.");
            $this->redirect('/admin/images');
            return;
        }

        $file = $_FILES['image'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', "Merci de sélectionner un fichier image valide.");
            $this->redirect('/admin/images');
            return;
        }

        if ($file['size'] > self::MAX_SIZE) {
            $this->setFlash('error', 'Le fichier dépasse la taille maximale autorisée (5 Mo).');
            $this->redirect('/admin/images');
            return;
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!isset(self::ALLOWED_MIME[$mime])) {
            $this->setFlash('error', 'Format non supporté. Utilisez une image JPEG, PNG ou WEBP.');
            $this->redirect('/admin/images');
            return;
        }

        $publicDir = dirname(__DIR__, 3) . '/public';
        $uploadDir = $publicDir . '/uploads/images';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $filename = $slug . '_' . time() . '.' . self::ALLOWED_MIME[$mime];
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename)) {
            $this->setFlash('error', "Échec de l'enregistrement du fichier.");
            $this->redirect('/admin/images');
            return;
        }

        $oldPath = Settings::get('img_' . $slug);
        if ($oldPath && str_starts_with($oldPath, '/uploads/images/')) {
            @unlink($publicDir . $oldPath);
        }

        Settings::updateMany(['img_' . $slug => '/uploads/images/' . $filename]);

        $this->setFlash('success', 'Photo mise à jour avec succès.');
        $this->redirect('/admin/images');
    }

    public function destroy(string $slug): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        if (!$this->slugExists($slug)) {
            $this->setFlash('error', "Emplacement d'image inconnu.");
            $this->redirect('/admin/images');
            return;
        }

        $oldPath = Settings::get('img_' . $slug);
        if ($oldPath && str_starts_with($oldPath, '/uploads/images/')) {
            @unlink(dirname(__DIR__, 3) . '/public' . $oldPath);
        }

        Settings::updateMany(['img_' . $slug => '']);

        $this->setFlash('success', 'Photo retirée, le visuel par défaut est de nouveau utilisé.');
        $this->redirect('/admin/images');
    }

    private function slugExists(string $slug): bool
    {
        $catalog = require dirname(__DIR__, 3) . '/config/image_slots.php';
        foreach ($catalog as $items) {
            if (array_key_exists($slug, $items)) {
                return true;
            }
        }
        return false;
    }
}
