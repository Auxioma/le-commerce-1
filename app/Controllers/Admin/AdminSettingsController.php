<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
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
        ], 'admin');
    }

    public function update(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $data = [
            'shop_name'        => trim((string) $this->input('shop_name', '')),
            'shop_address'     => trim((string) $this->input('shop_address', '')),
            'shop_zipcode'     => trim((string) $this->input('shop_zipcode', '')),
            'shop_city'        => trim((string) $this->input('shop_city', '')),
            'shop_phone'       => trim((string) $this->input('shop_phone', '')),
            'shop_whatsapp'    => trim((string) $this->input('shop_whatsapp', '')),
            'shop_email'       => trim((string) $this->input('shop_email', '')),
            'hours_lun_sam'    => trim((string) $this->input('hours_lun_sam', '')),
            'hours_dim'        => trim((string) $this->input('hours_dim', '')),
            'social_facebook'  => trim((string) $this->input('social_facebook', '')),
            'social_instagram' => trim((string) $this->input('social_instagram', '')),
            'latitude'         => trim((string) $this->input('latitude', '')),
            'longitude'        => trim((string) $this->input('longitude', '')),
            'streetview_embed_url' => trim((string) $this->input('streetview_embed_url', '')),

            'legal_forme_juridique'       => trim((string) $this->input('legal_forme_juridique', '')),
            'legal_capital_social'        => trim((string) $this->input('legal_capital_social', '')),
            'legal_siret'                 => trim((string) $this->input('legal_siret', '')),
            'legal_rcs_numero'            => trim((string) $this->input('legal_rcs_numero', '')),
            'legal_rcs_ville'             => trim((string) $this->input('legal_rcs_ville', '')),
            'legal_directeur_publication' => trim((string) $this->input('legal_directeur_publication', '')),
            'legal_hebergeur_nom'         => trim((string) $this->input('legal_hebergeur_nom', '')),
            'legal_hebergeur_adresse'     => trim((string) $this->input('legal_hebergeur_adresse', '')),
            'legal_hebergeur_telephone'   => trim((string) $this->input('legal_hebergeur_telephone', '')),

            'ga4_property_id'          => trim((string) $this->input('ga4_property_id', '')),
            'ga4_service_account_json' => trim((string) $this->input('ga4_service_account_json', '')),
        ];

        if ($data['shop_name'] === '' || $data['shop_email'] === '' || !filter_var($data['shop_email'], FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'Le nom et une adresse e-mail valide sont obligatoires.');
            $this->redirect('/admin/parametres');
            return;
        }

        Settings::updateMany($data);

        $this->setFlash('success', 'Paramètres mis à jour avec succès.');
        $this->redirect('/admin/parametres');
    }
}
