<?php

declare(strict_types=1);

namespace App\Controllers\Client;

use App\Core\Controller;
use App\Core\Middleware;

class ClientPlaceholderController extends Controller
{
    /**
     * Sections « prochainement » de l'espace client. Toutes les sections du
     * menu ont désormais leur propre écran : cette liste est vide et toute
     * autre URL /mon-compte/... renvoie un 404.
     */
    private const SECTIONS = [];

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
