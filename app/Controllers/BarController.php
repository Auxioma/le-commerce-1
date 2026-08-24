<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\BarPlanche;
use App\Models\BarSoft;
use App\Models\Drink;

class BarController extends Controller
{
    public function index(): void
    {
        $drinks = Drink::featured(100);
        $categories = ['biere_blonde' => [], 'biere_brune' => [], 'biere_ambree' => [], 'autre' => []];
        foreach ($drinks as $drink) {
            $categories[$drink['category']][] = $drink;
        }

        $this->view('pages/bar', [
            'title'      => 'Le Bar — Le Commerce',
            'description' => 'Le Bar du Commerce à Forges-les-Eaux : bières pression, planches à partager et boissons fraîches dans une ambiance conviviale.',
            'heading'    => 'Le Bar',
            'categories' => $categories,
            'planches'   => BarPlanche::listActiveOrdered(),
            'softs'      => BarSoft::listAllOrdered(),
        ]);
    }
}
