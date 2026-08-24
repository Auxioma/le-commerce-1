<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\PmuCategory;
use App\Models\PmuService;

class PmuController extends Controller
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
        }, PmuCategory::listActiveOrdered());

        $services = array_map(static fn(array $service): string => $service['name'], PmuService::listAllOrdered());

        $this->view('pages/pmu', [
            'title'   => 'PMU — Le Commerce',
            'description' => 'Point PMU à Forges-les-Eaux : paris hippiques simple gagnant/placé, couplé, trio, Quinté+ et Multi, retransmission des courses en boutique.',
            'heading' => 'PMU',
            'categories' => $categories,
            'services'   => $services,
        ]);
    }
}
