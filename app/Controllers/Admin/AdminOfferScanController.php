<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\OfferRedemption;
use App\Service\NotificationService;

class AdminOfferScanController extends Controller
{
    private NotificationService $notificationService;

    public function __construct()
    {
        parent::__construct();
        $this->notificationService = new NotificationService();
    }

    public function index(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/offers/scanner', [
            'title'      => 'Scanner une offre — Administration Le Commerce',
            'pageTitle'  => 'Scanner une offre',
            'step'       => 'input',
            'redemption' => null,
            'error'      => null,
            'code'       => '',
        ], 'admin');
    }

    /**
     * Étape 1 : vérification du code saisi (sans le consommer).
     */
    public function verify(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $code = strtoupper(trim((string) $this->input('code', '')));
        $redemption = $code !== '' ? OfferRedemption::findByCodeWithDetails($code) : null;

        $step = 'input';
        $error = null;

        if ($code === '') {
            $error = 'Merci de saisir un code.';
        } elseif (!$redemption) {
            $error = "Ce code n'existe pas.";
        } elseif ($redemption['status'] === 'utilisee') {
            $step = 'already_used';
        } elseif ($redemption['status'] === 'expiree' || strtotime($redemption['valid_until']) < strtotime('today')) {
            $step = 'expired';
        } else {
            $step = 'valid';
        }

        $this->view('admin/offers/scanner', [
            'title'      => 'Scanner une offre — Administration Le Commerce',
            'pageTitle'  => 'Scanner une offre',
            'step'       => $step,
            'redemption' => $redemption,
            'error'      => $error,
            'code'       => $code,
        ], 'admin');
    }

    /**
     * Étape 2 : validation effective (consommation atomique du code).
     */
    public function redeem(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $code = strtoupper(trim((string) $this->input('code', '')));
        $result = OfferRedemption::redeem($code);

        if (!$result['success']) {
            $stepMap = ['deja_utilisee' => 'already_used', 'expiree' => 'expired', 'introuvable' => 'input'];
            $this->view('admin/offers/scanner', [
                'title'      => 'Scanner une offre — Administration Le Commerce',
                'pageTitle'  => 'Scanner une offre',
                'step'       => $stepMap[$result['reason']] ?? 'input',
                'redemption' => $result['data'],
                'error'      => $result['reason'] === 'introuvable' ? "Ce code n'existe pas." : null,
                'code'       => $code,
            ], 'admin');
            return;
        }

        $redemption = $result['data'];

        // Notifie le client que son offre a bien été utilisée
        $this->notificationService->sendWhatsapp(
            (int) $redemption['user_id'],
            "✅ Votre offre a été utilisée !\nOffre : " . $redemption['offer_title']
                . "\nUtilisée le : " . date('d/m/Y à H:i') . "\nMerci de votre visite, à très bientôt !"
        );

        $this->view('admin/offers/scanner', [
            'title'      => 'Scanner une offre — Administration Le Commerce',
            'pageTitle'  => 'Scanner une offre',
            'step'       => 'confirmed',
            'redemption' => $redemption,
            'error'      => null,
            'code'       => $code,
        ], 'admin');
    }
}
