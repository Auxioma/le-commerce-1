<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\FdjCategory;
use App\Models\FdjService;

class FdjController extends Controller
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
        }, FdjCategory::listActiveOrdered());

        $services = array_map(static fn(array $service): string => $service['name'], FdjService::listAllOrdered());

        $this->view('pages/fdj', [
            'title'   => 'FDJ — Le Commerce',
            'description' => 'Point de vente FDJ à Forges-les-Eaux : Loto, Euromillions, Illiko, Amigo, Keno et Rapido. Vérification et retrait de vos gains en caisse.',
            'heading' => 'FDJ',
            'categories' => $categories,
            'services'   => $services,
        ]);
    }
}
