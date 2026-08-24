<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Service;

class ServicesController extends Controller
{
    public function index(): void
    {
        $categories = array_map(static function (array $service): array {
            return [
                'name' => $service['name'],
                'desc' => $service['description'] ?? '',
                'icon' => $service['icon'],
                'slug' => $service['slug'],
            ];
        }, Service::listActiveOrdered());

        $this->view('pages/services', [
            'title'      => 'Nos Services — Le Commerce',
            'description' => 'Découvrez les services du quotidien proposés par Le Commerce à Forges-les-Eaux : timbres, recharges, colis et bien plus.',
            'heading'    => 'Nos Services du Quotidien',
            'categories' => $categories,
        ]);
    }
}
