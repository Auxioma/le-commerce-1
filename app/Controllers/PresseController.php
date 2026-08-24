<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class PresseController extends Controller
{
    public function index(): void
    {
        $this->view('pages/presse', [
            'title'   => 'Presse — Le Commerce',
            'description' => 'Presse et magazines à Forges-les-Eaux : quotidiens nationaux et régionaux, magazines spécialisés et presse jeunesse, réservation possible.',
            'heading' => 'Presse',
            'categories' => [
                [
                    'name' => 'Quotidiens nationaux',
                    'desc' => 'La presse d\'information générale et sportive, livrée chaque matin.',
                    'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l7-3 7 3z',
                    'slug' => 'presse_quotidiens_nationaux',
                ],
                [
                    'name' => 'Presse régionale',
                    'desc' => 'L\'actualité locale et régionale, pour ne rien manquer près de chez vous.',
                    'icon' => 'M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z',
                    'slug' => 'presse_regionale',
                ],
                [
                    'name' => 'Magazines & spécialisée',
                    'desc' => 'Décoration, cuisine, automobile, informatique, mode... une large sélection de titres.',
                    'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s4.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                    'slug' => 'presse_magazines',
                ],
                [
                    'name' => 'Presse jeunesse',
                    'desc' => 'Des magazines adaptés à tous les âges pour éveiller la curiosité des plus jeunes.',
                    'icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422A12.083 12.083 0 0112 20.055a12.083 12.083 0 01-6.16-9.477L12 14z',
                    'slug' => 'presse_jeunesse',
                ],
            ],
            'services' => [
                'Réservation de vos titres préférés à l\'avance',
                'Livraison quotidienne dès l\'ouverture',
                'Large choix de magazines spécialisés sur commande',
            ],
        ]);
    }
}
