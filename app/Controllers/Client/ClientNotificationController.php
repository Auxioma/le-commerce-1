<?php

declare(strict_types=1);

namespace App\Controllers\Client;

use App\Core\Controller;
use App\Core\Middleware;
use App\Service\ClientNotificationService;

class ClientNotificationController extends Controller
{
    public function index(): void
    {
        Middleware::requireRole('client');

        $user  = Middleware::user();
        $feed  = (new ClientNotificationService())->feed((int) $user['id']);

        // Regroupement par jour pour l'affichage (Aujourd'hui / Hier / date).
        $groups = [];
        foreach ($feed as $item) {
            $day = substr($item['date'], 0, 10);
            $groups[$day][] = $item;
        }

        $this->view('client/notifications', [
            'title'   => 'Notifications — Le Commerce',
            'user'    => $user,
            'groups'  => $groups,
            'total'   => count($feed),
            'today'   => date('Y-m-d'),
            'yesterday' => date('Y-m-d', strtotime('-1 day')),
        ], 'client');
    }
}
