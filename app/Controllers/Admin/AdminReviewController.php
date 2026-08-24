<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\GoogleReview;

class AdminReviewController extends Controller
{
    public function index(): void
    {
        Middleware::requireRole('admin');

        $reviews = GoogleReview::latest(50);
        $average = $this->sharedData['shop']['google_rating'];
        $total   = count($reviews) ?: $this->sharedData['shop']['google_reviews_count'];

        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($reviews as $r) {
            $distribution[(int) $r['rating']] = ($distribution[(int) $r['rating']] ?? 0) + 1;
        }

        $this->view('admin/reviews/index', [
            'title'     => 'Avis Google — Administration Le Commerce',
            'pageTitle' => 'Avis Google',
            'reviews'      => $reviews,
            'average'      => $average,
            'totalReviews' => $this->sharedData['shop']['google_reviews_count'],
            'distribution' => $distribution,
            'countLoaded'  => count($reviews),
        ], 'admin');
    }

    public function store(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $author  = trim((string) $this->input('author_name', ''));
        $rating  = (int) $this->input('rating', 5);
        $comment = trim((string) $this->input('comment', ''));

        if ($author === '' || $rating < 1 || $rating > 5) {
            $this->setFlash('error', 'Merci de renseigner un auteur et une note valide (1 à 5).');
            $this->redirect('/admin/avis-google');
            return;
        }

        GoogleReview::create([
            'author_name'  => $author,
            'rating'       => $rating,
            'comment'      => $comment ?: null,
            'published_at' => date('Y-m-d H:i:s'),
        ]);

        $this->setFlash('success', 'Avis ajouté.');
        $this->redirect('/admin/avis-google');
    }

    public function update(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $review = GoogleReview::find($id);
        if (!$review) {
            $this->setFlash('error', 'Avis introuvable.');
            $this->redirect('/admin/avis-google');
            return;
        }

        $author  = trim((string) $this->input('author_name', ''));
        $rating  = (int) $this->input('rating', 5);
        $comment = trim((string) $this->input('comment', ''));

        if ($author === '' || $rating < 1 || $rating > 5) {
            $this->setFlash('error', 'Merci de renseigner un auteur et une note valide (1 à 5).');
            $this->redirect('/admin/avis-google');
            return;
        }

        GoogleReview::update($id, [
            'author_name' => $author,
            'rating'      => $rating,
            'comment'     => $comment ?: null,
        ]);

        $this->setFlash('success', 'Avis mis à jour.');
        $this->redirect('/admin/avis-google');
    }

    public function destroy(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        GoogleReview::delete($id);

        $this->setFlash('success', 'Avis supprimé.');
        $this->redirect('/admin/avis-google');
    }
}
