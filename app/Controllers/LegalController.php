<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class LegalController extends Controller
{
    public function mentionsLegales(): void
    {
        $this->view('pages/mentions-legales', [
            'title'   => 'Mentions légales — Le Commerce',
            'description' => 'Mentions légales du site Le Commerce : éditeur, hébergeur et informations légales de l\'établissement.',
            'heading' => 'Mentions légales',
        ]);
    }

    public function cgu(): void
    {
        $this->view('pages/cgu', [
            'title'   => "Conditions Générales d'Utilisation — Le Commerce",
            'description' => 'Conditions Générales d\'Utilisation du site et de l\'espace client Le Commerce.',
            'heading' => "Conditions Générales d'Utilisation",
        ]);
    }

    public function cgv(): void
    {
        $this->view('pages/cgv', [
            'title'   => 'Conditions Générales de Vente — Le Commerce',
            'description' => 'Conditions Générales de Vente applicables aux offres, réservations et achats du Commerce.',
            'heading' => 'Conditions Générales de Vente',
        ]);
    }

    public function confidentialite(): void
    {
        $this->view('pages/confidentialite', [
            'title'   => 'Politique de Confidentialité — Le Commerce',
            'description' => 'Politique de confidentialité et protection des données personnelles (RGPD) du Commerce.',
            'heading' => 'Politique de Confidentialité & RGPD',
        ]);
    }
}
