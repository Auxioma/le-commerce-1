<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\View;
use App\Models\Poll;

/**
 * Annonce d'un nouveau sondage aux clients par SMS (AllMySms) et/ou e-mail,
 * aux clients choisis un par un dans le tableau du formulaire. L'éligibilité
 * (numéro, e-mail valide, désinscription), le tableau des destinataires et
 * le lien de désinscription sont ceux de LotteryAnnouncementService.
 */
final class PollAnnouncementService
{
    /**
     * @param list<string> $channels sous-ensemble de LotteryAnnouncementService::CHANNELS
     * @param list<int> $recipientIds clients choisis dans le tableau
     * @return array{recipients:int, sms:?array, smsConfigured:bool, smsEligible:int, emailSent:?int, emailFailed:int}
     */
    public function announce(array $poll, string $siteUrl, array $channels, array $recipientIds): array
    {
        $clients = LotteryAnnouncementService::pickedClients($recipientIds);
        $pollUrl = $siteUrl . '/mon-compte/sondages/' . $poll['id'];
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
            if ($smsClients !== []) {
                $result['sms'] = $smsService->announcePoll($poll, $pollUrl, $smsClients);
            }
        }

        if (in_array('email', $channels, true)) {
            $result['emailSent'] = 0;
            $mailer = new Mailer();
            $shopName = (string) (require dirname(__DIR__, 2) . '/config/app.php')['shop']['name'];
            set_time_limit(0); // un e-mail par client : peut prendre du temps
            foreach (array_filter($clients, [LotteryAnnouncementService::class, 'canReceiveEmail']) as $client) {
                $html = View::render('emails/poll-announcement', [
                    'shopName'       => $shopName,
                    'firstName'      => $client['first_name'],
                    'poll'           => $poll,
                    'rewardLabel'    => Poll::REWARD_LABELS[$poll['reward_type']] ?? null,
                    'pollUrl'        => $pollUrl,
                    'unsubscribeUrl' => LotteryAnnouncementService::unsubscribeUrl($siteUrl, (int) $client['id']),
                ]);
                $sent = $mailer->send((string) $client['email'], 'Votre avis compte : ' . $poll['question'], $html);
                $sent ? $result['emailSent']++ : $result['emailFailed']++;
            }
        }

        return $result;
    }
}
