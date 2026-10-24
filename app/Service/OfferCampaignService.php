<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\View;
use App\Models\OfferRedemption;

/**
 * Diffusion d'une offre aux clients par SMS (AllMySms) et/ou e-mail, aux
 * clients cochés un par un dans le tableau des destinataires. Chaque client reçoit son propre code (offer_redemptions, réutilisé
 * s'il en a déjà un valide) à présenter en caisse.
 *
 * Le tableau des destinataires, l'éligibilité (numéro, e-mail valide, désinscription) et le
 * lien de désinscription sont ceux de LotteryAnnouncementService.
 */
final class OfferCampaignService
{
    /**
     * @param list<string> $channels sous-ensemble de LotteryAnnouncementService::CHANNELS
     * @param list<int> $recipientIds clients choisis dans le tableau
     * @return array{recipients:int, sms:?array, smsConfigured:bool, smsEligible:int, emailSent:?int, emailFailed:int}
     */
    public function send(array $offer, string $siteUrl, array $channels, array $recipientIds): array
    {
        $clients = LotteryAnnouncementService::pickedClients($recipientIds);
        $offersUrl = $siteUrl . '/mon-compte/offres';
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
            $smsClients = array_values(array_filter($clients, [LotteryAnnouncementService::class, 'canReceiveSms']));
            $result['smsEligible'] = count($smsClients);
            $result['smsConfigured'] = $smsService->isConfigured();
            if ($smsClients !== [] && $result['smsConfigured']) {
                $recipients = [];
                foreach ($smsClients as $client) {
                    $recipients[] = [
                        'phone'     => trim((string) $client['phone_whatsapp']),
                        'firstName' => (string) $client['first_name'],
                        'code'      => OfferRedemption::generate((int) $offer['id'], (int) $client['id'], 'sms')['code'],
                    ];
                }
                $result['sms'] = $smsService->sendOffer($offer, $offersUrl, $recipients);
            }
        }

        if (in_array('email', $channels, true)) {
            $result['emailSent'] = 0;
            $mailer = new Mailer();
            $shopName = (string) (require dirname(__DIR__, 2) . '/config/app.php')['shop']['name'];
            set_time_limit(0); // un e-mail par client : peut prendre du temps
            foreach (array_filter($clients, [LotteryAnnouncementService::class, 'canReceiveEmail']) as $client) {
                $redemption = OfferRedemption::generate((int) $offer['id'], (int) $client['id'], 'email');
                $html = View::render('emails/offer', [
                    'shopName'       => $shopName,
                    'firstName'      => $client['first_name'],
                    'offer'          => $offer,
                    'code'           => $redemption['code'],
                    'offersUrl'      => $offersUrl,
                    'unsubscribeUrl' => LotteryAnnouncementService::unsubscribeUrl($siteUrl, (int) $client['id']),
                ]);
                $sent = $mailer->send((string) $client['email'], 'Une offre rien que pour vous : ' . $offer['title'], $html);
                $sent ? $result['emailSent']++ : $result['emailFailed']++;
            }
        }

        return $result;
    }
}
