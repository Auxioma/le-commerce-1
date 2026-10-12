<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Gallery;
use App\Models\GalleryCategory;
use App\Models\GalleryPhoto;

class GalleryController extends Controller
{
    /**
     * Page /galerie : filtres par catégorie, grille des galeries et vue de
     * chaque galerie — tout est rendu ici, la navigation entre ces vues se
     * fait sans rechargement (voir initGalleryPage() dans app.js).
     */
    public function index(): void
    {
        $this->view('pages/galerie', [
            'title'       => 'Le Commerce en images — Galerie photos',
            'heading'     => 'Le Commerce en images',
            'description' => 'L\'ambiance du Commerce à Forges-les-Eaux en photos : le bar, les soirées, les matchs.',
            'galleries'   => Gallery::listPublished(),
            'photosByGallery' => GalleryPhoto::listPublishedGroupedByGallery(),
            'categories'  => GalleryCategory::listWithPublishedPhotos(),
        ]);
    }
}
