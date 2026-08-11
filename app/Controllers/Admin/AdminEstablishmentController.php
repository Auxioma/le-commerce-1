<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\Offer;
use App\Models\OfferRedemption;
use App\Models\Settings;
use App\Models\User;

/**
 * Gère la fiche d'identité de l'établissement (coordonnées, adresse,
 * horaires, réseaux sociaux, visite virtuelle). Ces informations sont
 * utilisées partout sur le site public (en-tête, pied de page, contact,
 * campagnes de proximité).
 *
 * Séparé de AdminSettingsController (qui ne conserve que les mentions
 * légales et la configuration Google Analytics) pour éviter d'avoir deux
 * formulaires différents modifiant les mêmes données.
 */
class AdminEstablishmentController extends Controller
{
    private const DAYS = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];

    public function index(): void
    {
        Middleware::requireRole('admin');

        $todayIndex = (int) date('N') - 1; // 0 = lundi ... 6 = dimanche

        $this->view('admin/establishment', [
            'title'     => 'Mon établissement — Administration Le Commerce',
            'pageTitle' => 'Mon établissement',

            'totalClients'         => User::countAll(),
            'activeOffers'         => Offer::countByStatus('active'),
            'redemptionsThisMonth' => OfferRedemption::countUsedThisMonth(),
            'savingsEstimate'      => OfferRedemption::sumSavings(),

            'todayLabel'  => self::DAYS[$todayIndex],
            'isSunday'    => $todayIndex === 6,
        ], 'admin');
    }

    public function update(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $data = [
            'shop_name'        => trim((string) $this->input('shop_name', '')),
            'shop_phone'       => trim((string) $this->input('shop_phone', '')),
            'shop_whatsapp'    => trim((string) $this->input('shop_whatsapp', '')),
            'shop_email'       => trim((string) $this->input('shop_email', '')),
            'shop_address'     => trim((string) $this->input('shop_address', '')),
            'shop_zipcode'     => trim((string) $this->input('shop_zipcode', '')),
            'shop_city'        => trim((string) $this->input('shop_city', '')),
            'latitude'         => trim((string) $this->input('latitude', '')),
            'longitude'        => trim((string) $this->input('longitude', '')),
            'streetview_embed_url' => trim((string) $this->input('streetview_embed_url', '')),
            'hours_lun_sam'    => trim((string) $this->input('hours_lun_sam', '')),
            'hours_dim'        => trim((string) $this->input('hours_dim', '')),
            'social_facebook'  => trim((string) $this->input('social_facebook', '')),
            'social_instagram' => trim((string) $this->input('social_instagram', '')),
        ];

        if ($data['shop_name'] === '') {
            $this->setFlash('error', 'Le nom du commerce est obligatoire.');
            $this->redirect('/admin/etablissement');
            return;
        }
        if ($data['shop_email'] !== '' && !filter_var($data['shop_email'], FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'Adresse e-mail invalide.');
            $this->redirect('/admin/etablissement');
            return;
        }
        if ($data['latitude'] !== '' && !is_numeric($data['latitude'])) {
            $this->setFlash('error', 'La latitude doit être une valeur numérique.');
            $this->redirect('/admin/etablissement');
            return;
        }
        if ($data['longitude'] !== '' && !is_numeric($data['longitude'])) {
            $this->setFlash('error', 'La longitude doit être une valeur numérique.');
            $this->redirect('/admin/etablissement');
            return;
        }

        Settings::updateMany($data);

        $this->setFlash('success', 'Fiche établissement mise à jour avec succès.');
        $this->redirect('/admin/etablissement');
    }
}
