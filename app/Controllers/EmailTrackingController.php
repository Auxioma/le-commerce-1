<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Service\LotteryAnnouncementService;

/**
 * Pixel de suivi (GIF transparent 1x1) intégré à l'e-mail d'annonce des
 * loteries : chaque chargement de l'image enregistre l'ouverture de l'e-mail
 * par le client. L'URL est signée pour empêcher de fausser les statistiques.
 * Le pixel est toujours renvoyé, même si le lien est invalide.
 */
class EmailTrackingController
{
    private const PIXEL = 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    public function lotteryOpen(string $lotteryId, string $userId, string $token): void
    {
        $token = preg_replace('/\.gif$/', '', $token);

        if (ctype_digit($lotteryId) && ctype_digit($userId)
            && LotteryAnnouncementService::isValidTrackingToken((int) $lotteryId, (int) $userId, $token)) {
            try {
                Database::connection()->prepare(
                    'INSERT INTO lottery_email_opens (lottery_id, user_id) VALUES (:lottery, :user)
                     ON DUPLICATE KEY UPDATE open_count = open_count + 1, last_opened_at = NOW()'
                )->execute(['lottery' => (int) $lotteryId, 'user' => (int) $userId]);
            } catch (\PDOException) {
                // Loterie ou client supprimé : on renvoie quand même l'image.
            }
        }

        header('Content-Type: image/gif');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        echo base64_decode(self::PIXEL);
    }
}
