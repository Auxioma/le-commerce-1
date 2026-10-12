<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Service\LotteryAnnouncementService;

/**
 * Lien « Ne plus recevoir nos e-mails » présent dans chaque e-mail de
 * communication. Le lien (signé, sans connexion) affiche une confirmation ;
 * la désinscription n'est faite qu'au clic sur le bouton, pour éviter que
 * les antivirus de messagerie qui ouvrent les liens ne désinscrivent le client.
 */
class EmailUnsubscribeController extends Controller
{
    public function show(string $id, string $token): void
    {
        $user = $this->findUser($id, $token);

        $this->view('pages/email-unsubscribe', [
            'title'       => 'Désinscription des e-mails — Le Commerce',
            'user'        => $user,
            'token'       => $token,
            'done'        => $user !== null && !empty($user['email_unsubscribed_at']),
        ]);
    }

    public function confirm(string $id, string $token): void
    {
        $this->verifyCsrf();

        $user = $this->findUser($id, $token);
        if ($user !== null && empty($user['email_unsubscribed_at'])) {
            User::update((int) $user['id'], ['email_unsubscribed_at' => date('Y-m-d H:i:s')]);
        }

        $this->redirect('/desabonnement/' . (int) $id . '/' . $token);
    }

    private function findUser(string $id, string $token): ?array
    {
        if (!ctype_digit($id) || !LotteryAnnouncementService::isValidUnsubscribeToken((int) $id, $token)) {
            return null;
        }
        $user = User::find((int) $id);
        return $user && $user['role'] === 'client' ? $user : null;
    }
}
