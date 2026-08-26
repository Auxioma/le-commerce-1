<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\GooglePlacesClient;
use App\Core\Middleware;

class AdminReviewController extends Controller
{
    public function index(): void
    {
        Middleware::requireRole('admin');

        $places = GooglePlacesClient::fromSettings();
        $summary = $places ? $places->summary() : null;

        $this->view('admin/reviews/index', [
            'title'     => 'Avis Google — Administration Le Commerce',
            'pageTitle' => 'Avis Google',
            'average'          => $summary['rating'] ?? $this->sharedData['shop']['google_rating'],
            'totalReviews'     => $summary['total'] ?? $this->sharedData['shop']['google_reviews_count'],
            'googleReviews'    => $places ? $places->reviews() : [],
            'googleConfigured' => $places !== null,
            'googleMapsUrl'    => ($places ? $places->mapsUri() : null) ?? GooglePlacesClient::writeReviewUrl() ?? 'https://www.google.com/maps',
        ], 'admin');
    }
}
