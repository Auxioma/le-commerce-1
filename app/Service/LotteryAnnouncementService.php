<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\Database;
use App\Core\View;

/**
 * Annonce d'une nouvelle loterie aux clients : choix du mode de diffusion
 * (SMS via AllMySms et/ou e-mail) et ciblage par segments de clients
 * (ClientSegmentationService). Seuls les clients au compte actif sont
 * concernés ; les e-mails ne partent pas aux clients désinscrits et
 * contiennent toujours un lien de désinscription.
 */
final class LotteryAnnouncementService
{
    public const CHANNELS = [
        'sms'   => 'SMS',
        'email' => 'E-mail',
    ];

    /** Valeur « tous les clients » du ciblage. */
    public const ALL = 'tous';

    /**
     * Clients actifs ciblés : tous, ou l'union des segments choisis.
     *
     * @param list<string> $segments clés de segments, ou [self::ALL]
     */
    public static function recipients(array $segments): array
    {
        $clients = Database::connection()->query(
            "SELECT id, first_name, last_name, phone_whatsapp, email, email_unsubscribed_at
             FROM users WHERE role = 'client' AND status = 'actif' AND deleted_at IS NULL
             ORDER BY first_name ASC"
        )->fetchAll();

        if ($segments === [] || in_array(self::ALL, $segments, true)) {
            return $clients;
        }

        $ids = [];
        foreach ($segments as $segment) {
            foreach (ClientSegmentationService::idsInSegment($segment) as $id) {
                $ids[$id] = true;
            }
        }
        return array_values(array_filter($clients, static fn(array $c) => isset($ids[(int) $c['id']])));
    }

    public static function canReceiveSms(array $client): bool
    {
        return trim((string) ($client['phone_whatsapp'] ?? '')) !== '';
    }

    public static function canReceiveEmail(array $client): bool
    {
        return filter_var(trim((string) ($client['email'] ?? '')), FILTER_VALIDATE_EMAIL) !== false
            && empty($client['email_unsubscribed_at']);
    }

    /**
     * Données compactes des clients actifs pour estimer en direct, dans le
     * formulaire, le nombre de destinataires selon les cases cochées, et
     * alimenter le moteur de recherche des destinataires e-mail :
     * [[id, nom, e-mail (null si non joignable), segments[], sms bool, email bool], ...].
     */
    public static function audienceData(): array
    {
        $segmentation = ClientSegmentationService::computeAll();
        $data = [];
        foreach (self::recipients([self::ALL]) as $client) {
            $seg = $segmentation[(int) $client['id']] ?? null;
            $canEmail = self::canReceiveEmail($client);
            $data[] = [
                'id' => (int) $client['id'],
                'n' => trim($client['first_name'] . ' ' . $client['last_name']),
                'm' => $canEmail ? trim((string) $client['email']) : null,
                's' => $seg ? array_merge([$seg['lifecycle']], $seg['profiles']) : [],
                'sms' => self::canReceiveSms($client),
                'email' => $canEmail,
            ];
        }
        return $data;
    }

    /**
     * Envoie l'annonce sur les canaux choisis.
     *
     * @param list<string> $channels sous-ensemble de self::CHANNELS
     * @param list<string> $segments segments ciblés, ou [self::ALL]
     * @param list<int> $emailRecipientIds clients choisis un par un pour
     *        l'e-mail (remplace alors le ciblage par segments pour ce canal)
     * @return array{recipients:int, sms:?array, smsConfigured:bool, smsEligible:int, emailSent:?int, emailFailed:int}
     */
    public function announce(array $lottery, string $siteUrl, array $channels, array $segments, array $emailRecipientIds = []): array
    {
        $clients = self::recipients($segments);
        $lotteryUrl = $siteUrl . '/loterie/' . $lottery['qr_token'];
        $result = [
            'recipients'    => count($clients),
            'sms'           => null,
            'smsConfigured' => true,
            'smsEligible'   => 0,
            'emailSent'     => null,
            'emailFailed'   => 0,
        ];

        if (in_array('sms', $channels, true)) {
            $smsService = new SmsCampaignService();
            $smsClients = array_values(array_filter($clients, [self::class, 'canReceiveSms']));
            $result['smsEligible'] = count($smsClients);
            $result['smsConfigured'] = $smsService->isConfigured();
            if ($smsClients !== []) {
                $result['sms'] = $smsService->announceLottery($lottery, $lotteryUrl, $smsClients);
            }
        }

        if (in_array('email', $channels, true)) {
            $result['emailSent'] = 0;
            $mailer = new Mailer();
            $shopName = (string) (require dirname(__DIR__, 2) . '/config/app.php')['shop']['name'];
            set_time_limit(0); // un e-mail par client : peut prendre du temps
            $emailClients = $clients;
            if ($emailRecipientIds !== []) {
                $picked = array_flip($emailRecipientIds);
                $emailClients = array_filter(self::recipients([self::ALL]), static fn(array $c) => isset($picked[(int) $c['id']]));
            }
            foreach (array_filter($emailClients, [self::class, 'canReceiveEmail']) as $client) {
                $html = View::render('emails/lottery-announcement', [
                    'shopName'       => $shopName,
                    'firstName'      => $client['first_name'],
                    'lottery'        => $lottery,
                    'lotteryUrl'     => $lotteryUrl,
                    'unsubscribeUrl' => self::unsubscribeUrl($siteUrl, (int) $client['id']),
                    'trackingUrl'    => self::trackingUrl($siteUrl, (int) $lottery['id'], (int) $client['id']),
                ]);
                $sent = $mailer->send((string) $client['email'], 'Nouvelle loterie : ' . $lottery['prize'] . ' à gagner !', $html);
                $sent ? $result['emailSent']++ : $result['emailFailed']++;
            }
        }

        return $result;
    }

    // --- Suivi des ouvertures de l'e-mail (pixel) -----------------------

    public static function trackingUrl(string $siteUrl, int $lotteryId, int $userId): string
    {
        return $siteUrl . '/email/ouverture/' . $lotteryId . '/' . $userId . '/' . self::trackingToken($lotteryId, $userId) . '.gif';
    }

    public static function isValidTrackingToken(int $lotteryId, int $userId, string $token): bool
    {
        return hash_equals(self::trackingToken($lotteryId, $userId), $token);
    }

    private static function trackingToken(int $lotteryId, int $userId): string
    {
        $key = (string) (getenv('APP_KEY') ?: 'le-commerce');
        return substr(hash_hmac('sha256', 'email-open:' . $lotteryId . ':' . $userId, $key), 0, 32);
    }

    // --- Désinscription des e-mails commerciaux --------------------------

    public static function unsubscribeUrl(string $siteUrl, int $userId): string
    {
        return $siteUrl . '/desabonnement/' . $userId . '/' . self::unsubscribeToken($userId);
    }

    public static function isValidUnsubscribeToken(int $userId, string $token): bool
    {
        return hash_equals(self::unsubscribeToken($userId), $token);
    }

    private static function unsubscribeToken(int $userId): string
    {
        $key = (string) (getenv('APP_KEY') ?: 'le-commerce');
        return substr(hash_hmac('sha256', 'email-unsubscribe:' . $userId, $key), 0, 32);
    }
}
