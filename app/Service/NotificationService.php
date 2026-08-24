<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\SmsMessage;
use App\Models\WhatsappMessage;

/**
 * Point d'envoi unique pour les messages WhatsApp/SMS sortants. Pour l'instant
 * l'envoi est simulé (journalisation en base) ; c'est ici que l'intégration
 * réelle à l'API WhatsApp Business / une passerelle SMS sera branchée plus
 * tard, sans que les contrôleurs aient à changer.
 */
final class NotificationService
{
    public function sendWhatsapp(int $userId, string $content): void
    {
        if ($content === '') {
            return;
        }

        WhatsappMessage::create([
            'user_id'   => $userId,
            'direction' => 'sortant',
            'content'   => $content,
        ]);
    }

    public function sendSms(int $userId, string $content): void
    {
        if ($content === '') {
            return;
        }

        SmsMessage::create([
            'user_id'   => $userId,
            'direction' => 'sortant',
            'content'   => $content,
        ]);
    }

    public function send(string $channel, int $userId, string $content): void
    {
        if ($channel === 'sms') {
            $this->sendSms($userId, $content);
            return;
        }

        $this->sendWhatsapp($userId, $content);
    }
}
