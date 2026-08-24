<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\GoogleAnalyticsClient;
use App\Core\Middleware;
use App\Models\Settings;

class AdminSettingsController extends Controller
{
    public function index(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/settings/index', [
            'title'     => 'Paramètres — Administration Le Commerce',
            'pageTitle' => 'Paramètres',

            'ga4OAuthClientPresent'    => GoogleAnalyticsClient::hasOAuthClient(),
            'ga4OAuthAuthorized'       => GoogleAnalyticsClient::isOAuthAuthorized(),
            'ga4PropertyId'            => GoogleAnalyticsClient::propertyId(),
        ], 'admin');
    }

    public function update(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $data = [
            'legal_forme_juridique'       => trim((string) $this->input('legal_forme_juridique', '')),
            'legal_capital_social'        => trim((string) $this->input('legal_capital_social', '')),
            'legal_siret'                 => trim((string) $this->input('legal_siret', '')),
            'legal_rcs_numero'            => trim((string) $this->input('legal_rcs_numero', '')),
            'legal_rcs_ville'             => trim((string) $this->input('legal_rcs_ville', '')),
            'legal_directeur_publication' => trim((string) $this->input('legal_directeur_publication', '')),
            'legal_hebergeur_nom'         => trim((string) $this->input('legal_hebergeur_nom', '')),
            'legal_hebergeur_adresse'     => trim((string) $this->input('legal_hebergeur_adresse', '')),
            'legal_hebergeur_telephone'   => trim((string) $this->input('legal_hebergeur_telephone', '')),
        ];

        Settings::updateMany($data);

        $this->setFlash('success', 'Paramètres mis à jour avec succès.');
        $this->redirect('/admin/parametres');
    }
}
