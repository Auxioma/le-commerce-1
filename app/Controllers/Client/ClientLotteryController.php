<?php

namespace App\Controllers\Client;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\Lottery;
use App\Models\LotteryEntry;

class ClientLotteryController extends Controller
{
    public function index(): void
    {
        Middleware::requireRole('client');

        $user = Middleware::user();
        $lotteries = Lottery::activeForClients();

        $entries = [];
        foreach ($lotteries as $lottery) {
            $entries[$lottery['id']] = LotteryEntry::forUserAndLottery((int) $lottery['id'], (int) $user['id']);
        }

        $this->view('client/lotteries', [
            'title'      => 'Loterie — Le Commerce',
            'user'       => $user,
            'lotteries'  => $lotteries,
            'entries'    => $entries,
            'pastEntries'=> LotteryEntry::forUser((int) $user['id']),
        ], 'client');
    }

    public function participate(int $id): void
    {
        Middleware::requireRole('client');
        $this->verifyCsrf();

        $user = Middleware::user();
        $lottery = Lottery::find($id);

        if (!$lottery || $lottery['status'] !== 'active' || strtotime($lottery['ends_at']) < strtotime('today')) {
            $this->setFlash('error', 'Cette loterie n\'est plus disponible.');
            $this->redirect('/mon-compte/loterie');
            return;
        }

        LotteryEntry::generate($id, (int) $user['id']);

        $this->setFlash('success', 'Votre ticket de participation a bien été généré !');
        $this->redirect('/mon-compte/loterie');
    }
}
