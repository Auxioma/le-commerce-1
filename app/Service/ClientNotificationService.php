<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\LotteryEntry;
use App\Models\OfferRedemption;
use App\Models\Poll;
use App\Models\PollVote;
use App\Models\SmsMessage;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\WhatsappMessage;

/**
 * Construit le fil de notifications personnel d'un client à partir de tout ce
 * que le site a produit pour lui : messages WhatsApp/SMS, mouvements de
 * portefeuille, offres attribuées, participations et gains de loterie, votes
 * et nouveaux sondages, filleuls parrainés. Il n'existe pas de table
 * `notifications` dédiée : le fil est agrégé à la volée et trié par date.
 */
final class ClientNotificationService
{
    /**
     * @return list<array{kind:string, icon:string, accent:string, title:string, body:string, date:string, url:?string}>
     */
    public function feed(int $userId, int $limit = 60): array
    {
        $items = array_merge(
            $this->messages($userId),
            $this->wallet($userId),
            $this->offers($userId),
            $this->lottery($userId),
            $this->polls($userId),
            $this->referrals($userId),
        );

        // Tri chronologique décroissant (les dates sont au format 'Y-m-d H:i:s').
        usort($items, static fn (array $a, array $b) => strcmp($b['date'], $a['date']));

        return array_slice($items, 0, $limit);
    }

    /** @return list<array<string,mixed>> */
    private function messages(int $userId): array
    {
        $out = [];

        foreach (WhatsappMessage::outgoingForUser($userId) as $m) {
            $out[] = [
                'kind'   => 'message',
                'icon'   => 'chat',
                'accent' => 'emerald',
                'title'  => 'Message WhatsApp reçu',
                'body'   => self::excerpt((string) $m['content']),
                'date'   => (string) $m['sent_at'],
                'url'    => null,
            ];
        }
        foreach (SmsMessage::outgoingForUser($userId) as $m) {
            $out[] = [
                'kind'   => 'message',
                'icon'   => 'chat',
                'accent' => 'blue',
                'title'  => 'SMS reçu',
                'body'   => self::excerpt((string) $m['content']),
                'date'   => (string) $m['sent_at'],
                'url'    => null,
            ];
        }

        return $out;
    }

    /** @return list<array<string,mixed>> */
    private function wallet(int $userId): array
    {
        $labels = [
            'recharge'      => 'Portefeuille rechargé',
            'debit'         => 'Paiement effectué',
            'remboursement' => 'Crédit reçu',
        ];

        $out = [];
        foreach (WalletTransaction::forUser($userId, 50) as $t) {
            $type = (string) $t['type'];
            $sign = $type === 'debit' ? '−' : '+';
            $amount = number_format((float) $t['amount'], 2, ',', ' ');
            $body = $sign . ' ' . $amount . ' €';
            if (!empty($t['label'])) {
                $body .= ' · ' . $t['label'];
            }
            if ((string) $t['status'] !== 'reussi') {
                $body .= ' (' . $t['status'] . ')';
            }

            $out[] = [
                'kind'   => 'wallet',
                'icon'   => 'wallet',
                'accent' => $type === 'debit' ? 'brand' : 'emerald',
                'title'  => $labels[$type] ?? 'Mouvement de portefeuille',
                'body'   => $body,
                'date'   => (string) $t['created_at'],
                'url'    => '/mon-compte/transactions',
            ];
        }

        return $out;
    }

    /** @return list<array<string,mixed>> */
    private function offers(int $userId): array
    {
        $out = [];
        foreach (OfferRedemption::forUserWithOffer($userId) as $r) {
            $body = 'Code ' . $r['code'];
            if (!empty($r['valid_until'])) {
                $body .= ' · valable jusqu\'au ' . date('d/m/Y', strtotime((string) $r['valid_until']));
            }
            if ((string) $r['status'] === 'utilisee') {
                $body .= ' · déjà utilisée';
            }

            $out[] = [
                'kind'   => 'offer',
                'icon'   => 'gift',
                'accent' => 'purple',
                'title'  => 'Offre disponible : ' . $r['offer_title'],
                'body'   => $body,
                'date'   => (string) $r['created_at'],
                'url'    => '/mon-compte/offres',
            ];
        }

        return $out;
    }

    /** @return list<array<string,mixed>> */
    private function lottery(int $userId): array
    {
        $out = [];
        foreach (LotteryEntry::forUser($userId) as $e) {
            $out[] = [
                'kind'   => 'lottery',
                'icon'   => 'ticket',
                'accent' => 'amber',
                'title'  => 'Participation à la loterie : ' . $e['lottery_title'],
                'body'   => 'Votre ticket : ' . $e['code'],
                'date'   => (string) $e['created_at'],
                'url'    => '/mon-compte/loterie',
            ];

            if ((int) ($e['winner_user_id'] ?? 0) === $userId) {
                $out[] = [
                    'kind'   => 'lottery_win',
                    'icon'   => 'trophy',
                    'accent' => 'amber',
                    'title'  => '🎉 Vous avez gagné : ' . $e['prize'],
                    'body'   => 'Loterie « ' . $e['lottery_title'] . ' ». Rendez-vous en boutique pour récupérer votre lot.',
                    'date'   => (string) ($e['drawn_at'] ?: $e['created_at']),
                    'url'    => '/mon-compte/loterie',
                ];
            }
        }

        return $out;
    }

    /** @return list<array<string,mixed>> */
    private function polls(int $userId): array
    {
        $out = [];

        foreach (PollVote::forUserWithPoll($userId) as $v) {
            $out[] = [
                'kind'   => 'poll',
                'icon'   => 'poll',
                'accent' => 'blue',
                'title'  => 'Réponse enregistrée à un sondage',
                'body'   => '« ' . $v['question'] .' » · votre choix : ' . $v['option_label'],
                'date'   => (string) $v['created_at'],
                'url'    => '/mon-compte/sondages',
            ];
        }

        foreach (Poll::openNotVotedBy($userId) as $p) {
            $out[] = [
                'kind'   => 'poll_open',
                'icon'   => 'poll',
                'accent' => 'brand',
                'title'  => 'Nouveau sondage disponible',
                'body'   => '« ' . $p['question'] . ' » · donnez votre avis avant le ' . date('d/m/Y', strtotime((string) $p['ends_at'])),
                'date'   => (string) $p['created_at'],
                'url'    => '/mon-compte/sondages/' . (int) $p['id'],
            ];
        }

        return $out;
    }

    /** @return list<array<string,mixed>> */
    private function referrals(int $userId): array
    {
        $out = [];
        foreach (User::referralsWithDetails($userId) as $r) {
            $name = trim($r['first_name'] . ' ' . mb_substr((string) $r['last_name'], 0, 1) . '.');
            $out[] = [
                'kind'   => 'referral',
                'icon'   => 'users',
                'accent' => 'emerald',
                'title'  => 'Nouveau filleul parrainé',
                'body'   => $name . ' a rejoint Le Commerce grâce à votre code.',
                'date'   => (string) $r['created_at'],
                'url'    => '/mon-compte/parrainage',
            ];
        }

        return $out;
    }

    private static function excerpt(string $text, int $max = 160): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        return mb_strlen($text) > $max ? mb_substr($text, 0, $max - 1) . '…' : $text;
    }
}
