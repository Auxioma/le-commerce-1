<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\NewsArticle;

class ActualitesController extends Controller
{
    public function index(): void
    {
        $this->view('pages/actualites', [
            'title'       => 'Actualités — Le Commerce',
            'description' => 'Toute l\'actualité et les événements du Commerce à Forges-les-Eaux.',
            'articles'    => NewsArticle::listPublished(),
        ]);
    }

    public function show(string $slug): void
    {
        $article = NewsArticle::findPublishedBySlug($slug);

        if (!$article) {
            http_response_code(404);
            $this->view('pages/actualite-show', [
                'title'   => 'Actualité introuvable — Le Commerce',
                'article' => null,
            ]);
            return;
        }

        $this->view('pages/actualite-show', [
            'title'       => $article['title'] . ' — Le Commerce',
            'description' => $article['excerpt'] ?: mb_substr(strip_tags($article['content']), 0, 160),
            'article'     => $article,
        ]);
    }
}
