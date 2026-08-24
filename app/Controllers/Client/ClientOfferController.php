<?php

declare(strict_types=1);

namespace App\Controllers\Client;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\OfferRedemption;

class ClientOfferController extends Controller
{
    public function index(): void
    {
        Middleware::requireRole('client');

        $user = Middleware::user();

        $this->view('client/offers', [
            'title'  => 'Mes offres — Le Commerce',
            'user'   => $user,
            'offers' => OfferRedemption::forUser($user['id']),
        ], 'client');
    }
}
