<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\View;
use App\Models\Poll;

/**
 * Annonce d'un nouveau sondage aux clients par SMS (AllMySms) et/ou e-mail,
 * ciblée par segments de clients ou par sélection manuelle des destinataires
 * e-mail. Le ciblage, l'éligibilité (numéro, e-mail valide, désinscription)
 * et le lien de désinscription sont ceux de LotteryAnnouncementService.
 */
final class PollAnnouncementService
{
    /**
     * @param list<string> $channels sous-ensemble de LotteryAnnouncementService::CHANNELS
     * @param list<string> $segments segments ciblés, ou [LotteryAnnouncementService::ALL]
     * @param list<int> $emailRecipientIds clients choisis un par un pour
     *        l'e-mail (remplace alors le ciblage par segments pour ce canal)
     * @return array{recipients:int, sms:?array, smsConfigured:bool, smsEligible:int, emailSent:?int, emailFailed:int}
     */
    public function announce(array $poll, string $siteUrl, array $channels, array $segments, array $emailRecipientIds = []): array
    {
        $clients = LotteryAnnouncementService::recipients($segments);
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
            $emailClients = $clients;
            if ($emailRecipientIds !== []) {
                $picked = array_flip($emailRecipientIds);
                $emailClients = array_filter(
                    LotteryAnnouncementService::recipients([LotteryAnnouncementService::ALL]),
                    static fn(array $c) => isset($picked[(int) $c['id']])
                );
            }
            foreach (array_filter($emailClients, [LotteryAnnouncementService::class, 'canReceiveEmail']) as $client) {
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
