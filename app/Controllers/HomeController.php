<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\GooglePlacesClient;
use App\Models\Drink;
use App\Models\Deal;
use App\Models\GoogleReview;

class HomeController extends Controller
{
    public function index(): void
    {
        $places = GooglePlacesClient::fromSettings();
        $summary = $places ? $places->summary() : null;

        $this->view('home/index', [
            'title'       => 'Le Commerce — Bar, Tabac, PMU, FDJ, Presse à Forges-les-Eaux',
            'description' => 'Le Commerce, votre bar-tabac-presse à Forges-les-Eaux (76440) : bar convivial, tabac, PMU, FDJ, presse et services du quotidien. Ouvert 7j/7.',
            'drinks'  => Drink::featured(10),
            'deal'    => Deal::current(),
            'reviews' => GoogleReview::latest(3),
            'googleRating'        => $summary['rating'] ?? $this->sharedData['shop']['google_rating'],
            'googleReviewsCount'  => $summary['total'] ?? $this->sharedData['shop']['google_reviews_count'],
            'googleReviewUrl'     => GooglePlacesClient::writeReviewUrl() ?? 'https://www.google.com/maps',
        ]);
    }
}
