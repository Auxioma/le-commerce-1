<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\TabacCategory;
use App\Models\TabacService;

class TabacController extends Controller
{
    public function index(): void
    {
        $categories = array_map(static function (array $category): array {
            return [
                'name'  => $category['name'],
                'desc'  => $category['description'] ?? '',
                'icon'  => $category['icon'],
                'image' => $category['image'],
            ];
        }, TabacCategory::listActiveOrdered());

        $services = array_map(static fn(array $service): string => $service['name'], TabacService::listAllOrdered());

        $this->view('pages/tabac', [
            'title'   => 'Tabac — Le Commerce',
            'description' => 'Bureau de tabac à Forges-les-Eaux : cigarettes, tabac à rouler, cigares, e-cigarettes, timbres fiscaux et cartes prépayées.',
            'heading' => 'Tabac',
            'categories' => $categories,
            'services'   => $services,
        ]);
    }
}
