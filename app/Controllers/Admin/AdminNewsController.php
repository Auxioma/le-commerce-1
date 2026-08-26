<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\NewsArticle;

/**
 * Gère les actualités affichées sur la page publique /actualites : liste
 * de billets avec page dédiée par article (slug) — CRUD complet sur la
 * table `news_articles`.
 */
class AdminNewsController extends Controller
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

        $this->view('admin/news/index', [
            'title'     => 'Actualités — Administration Le Commerce',
            'pageTitle' => 'Actualités',
            'articles'      => NewsArticle::listAllOrdered(),
            'activeCount'   => NewsArticle::countByStatus('active'),
            'inactiveCount' => NewsArticle::countByStatus('inactif'),
            'newThisMonth'  => NewsArticle::countCreatedThisMonth(),
        ], 'admin');
    }

    public function create(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/news/create', [
            'title'     => 'Ajouter une actualité — Administration Le Commerce',
            'pageTitle' => 'Ajouter une actualité',
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
            $this->view('admin/news/create', [
                'title'     => 'Ajouter une actualité — Administration Le Commerce',
                'pageTitle' => 'Ajouter une actualité',
                'errors' => $errors,
                'old'    => $data,
            ], 'admin');
            return;
        }

        NewsArticle::create([
            'slug'         => NewsArticle::generateUniqueSlug($data['title']),
            'title'        => $data['title'],
            'excerpt'      => $data['excerpt'] ?: null,
            'content'      => $data['content'],
            'image'        => $this->uploadImage(null),
            'status'       => $data['publish'] ? 'active' : 'inactif',
            'published_at' => date('Y-m-d H:i:s'),
        ]);

        $this->setFlash('success', 'L\'actualité "' . $data['title'] . '" a bien été créée.');
        $this->redirect('/admin/actualites');
    }

    public function edit(int $id): void
    {
        Middleware::requireRole('admin');

        $article = NewsArticle::find($id);
        if (!$article) {
            $this->setFlash('error', 'Actualité introuvable.');
            $this->redirect('/admin/actualites');
            return;
        }

        $this->view('admin/news/edit', [
            'title'     => 'Modifier une actualité — Administration Le Commerce',
            'pageTitle' => 'Modifier « ' . $article['title'] . ' »',
            'article' => $article,
            'errors'  => [],
            'old'     => [],
        ], 'admin');
    }

    public function update(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $article = NewsArticle::find($id);
        if (!$article) {
            $this->setFlash('error', 'Actualité introuvable.');
            $this->redirect('/admin/actualites');
            return;
        }

        [$errors, $data] = $this->validate();

        if ($errors) {
            $this->view('admin/news/edit', [
                'title'     => 'Modifier une actualité — Administration Le Commerce',
                'pageTitle' => 'Modifier « ' . $article['title'] . ' »',
                'article' => $article,
                'errors'  => $errors,
                'old'     => $data,
            ], 'admin');
            return;
        }

        NewsArticle::update($id, [
            'title'   => $data['title'],
            'excerpt' => $data['excerpt'] ?: null,
            'content' => $data['content'],
            'image'   => $this->uploadImage($article['image']),
            'status'  => $data['publish'] ? 'active' : 'inactif',
        ]);

        $this->setFlash('success', 'L\'actualité "' . $data['title'] . '" a bien été mise à jour.');
        $this->redirect('/admin/actualites');
    }

    public function toggleStatus(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $article = NewsArticle::find($id);
        if (!$article) {
            $this->setFlash('error', 'Actualité introuvable.');
            $this->redirect('/admin/actualites');
            return;
        }

        $newStatus = $article['status'] === 'active' ? 'inactif' : 'active';
        NewsArticle::update($id, ['status' => $newStatus]);

        $this->setFlash('success', 'Statut de l\'actualité mis à jour.');
        $this->redirect('/admin/actualites');
    }

    public function destroy(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $article = NewsArticle::find($id);
        if (!$article) {
            $this->setFlash('error', 'Actualité introuvable.');
            $this->redirect('/admin/actualites');
            return;
        }

        if ($article['image']) {
            $this->deleteImageFile($article['image']);
        }

        NewsArticle::delete($id);

        $this->setFlash('success', 'L\'actualité "' . $article['title'] . '" a été supprimée.');
        $this->redirect('/admin/actualites');
    }

    /**
     * @return array{0: array<string,string>, 1: array<string,mixed>} [errors, data]
     */
    private function validate(): array
    {
        $title   = trim((string) $this->input('title', ''));
        $excerpt = trim((string) $this->input('excerpt', ''));
        $content = trim((string) $this->input('content', ''));
        $publish = (bool) $this->input('publish');

        $errors = [];
        if ($title === '' || mb_strlen($title) > 160) {
            $errors['title'] = 'Le titre est obligatoire (160 caractères maximum).';
        }
        if ($excerpt !== '' && mb_strlen($excerpt) > 300) {
            $errors['excerpt'] = 'Le résumé ne peut pas dépasser 300 caractères.';
        }
        if ($content === '') {
            $errors['content'] = 'Le contenu de l\'article est obligatoire.';
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

        return [$errors, compact('title', 'excerpt', 'content', 'publish')];
    }

    /**
     * Traite un éventuel nouveau fichier envoyé pour le champ "image" d'une
     * actualité : si un fichier valide a été fourni, l'enregistre et
     * supprime l'ancien visuel ; sinon conserve $existingImage inchangé.
     */
    private function uploadImage(?string $existingImage): ?string
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

        $filename = 'actualite_' . bin2hex(random_bytes(6)) . '.' . self::ALLOWED_IMAGE_MIME[$mime];
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename)) {
            return $existingImage;
        }

        if ($existingImage) {
            $this->deleteImageFile($existingImage);
        }

        return '/uploads/images/' . $filename;
    }

    private function deleteImageFile(string $image): void
    {
        if (str_starts_with($image, '/uploads/images/')) {
            @unlink(dirname(__DIR__, 3) . '/public' . $image);
        }
    }
}
