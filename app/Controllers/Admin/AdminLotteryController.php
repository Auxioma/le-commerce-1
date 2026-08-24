<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\Lottery;
use App\Models\LotteryEntry;
use App\Service\NotificationService;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

class AdminLotteryController extends Controller
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

        $statusFilter = (string) $this->input('statut', 'active');
        $statusMap = ['actives' => 'active', 'brouillons' => 'brouillon', 'terminees' => 'terminee', 'toutes' => null];
        $status = array_key_exists($statusFilter, $statusMap) ? $statusMap[$statusFilter] : 'active';

        $this->view('admin/lotteries/index', [
            'title'     => 'Loterie — Administration Le Commerce',
            'pageTitle' => 'Loterie',

            'lotteries'      => Lottery::listWithStats($status),
            'activeTab'      => $statusFilter,
            'lotteriesActive'=> Lottery::countByStatus('active'),
            'lotteriesDraft' => Lottery::countByStatus('brouillon'),
            'lotteriesDone'  => Lottery::countByStatus('terminee'),
        ], 'admin');
    }

    public function create(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/lotteries/create', [
            'title'     => 'Créer une loterie — Administration Le Commerce',
            'pageTitle' => 'Créer une nouvelle loterie',
            'errors' => [],
            'old'    => [],
        ], 'admin');
    }

    public function store(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $title       = trim((string) $this->input('title', ''));
        $description = trim((string) $this->input('description', ''));
        $prize       = trim((string) $this->input('prize', ''));
        $endsAt      = (string) $this->input('ends_at', '');
        $publish     = $this->input('publish') ? 'active' : 'brouillon';

        $errors = [];
        if ($title === '' || mb_strlen($title) > 150) {
            $errors['title'] = 'Le titre est obligatoire (150 caractères maximum).';
        }
        if ($prize === '' || mb_strlen($prize) > 150) {
            $errors['prize'] = 'Le lot à gagner est obligatoire.';
        }
        if ($endsAt === '' || strtotime($endsAt) === false || strtotime($endsAt) < strtotime('today')) {
            $errors['ends_at'] = 'La date de fin doit être aujourd\'hui ou une date future.';
        }

        if ($errors) {
            $this->view('admin/lotteries/create', [
                'title'     => 'Créer une loterie — Administration Le Commerce',
                'pageTitle' => 'Créer une nouvelle loterie',
                'errors' => $errors,
                'old'    => compact('title', 'description', 'prize', 'endsAt'),
            ], 'admin');
            return;
        }

        $lotteryId = Lottery::create([
            'title'       => $title,
            'description' => $description ?: null,
            'prize'       => $prize,
            'ends_at'     => date('Y-m-d', strtotime($endsAt)),
            'status'      => $publish,
            'qr_token'    => Lottery::generateUniqueQrToken(),
        ]);

        $this->setFlash('success', 'La loterie "' . $title . '" a bien été créée. Voici son QR code de participation.');
        $this->redirect('/admin/loterie/' . $lotteryId . '/qrcode');
    }

    public function toggleStatus(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $lottery = Lottery::find($id);
        if (!$lottery || $lottery['status'] === 'terminee') {
            $this->setFlash('error', 'Loterie introuvable ou déjà terminée.');
            $this->redirect('/admin/loterie');
            return;
        }

        $newStatus = $lottery['status'] === 'active' ? 'brouillon' : 'active';
        Lottery::update($id, ['status' => $newStatus]);

        $this->setFlash('success', 'Statut de la loterie mis à jour.');
        $this->redirect('/admin/loterie');
    }

    /**
     * Tirage au sort : sélectionne aléatoirement un participant parmi les
     * tickets de la loterie, l'enregistre comme gagnant et clôt la loterie.
     */
    public function draw(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $lottery = Lottery::find($id);
        if (!$lottery) {
            $this->setFlash('error', 'Loterie introuvable.');
            $this->redirect('/admin/loterie');
            return;
        }
        if ($lottery['status'] === 'terminee') {
            $this->setFlash('error', 'Cette loterie a déjà été tirée au sort.');
            $this->redirect('/admin/loterie');
            return;
        }

        $winnerEntry = LotteryEntry::randomEntryForLottery($id);
        if (!$winnerEntry) {
            $this->setFlash('error', 'Aucun participant inscrit à cette loterie, tirage impossible.');
            $this->redirect('/admin/loterie');
            return;
        }

        Lottery::update($id, [
            'winner_user_id' => $winnerEntry['user_id'],
            'drawn_at'       => date('Y-m-d H:i:s'),
            'status'         => 'terminee',
        ]);

        $this->notificationService->sendWhatsapp(
            (int) $winnerEntry['user_id'],
            "🎉 Félicitations " . $winnerEntry['first_name'] . " !\nVous avez remporté la loterie « " . $lottery['title'] . " » : " . $lottery['prize'] . " !\nPassez en boutique pour récupérer votre lot."
        );

        $this->setFlash('success', 'Tirage effectué : ' . $winnerEntry['first_name'] . ' ' . $winnerEntry['last_name'] . ' remporte "' . $lottery['prize'] . '".');
        $this->redirect('/admin/loterie');
    }

    public function destroy(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        Lottery::delete($id);
        $this->setFlash('success', 'Loterie supprimée.');
        $this->redirect('/admin/loterie');
    }

    /**
     * Page d'affichage du QR code de participation (à imprimer/afficher en
     * boutique), avec le lien public en clair.
     */
    public function qrcode(int $id): void
    {
        Middleware::requireRole('admin');

        $lottery = Lottery::find($id);
        if (!$lottery) {
            $this->setFlash('error', 'Loterie introuvable.');
            $this->redirect('/admin/loterie');
            return;
        }

        $this->view('admin/lotteries/qrcode', [
            'title'     => 'QR code — ' . $lottery['title'] . ' — Administration Le Commerce',
            'pageTitle' => 'QR code — ' . $lottery['title'],
            'lottery'   => $lottery,
            'publicUrl' => $this->publicLotteryUrl($lottery),
        ], 'admin');
    }

    /**
     * Image PNG du QR code, générée à la volée (jamais stockée sur disque).
     */
    public function qrcodeImage(int $id): void
    {
        Middleware::requireRole('admin');

        $lottery = Lottery::find($id);
        if (!$lottery) {
            http_response_code(404);
            return;
        }

        $result = (new Builder())->build(
            writer: new PngWriter(),
            data: $this->publicLotteryUrl($lottery),
            size: 480,
            margin: 16,
            errorCorrectionLevel: ErrorCorrectionLevel::High,
        );

        header('Content-Type: ' . $result->getMimeType());
        header('Content-Disposition: inline; filename="loterie-' . $lottery['id'] . '.png"');
        echo $result->getString();
    }

    /**
     * URL publique encodée dans le QR code — reconstruite depuis l'hôte de
     * la requête courante (et non depuis APP_URL, qui peut être désynchronisé
     * du domaine réellement servi en production).
     */
    private function publicLotteryUrl(array $lottery): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return 'https://' . $host . BASE_PATH . '/loterie/' . $lottery['qr_token'];
    }
}
