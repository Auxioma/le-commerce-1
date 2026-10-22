<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\View;
use App\Models\Poll;

/**
 * Annonce d'un nouveau sondage aux clients par SMS (AllMySms) et/ou e-mail,
 * aux clients choisis un par un dans le tableau du formulaire. L'éligibilité
 * (numéro, e-mail valide, désinscription) et le lien de désinscription sont
 * ceux de LotteryAnnouncementService.
 */
final class PollAnnouncementService
{
    /**
     * Lignes du tableau de choix des destinataires (clients actifs) :
     * identité, coordonnées, groupe (segment de cycle de vie), ventes,
     * joignabilité SMS / e-mail, inscription et dernière visite.
     *
     * @return list<array{id:int, first:string, last:string, email:string, phone:string, group:?string, sales:float, sms:bool, mail:bool, created:string, visit:?string}>
     */
    public static function clientTable(): array
    {
        $segmentation = ClientSegmentationService::computeAll();
        $rows = [];
        foreach (LotteryAnnouncementService::recipients([LotteryAnnouncementService::ALL]) as $client) {
            $seg = $segmentation[(int) $client['id']] ?? null;
            $rows[] = [
                'id'      => (int) $client['id'],
                'first'   => (string) $client['first_name'],
                'last'    => (string) $client['last_name'],
                'email'   => trim((string) $client['email']),
                'phone'   => trim((string) $client['phone_whatsapp']),
                'group'   => $seg['lifecycle'] ?? null,
                'sales'   => round((float) ($seg['metrics']['spend_total'] ?? 0), 2),
                'sms'     => LotteryAnnouncementService::canReceiveSms($client),
                'mail'    => LotteryAnnouncementService::canReceiveEmail($client),
                'created' => (string) $client['created_at'],
                'visit'   => $seg['metrics']['last_visit'] ?? null,
            ];
        }
        return $rows;
    }

    /**
     * @param list<string> $channels sous-ensemble de LotteryAnnouncementService::CHANNELS
     * @param list<int> $recipientIds clients choisis dans le tableau
     * @return array{recipients:int, sms:?array, smsConfigured:bool, smsEligible:int, emailSent:?int, emailFailed:int}
     */
    public function announce(array $poll, string $siteUrl, array $channels, array $recipientIds): array
    {
        $picked = array_flip($recipientIds);
        $clients = array_values(array_filter(
            LotteryAnnouncementService::recipients([LotteryAnnouncementService::ALL]),
            static fn(array $c) => isset($picked[(int) $c['id']])
        ));
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
