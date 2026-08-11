<?php

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
            'heading'    => 'Nos Services du Quotidien',
            'categories' => $categories,
        ]);
    }
}
