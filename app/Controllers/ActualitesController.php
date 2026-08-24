<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class ActualitesController extends Controller
{
    public function index(): void
    {
        $this->view('pages/placeholder', [
            'title'   => 'Actualités - Le Commerce',
            'description' => 'Toute l\'actualité et les événements du Commerce à Forges-les-Eaux.',
            'heading' => 'Actualités',
            'text'    => 'Les actualités et événements du Commerce arrivent très bientôt!',
        ]);
    }
}
