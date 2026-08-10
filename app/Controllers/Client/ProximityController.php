<?php

namespace App\Controllers\Client;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\Offer;
use App\Models\OfferRedemption;
use App\Models\ProximityCampaign;
use App\Models\UserLocation;

class ProximityController extends Controller
{
    /**
     * Reçoit la position GPS du client (envoyée par le navigateur) et
     * renvoie en JSON la campagne de proximité correspondante, s'il y en
     * a une (bon jour, bonne heure, bon segment, et à portée du rayon).
     */
    public function check(): void
    {
        Middleware::requireRole('client');

        $user = Middleware::user();

        if (!$user['geolocation_opt_in']) {
            $this->json(['match' => false, 'reason' => 'opt_out']);
            return;
        }

        $lat = (float) $this->input('lat', 0);
        $lng = (float) $this->input('lng', 0);
        if ($lat === 0.0 && $lng === 0.0) {
            $this->json(['match' => false, 'reason' => 'position_invalide']);
            return;
        }

        // Mémorise la dernière position connue (pour la carte "Clients à
        // proximité" du back-office), uniquement pour les clients opt-in.
        UserLocation::upsert((int) $user['id'], $lat, $lng);

        $shopLat = (float) $this->sharedData['shop']['latitude'];
        $shopLng = (float) $this->sharedData['shop']['longitude'];
        $distance = ProximityCampaign::distanceMeters($lat, $lng, $shopLat, $shopLng);

        $candidates = ProximityCampaign::activeNowForSegment($user['segment']);

        // Ne garde que les campagnes dont le rayon couvre la distance actuelle,
        // et choisit la plus petite zone (la plus ciblée) en cas de plusieurs matches.
        $matches = array_values(array_filter($candidates, fn ($c) => $distance <= (int) $c['radius_m']));
        usort($matches, fn ($a, $b) => $a['radius_m'] <=> $b['radius_m']);
        $campaign = $matches[0] ?? null;

        if (!$campaign) {
            $this->json(['match' => false, 'reason' => 'hors_zone', 'distance' => round($distance)]);
            return;
        }

        // Comptabilise l'envoi une seule fois par campagne et par session,
        // pour éviter de gonfler le compteur à chaque position GPS reçue.
        $sessionKey = 'proximity_notified_' . $campaign['id'];
        if (empty($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = true;
            ProximityCampaign::incrementSent((int) $campaign['id']);
        }

        $this->json([
            'match'    => true,
            'distance' => round($distance),
            'campaign' => [
                'id'      => $campaign['id'],
                'name'    => $campaign['name'],
                'message' => $campaign['message'],
                'offer_title' => $campaign['offer_title'],
                'end_time'    => substr($campaign['end_time'], 0, 5),
            ],
        ]);
    }

    /**
     * Le client clique sur "J'en profite" : génère le code de l'offre liée
     * (le cas échéant) et comptabilise l'utilisation de la campagne.
     */
    public function claim(int $id): void
    {
        Middleware::requireRole('client');
        $this->verifyCsrf();

        $user = Middleware::user();
        $campaign = ProximityCampaign::find($id);

        if (!$campaign || $campaign['status'] !== 'active') {
            $this->setFlash('error', 'Cette offre de proximité n\'est plus disponible.');
            $this->redirect('/mon-compte');
            return;
        }

        ProximityCampaign::incrementUsed($id);

        if ($campaign['offer_id']) {
            $offer = Offer::find((int) $campaign['offer_id']);
            if ($offer) {
                $redemption = OfferRedemption::generate((int) $offer['id'], (int) $user['id'], 'qr_caisse');
                $this->setFlash('success', 'Votre offre "' . $offer['title'] . '" est prête ! Code : ' . $redemption['code'] . '. Présentez-le en caisse.');
                $this->redirect('/mon-compte/offres');
                return;
            }
        }

        $this->setFlash('success', 'Merci, votre visite a bien été enregistrée !');
        $this->redirect('/mon-compte');
    }
}
