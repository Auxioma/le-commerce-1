<?php

declare(strict_types=1);

namespace App\Controllers\Client;

use App\Core\Controller;
use App\Core\Middleware;

class ClientPlaceholderController extends Controller
{
    private const SECTIONS = [
        'informations'  => 'Mes informations',
        'notifications' => 'Notifications',
        'aide'          => 'Aide & support',
    ];

    public function show(string $section): void
    {
        Middleware::requireRole('client');

        if (!isset(self::SECTIONS[$section])) {
            http_response_code(404);
            echo \App\Core\View::render('errors/404');
            return;
        }

        $this->view('client/placeholder', [
            'title'   => self::SECTIONS[$section] . ' — Le Commerce',
            'heading' => self::SECTIONS[$section],
            'user'    => Middleware::user(),
        ], 'client');
    }
}
