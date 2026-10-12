<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\Gallery;
use App\Models\GalleryCategory;
use App\Models\GalleryPhoto;

/**
 * Gère la galerie « Le Commerce en images » (carrousel de l'accueil et page
 * publique /galerie), organisée en trois niveaux :
 *   catégories (`gallery_categories`, servent de filtres)
 *   -> galeries / albums (`galleries`)
 *   -> photos (`gallery_photos`).
 */
class AdminGalleryController extends Controller
{
    private const ALLOWED_IMAGE_MIME = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    private const MAX_IMAGE_SIZE = 5 * 1024 * 1024; // 5 Mo

    private const MAX_FILES_PER_UPLOAD = 20; // = max_file_uploads par défaut de PHP

    private const UPLOAD_PREFIX = '/uploads/gallery/';

    public function index(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/gallery/index', [
            'title'     => 'Galerie photos — Administration Le Commerce',
            'pageTitle' => 'Galerie photos',
            'galleries'   => Gallery::listAllOrdered(),
            'categories'  => GalleryCategory::listAllOrdered(),
            'photosCount' => GalleryPhoto::countByStatus('active'),
            'activeGalleriesCount' => Gallery::countByStatus('active'),
        ], 'admin');
    }

    // ------------------------------------------------------------------
    // Galeries (albums)
    // ------------------------------------------------------------------

    public function createAlbum(): void
    {
        Middleware::requireRole('admin');

        $categoryId = (int) $this->input('categorie', 0);
        $this->renderAlbumForm(null, [], [
            'event_date'  => date('Y-m-d'),
            'category_id' => $categoryId > 0 ? $categoryId : null,
            'publish'     => true,
        ]);
    }

    public function storeAlbum(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        [$errors, $data] = $this->validateAlbum();
        if ($errors) {
            $this->renderAlbumForm(null, $errors, $data);
            return;
        }

        $id = Gallery::create([
            'title'       => $data['title'],
            'slug'        => Gallery::generateUniqueSlug($data['title']),
            'description' => $data['description'] ?: null,
            'category_id' => $data['category_id'],
            'event_date'  => $data['event_date'],
            'status'      => $data['publish'] ? 'active' : 'inactif',
        ]);

        $this->setFlash('success', 'La galerie "' . $data['title'] . '" a été créée. Ajoutez-y maintenant vos photos.');
        $this->redirect('/admin/galerie/albums/' . $id);
    }

    /**
     * Page d'une galerie : ses photos + formulaire d'envoi multiple.
     */
    public function showAlbum(int $id): void
    {
        Middleware::requireRole('admin');

        $gallery = Gallery::findWithStats($id);
        if (!$gallery) {
            $this->setFlash('error', 'Galerie introuvable.');
            $this->redirect('/admin/galerie');
            return;
        }

        $this->view('admin/gallery/album', [
            'title'     => $gallery['title'] . ' — Administration Le Commerce',
            'pageTitle' => $gallery['title'],
            'gallery'   => $gallery,
            'photos'    => GalleryPhoto::listByGallery($id),
        ], 'admin');
    }

    public function editAlbum(int $id): void
    {
        Middleware::requireRole('admin');

        $gallery = $this->findOrRedirect(Gallery::class, $id, 'Galerie introuvable.');
        if ($gallery) {
            $this->renderAlbumForm($gallery, [], []);
        }
    }

    public function updateAlbum(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $gallery = $this->findOrRedirect(Gallery::class, $id, 'Galerie introuvable.');
        if (!$gallery) {
            return;
        }

        [$errors, $data] = $this->validateAlbum();
        if ($errors) {
            $this->renderAlbumForm($gallery, $errors, $data);
            return;
        }

        Gallery::update($id, [
            'title'       => $data['title'],
            'slug'        => Gallery::generateUniqueSlug($data['title'], $id),
            'description' => $data['description'] ?: null,
            'category_id' => $data['category_id'],
            'event_date'  => $data['event_date'],
            'status'      => $data['publish'] ? 'active' : 'inactif',
        ]);

        $this->setFlash('success', 'La galerie "' . $data['title'] . '" a bien été mise à jour.');
        $this->redirect('/admin/galerie/albums/' . $id);
    }

    public function toggleAlbum(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $gallery = $this->findOrRedirect(Gallery::class, $id, 'Galerie introuvable.');
        if (!$gallery) {
            return;
        }

        Gallery::update($id, ['status' => $gallery['status'] === 'active' ? 'inactif' : 'active']);

        $this->setFlash('success', 'Statut de la galerie mis à jour.');
        $this->redirectBack('/admin/galerie');
    }

    public function destroyAlbum(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $gallery = $this->findOrRedirect(Gallery::class, $id, 'Galerie introuvable.');
        if (!$gallery) {
            return;
        }

        // Les lignes photos partent avec la galerie (ON DELETE CASCADE) : on
        // supprime d'abord les fichiers envoyés depuis l'admin.
        foreach (GalleryPhoto::listByGallery($id) as $photo) {
            $this->deleteImageFile($photo['image']);
        }
        Gallery::delete($id);

        $this->setFlash('success', 'La galerie "' . $gallery['title'] . '" et ses photos ont été supprimées.');
        $this->redirect('/admin/galerie');
    }

    /**
     * Envoi de plusieurs photos d'un coup dans une galerie : légende et date
     * reprises de la galerie (modifiables ensuite photo par photo).
     */
    public function uploadAlbumPhotos(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $gallery = $this->findOrRedirect(Gallery::class, $id, 'Galerie introuvable.');
        if (!$gallery) {
            return;
        }

        $files = $this->normalizeFiles($_FILES['images'] ?? null);
        if (!$files) {
            $this->setFlash('error', 'Sélectionnez au moins une photo à envoyer.');
            $this->redirect('/admin/galerie/albums/' . $id);
            return;
        }
        if (count($files) > self::MAX_FILES_PER_UPLOAD) {
            $this->setFlash('error', 'Maximum ' . self::MAX_FILES_PER_UPLOAD . ' photos par envoi.');
            $this->redirect('/admin/galerie/albums/' . $id);
            return;
        }

        $legend = trim((string) $this->input('title', '')) ?: $gallery['title'];
        $added = 0;
        $rejected = [];
        foreach ($files as $file) {
            $error = $this->fileError($file);
            $path = $error === null ? $this->storeFile($file) : null;
            if ($path === null) {
                $rejected[] = $file['name'] . ($error ? ' (' . $error . ')' : '');
                continue;
            }
            GalleryPhoto::create([
                'gallery_id' => $id,
                'title'      => mb_substr($legend, 0, 120),
                'image'      => $path,
                'taken_at'   => $gallery['event_date'],
                'status'     => 'active',
            ]);
            $added++;
        }

        if ($added > 0) {
            $this->setFlash('success', $added . ' photo(s) ajoutée(s) à la galerie.'
                . ($rejected ? ' Ignorée(s) : ' . implode(', ', $rejected) . '.' : ''));
        } else {
            $this->setFlash('error', 'Aucune photo ajoutée. ' . implode(', ', $rejected));
        }
        $this->redirect('/admin/galerie/albums/' . $id);
    }

    // ------------------------------------------------------------------
    // Photos
    // ------------------------------------------------------------------

    public function createPhoto(): void
    {
        Middleware::requireRole('admin');

        $galleryId = (int) $this->input('galerie', 0);
        $this->renderPhotoForm(null, [], [
            'gallery_id' => $galleryId > 0 ? $galleryId : null,
            'taken_at'   => date('Y-m-d'),
            'publish'    => true,
        ]);
    }

    public function storePhoto(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        [$errors, $data] = $this->validatePhoto(true);
        if ($errors) {
            $this->renderPhotoForm(null, $errors, $data);
            return;
        }

        $image = $this->storeFile($_FILES['image']);
        if ($image === null) {
            $this->renderPhotoForm(null, ['image' => "Échec de l'enregistrement de l'image."], $data);
            return;
        }

        GalleryPhoto::create([
            'gallery_id' => $data['gallery_id'],
            'title'      => $data['title'],
            'image'      => $image,
            'taken_at'   => $data['taken_at'],
            'status'     => $data['publish'] ? 'active' : 'inactif',
        ]);

        $this->setFlash('success', 'La photo "' . $data['title'] . '" a bien été ajoutée.');
        $this->redirect('/admin/galerie/albums/' . $data['gallery_id']);
    }

    public function editPhoto(int $id): void
    {
        Middleware::requireRole('admin');

        $photo = $this->findOrRedirect(GalleryPhoto::class, $id, 'Photo introuvable.');
        if ($photo) {
            $this->renderPhotoForm($photo, [], []);
        }
    }

    public function updatePhoto(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $photo = $this->findOrRedirect(GalleryPhoto::class, $id, 'Photo introuvable.');
        if (!$photo) {
            return;
        }

        [$errors, $data] = $this->validatePhoto(false);
        if ($errors) {
            $this->renderPhotoForm($photo, $errors, $data);
            return;
        }

        $image = $photo['image'];
        $file = $_FILES['image'] ?? null;
        if ($file && $file['error'] === UPLOAD_ERR_OK && ($newImage = $this->storeFile($file)) !== null) {
            $this->deleteImageFile($photo['image']);
            $image = $newImage;
        }

        GalleryPhoto::update($id, [
            'gallery_id' => $data['gallery_id'],
            'title'      => $data['title'],
            'image'      => $image,
            'taken_at'   => $data['taken_at'],
            'status'     => $data['publish'] ? 'active' : 'inactif',
        ]);

        $this->setFlash('success', 'La photo "' . $data['title'] . '" a bien été mise à jour.');
        $this->redirect('/admin/galerie/albums/' . $data['gallery_id']);
    }

    public function togglePhoto(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $photo = $this->findOrRedirect(GalleryPhoto::class, $id, 'Photo introuvable.');
        if (!$photo) {
            return;
        }

        GalleryPhoto::update($id, ['status' => $photo['status'] === 'active' ? 'inactif' : 'active']);

        $this->setFlash('success', 'Statut de la photo mis à jour.');
        $this->redirect('/admin/galerie/albums/' . $photo['gallery_id']);
    }

    public function destroyPhoto(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $photo = $this->findOrRedirect(GalleryPhoto::class, $id, 'Photo introuvable.');
        if (!$photo) {
            return;
        }

        $this->deleteImageFile($photo['image']);
        GalleryPhoto::delete($id);

        $this->setFlash('success', 'La photo "' . $photo['title'] . '" a été supprimée.');
        $this->redirect('/admin/galerie/albums/' . $photo['gallery_id']);
    }

    // ------------------------------------------------------------------
    // Catégories (filtres)
    // ------------------------------------------------------------------

    public function createCategory(): void
    {
        Middleware::requireRole('admin');

        $this->renderCategoryForm(null, [], [
            'display_order' => GalleryCategory::nextDisplayOrder(),
            'active'        => true,
        ]);
    }

    public function storeCategory(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        [$errors, $data] = $this->validateCategory();
        if ($errors) {
            $this->renderCategoryForm(null, $errors, $data);
            return;
        }

        GalleryCategory::create([
            'name'          => $data['name'],
            'slug'          => GalleryCategory::generateUniqueSlug($data['name']),
            'display_order' => $data['display_order'],
            'status'        => $data['active'] ? 'active' : 'inactif',
        ]);

        $this->setFlash('success', 'La catégorie "' . $data['name'] . '" a bien été créée.');
        $this->redirect('/admin/galerie');
    }

    public function editCategory(int $id): void
    {
        Middleware::requireRole('admin');

        $category = $this->findOrRedirect(GalleryCategory::class, $id, 'Catégorie introuvable.');
        if ($category) {
            $this->renderCategoryForm($category, [], []);
        }
    }

    public function updateCategory(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $category = $this->findOrRedirect(GalleryCategory::class, $id, 'Catégorie introuvable.');
        if (!$category) {
            return;
        }

        [$errors, $data] = $this->validateCategory();
        if ($errors) {
            $this->renderCategoryForm($category, $errors, $data);
            return;
        }

        GalleryCategory::update($id, [
            'name'          => $data['name'],
            'slug'          => GalleryCategory::generateUniqueSlug($data['name'], $id),
            'display_order' => $data['display_order'],
            'status'        => $data['active'] ? 'active' : 'inactif',
        ]);

        $this->setFlash('success', 'La catégorie "' . $data['name'] . '" a bien été mise à jour.');
        $this->redirect('/admin/galerie');
    }

    public function toggleCategory(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $category = $this->findOrRedirect(GalleryCategory::class, $id, 'Catégorie introuvable.');
        if (!$category) {
            return;
        }

        GalleryCategory::update($id, ['status' => $category['status'] === 'active' ? 'inactif' : 'active']);

        $this->setFlash('success', 'Statut de la catégorie mis à jour.');
        $this->redirect('/admin/galerie');
    }

    public function destroyCategory(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $category = $this->findOrRedirect(GalleryCategory::class, $id, 'Catégorie introuvable.');
        if (!$category) {
            return;
        }

        // Les galeries de cette catégorie sont conservées (clé étrangère ON DELETE SET NULL).
        GalleryCategory::delete($id);

        $this->setFlash('success', 'La catégorie "' . $category['name'] . '" a été supprimée. Ses galeries restent visibles dans « Tous ».');
        $this->redirect('/admin/galerie');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function renderAlbumForm(?array $gallery, array $errors, array $old): void
    {
        $label = $gallery ? 'Modifier « ' . $gallery['title'] . ' »' : 'Nouvelle galerie';

        $this->view('admin/gallery/album-form', [
            'title'      => $label . ' — Administration Le Commerce',
            'pageTitle'  => $label,
            'gallery'    => $gallery,
            'categories' => GalleryCategory::listAllOrdered(),
            'errors'     => $errors,
            'old'        => $old,
        ], 'admin');
    }

    private function renderPhotoForm(?array $photo, array $errors, array $old): void
    {
        $label = $photo ? 'Modifier « ' . $photo['title'] . ' »' : 'Ajouter une photo';

        $this->view('admin/gallery/photo-form', [
            'title'     => $label . ' — Administration Le Commerce',
            'pageTitle' => $label,
            'photo'     => $photo,
            'galleries' => Gallery::listForSelect(),
            'errors'    => $errors,
            'old'       => $old,
        ], 'admin');
    }

    private function renderCategoryForm(?array $category, array $errors, array $old): void
    {
        $label = $category ? 'Modifier « ' . $category['name'] . ' »' : 'Nouvelle catégorie';

        $this->view('admin/gallery/category-form', [
            'title'     => $label . ' — Administration Le Commerce',
            'pageTitle' => $label,
            'category'  => $category,
            'errors'    => $errors,
            'old'       => $old,
        ], 'admin');
    }

    /**
     * @param class-string<\App\Core\Model> $model
     */
    private function findOrRedirect(string $model, int $id, string $notFound): ?array
    {
        $row = $model::find($id);
        if (!$row) {
            $this->setFlash('error', $notFound);
            $this->redirect('/admin/galerie');
        }
        return $row;
    }

    /** Revient à la page admin d'où vient la requête (liste ou page de galerie). */
    private function redirectBack(string $fallback): void
    {
        $referer = parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_PATH) ?: '';
        $referer = BASE_PATH !== '' && str_starts_with($referer, BASE_PATH) ? substr($referer, strlen(BASE_PATH)) : $referer;
        $this->redirect(str_starts_with($referer, '/admin/galerie') ? $referer : $fallback);
    }

    private function isValidDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }

    /**
     * @return array{0: array<string,string>, 1: array<string,mixed>} [errors, data]
     */
    private function validateAlbum(): array
    {
        $title       = trim((string) $this->input('title', ''));
        $description = trim((string) $this->input('description', ''));
        $eventDate   = trim((string) $this->input('event_date', ''));
        $categoryId  = (int) $this->input('category_id', 0);
        $publish     = (bool) $this->input('publish');

        $errors = [];
        if ($title === '' || mb_strlen($title) > 120) {
            $errors['title'] = 'Le titre est obligatoire (120 caractères maximum).';
        }
        if (mb_strlen($description) > 300) {
            $errors['description'] = 'La description ne peut pas dépasser 300 caractères.';
        }
        if (!$this->isValidDate($eventDate)) {
            $errors['event_date'] = 'La date est invalide.';
        }
        if ($categoryId > 0 && !GalleryCategory::find($categoryId)) {
            $errors['category_id'] = 'Catégorie introuvable.';
        }

        return [$errors, [
            'title'       => $title,
            'description' => $description,
            'event_date'  => $eventDate,
            'category_id' => $categoryId > 0 ? $categoryId : null,
            'publish'     => $publish,
        ]];
    }

    /**
     * @return array{0: array<string,string>, 1: array<string,mixed>} [errors, data]
     */
    private function validatePhoto(bool $imageRequired): array
    {
        $title     = trim((string) $this->input('title', ''));
        $takenAt   = trim((string) $this->input('taken_at', ''));
        $galleryId = (int) $this->input('gallery_id', 0);
        $publish   = (bool) $this->input('publish');

        $errors = [];
        if ($title === '' || mb_strlen($title) > 120) {
            $errors['title'] = 'La légende est obligatoire (120 caractères maximum).';
        }
        if (!$this->isValidDate($takenAt)) {
            $errors['taken_at'] = 'La date est invalide.';
        }
        if ($galleryId <= 0 || !Gallery::find($galleryId)) {
            $errors['gallery_id'] = 'Choisissez la galerie de la photo.';
        }

        $file = $_FILES['image'] ?? null;
        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            if ($imageRequired) {
                $errors['image'] = 'Choisissez une photo à envoyer.';
            }
        } elseif (($fileError = $this->fileError($file)) !== null) {
            $errors['image'] = $fileError;
        }

        return [$errors, [
            'title'      => $title,
            'taken_at'   => $takenAt,
            'gallery_id' => $galleryId > 0 ? $galleryId : null,
            'publish'    => $publish,
        ]];
    }

    /**
     * @return array{0: array<string,string>, 1: array<string,mixed>} [errors, data]
     */
    private function validateCategory(): array
    {
        $name         = trim((string) $this->input('name', ''));
        $displayOrder = max(0, (int) $this->input('display_order', 0));
        $active       = (bool) $this->input('active');

        $errors = [];
        if ($name === '' || mb_strlen($name) > 60) {
            $errors['name'] = 'Le nom est obligatoire (60 caractères maximum).';
        }

        return [$errors, ['name' => $name, 'display_order' => $displayOrder, 'active' => $active]];
    }

    /**
     * Transforme $_FILES['images'] (champ multiple) en liste de fichiers,
     * en ignorant les emplacements vides.
     *
     * @return list<array{name:string,type:string,tmp_name:string,error:int,size:int}>
     */
    private function normalizeFiles(?array $input): array
    {
        if (!$input || !is_array($input['name'] ?? null)) {
            return [];
        }
        $files = [];
        foreach ($input['name'] as $i => $name) {
            if ((int) $input['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $files[] = [
                'name'     => (string) $name,
                'type'     => (string) $input['type'][$i],
                'tmp_name' => (string) $input['tmp_name'][$i],
                'error'    => (int) $input['error'][$i],
                'size'     => (int) $input['size'][$i],
            ];
        }
        return $files;
    }

    /** Message d'erreur pour un fichier envoyé, ou null s'il est valide. */
    private function fileError(array $file): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return "échec de l'envoi";
        }
        if ($file['size'] > self::MAX_IMAGE_SIZE) {
            return 'plus de 5 Mo';
        }
        if (!isset(self::ALLOWED_IMAGE_MIME[mime_content_type($file['tmp_name'])])) {
            return 'format non supporté (JPEG, PNG ou WEBP)';
        }
        return null;
    }

    /** Enregistre un fichier validé dans /uploads/gallery et renvoie son chemin public. */
    private function storeFile(array $file): ?string
    {
        if ($this->fileError($file) !== null) {
            return null;
        }

        $uploadDir = dirname(__DIR__, 3) . '/public' . rtrim(self::UPLOAD_PREFIX, '/');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $extension = self::ALLOWED_IMAGE_MIME[mime_content_type($file['tmp_name'])];
        $filename = 'galerie_' . bin2hex(random_bytes(6)) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename)) {
            return null;
        }

        return self::UPLOAD_PREFIX . $filename;
    }

    /**
     * Ne supprime que les fichiers envoyés depuis l'admin : les photos
     * d'exemple livrées dans /assets/images/gallery/ ne sont jamais effacées.
     */
    private function deleteImageFile(?string $image): void
    {
        if ($image && str_starts_with($image, self::UPLOAD_PREFIX)) {
            @unlink(dirname(__DIR__, 3) . '/public' . $image);
        }
    }
}
