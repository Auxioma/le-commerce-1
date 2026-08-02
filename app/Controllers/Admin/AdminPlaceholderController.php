<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\ContactMessage;
use App\Models\Offer;
use App\Models\OfferRedemption;
use App\Models\ProximityCampaign;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WhatsappMessage;

class AdminPlaceholderController extends Controller
{
    /**
     * Libellés humains des sections admin à venir (Lots 6 à 10).
     * Toute section absente de cette liste renvoie une 404 classique.
     */
    private const SECTIONS = [
        'etablissement' => 'Mon établissement',
        'services'      => 'Services du quotidien',
        'portefeuilles' => 'Portefeuille client',
        'reservations'  => 'Réservations',
    ];

    private const SECTION_FEATURES = [
        'etablissement' => [
            'Fiches établissement enrichies',
            'Gestion des horaires et coordonnées',
            'Création de vitrine et visuels',
            'Mise en avant des services proposés',
        ],
        'services' => [
            'Catalogue de services rapides',
            'Gestion des catégories et tarifs',
            'Disponibilités en temps réel',
            'Recommandations pour vos clients',
        ],
        'portefeuilles' => [
            'Solde et historique de consommation',
            'Recharges et offres dédiées',
            'Segmentation des clients fidèles',
            'Alertes et notifications personnalisées',
        ],
        'reservations' => [
            'Calendrier de réservation',
            'Gestion des créneaux disponibles',
            'Confirmations et rappels',
            'Statistiques de fréquentation',
        ],
    ];

    public function show(string $section): void
    {
        Middleware::requireRole('admin');

        if (!isset(self::SECTIONS[$section])) {
            http_response_code(404);
            require dirname(__DIR__, 2) . '/Views/errors/404.php';
            return;
        }

        $label = self::SECTIONS[$section];

        if ($section === 'etablissement') {
            $this->view('admin/establishment', [
                'title'            => $label . ' — Administration Le Commerce',
                'pageTitle'        => $label,
                'activeOffers'     => Offer::countByStatus('active'),
                'redemptionsThisMonth' => OfferRedemption::countUsedThisMonth(),
                'savingsEstimate'  => OfferRedemption::sumSavings(),
                'totalClients'     => User::countAll(),
            ], 'admin');
            return;
        }

        if ($section === 'services') {
            $this->view('admin/services', [
                'title'             => $label . ' — Administration Le Commerce',
                'pageTitle'         => $label,
                'activeOffers'      => Offer::countByStatus('active'),
                'newOffers'         => Offer::countCreatedThisMonth(),
                'activeCampaigns'   => ProximityCampaign::countByStatus('active'),
                'totalClients'      => User::countAll(),
                'activeOffersList'  => Offer::listWithUsage('active'),
            ], 'admin');
            return;
        }

        if ($section === 'portefeuilles') {
            $this->view('admin/wallets', [
                'title'              => $label . ' — Administration Le Commerce',
                'pageTitle'          => $label,
                'totalBalance'       => Wallet::totalBalance(),
                'walletCount'        => Wallet::count(),
                'rechargesThisMonth' => WalletTransaction::countByType('recharge', 'this_month'),
                'latestTransactions' => WalletTransaction::latestWithUser(5),
                'topClients'         => Wallet::topByBalance(5),
            ], 'admin');
            return;
        }

        if ($section === 'reservations') {
            $latestContacts = ContactMessage::latest(5);
            $latestWhatsapps = WhatsappMessage::latest(5);
            $messageDemandCount = ContactMessage::count() + WhatsappMessage::countByDirection('entrant');

            $this->view('admin/reservations', [
                'title'              => $label . ' — Administration Le Commerce',
                'pageTitle'          => $label,
                'contactTotal'       => ContactMessage::count(),
                'whatsappTotal'      => WhatsappMessage::countByDirection('entrant'),
                'messageDemandCount' => $messageDemandCount,
                'latestContacts'     => $latestContacts,
                'latestWhatsapps'    => $latestWhatsapps,
            ], 'admin');
            return;
        }

        $features = self::SECTION_FEATURES[$section] ?? [];

        $this->view('admin/placeholder', [
            'title'     => $label . ' — Administration Le Commerce',
            'pageTitle' => $label,
            'heading'   => $label,
            'features'  => $features,
        ], 'admin');
    }
}
