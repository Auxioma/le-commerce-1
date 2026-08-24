<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\ClientLabel;
use App\Models\ContactMessage;
use App\Models\SmsMessage;
use App\Models\WhatsappMessage;

/**
 * Agrège les messages de contact (e-mail), WhatsApp et SMS en un fil unique
 * pour la boîte de réception admin, et calcule les statistiques d'engagement
 * par client. Extrait d'AdminMessageController pour que le contrôleur reste
 * un simple chef d'orchestre HTTP.
 */
final class InboxService
{
    /**
     * Fil unifié (tri chrono, tous canaux confondus), filtré par canal/étiquette/recherche.
     */
    public function buildInbox(string $channel, string $label, string $q): array
    {
        $contacts = ContactMessage::allOrdered();
        $whatsThreads = WhatsappMessage::threads();
        $smsThreads = SmsMessage::threads();

        $userIds = array_merge(array_column($whatsThreads, 'user_id'), array_column($smsThreads, 'user_id'));
        $labelsByUser = ClientLabel::forUsers($userIds);

        $inbox = [];
        foreach ($contacts as $c) {
            $inbox[] = [
                'type' => 'contact',
                'channel' => 'email',
                'id' => (int) $c['id'],
                'userId' => null,
                'name' => $c['name'],
                'preview' => $c['subject'] !== '' ? $c['subject'] : $c['message'],
                'time' => $c['created_at'],
                'unread' => !$c['is_read'],
                'labels' => [],
            ];
        }
        foreach ($whatsThreads as $t) {
            $inbox[] = [
                'type' => 'whatsapp',
                'channel' => 'whatsapp',
                'id' => (int) $t['user_id'],
                'userId' => (int) $t['user_id'],
                'name' => trim($t['first_name'] . ' ' . $t['last_name']),
                'preview' => $t['last_content'],
                'time' => $t['last_sent_at'],
                'unread' => false,
                'labels' => $labelsByUser[(int) $t['user_id']] ?? [],
            ];
        }
        foreach ($smsThreads as $t) {
            $inbox[] = [
                'type' => 'sms',
                'channel' => 'sms',
                'id' => (int) $t['user_id'],
                'userId' => (int) $t['user_id'],
                'name' => trim($t['first_name'] . ' ' . $t['last_name']),
                'preview' => $t['last_content'],
                'time' => $t['last_sent_at'],
                'unread' => false,
                'labels' => $labelsByUser[(int) $t['user_id']] ?? [],
            ];
        }
        usort($inbox, fn($a, $b) => strtotime($b['time']) <=> strtotime($a['time']));

        return $this->filter($inbox, $channel, $label, $q);
    }

    private function filter(array $inbox, string $channel, string $label, string $q): array
    {
        if ($channel !== 'tous') {
            $inbox = array_values(array_filter($inbox, fn($i) => $i['channel'] === $channel));
        }
        if ($label !== 'tous') {
            $inbox = array_values(array_filter($inbox, fn($i) => in_array($label, array_column($i['labels'], 'label'), true)));
        }
        if ($q !== '') {
            $needle = mb_strtolower($q);
            $inbox = array_values(array_filter(
                $inbox,
                fn($i) => str_contains(mb_strtolower($i['name']), $needle) || str_contains(mb_strtolower($i['preview']), $needle)
            ));
        }

        return $inbox;
    }

    /**
     * Historique unifié des messages sortants (WhatsApp + SMS), pour l'onglet "Envoyés".
     */
    public function sentMessages(): array
    {
        $rows = [];
        foreach (WhatsappMessage::threads() as $t) {
            foreach (WhatsappMessage::forUserChronological((int) $t['user_id']) as $m) {
                if ($m['direction'] === 'sortant') {
                    $rows[] = [
                        'channel' => 'whatsapp',
                        'userId' => (int) $t['user_id'],
                        'name' => trim($t['first_name'] . ' ' . $t['last_name']),
                        'content' => $m['content'],
                        'time' => $m['sent_at'],
                    ];
                }
            }
        }
        foreach (SmsMessage::threads() as $t) {
            foreach (SmsMessage::forUserChronological((int) $t['user_id']) as $m) {
                if ($m['direction'] === 'sortant') {
                    $rows[] = [
                        'channel' => 'sms',
                        'userId' => (int) $t['user_id'],
                        'name' => trim($t['first_name'] . ' ' . $t['last_name']),
                        'content' => $m['content'],
                        'time' => $m['sent_at'],
                    ];
                }
            }
        }
        usort($rows, fn($a, $b) => strtotime($b['time']) <=> strtotime($a['time']));

        return array_slice($rows, 0, 100);
    }

    /**
     * Statistiques d'engagement affichées dans la colonne "Détails du contact" :
     * volume de messages échangés, taux de réponse, dernier contact, canal préféré.
     */
    public function buildEngagement(int $userId): array
    {
        $whats = WhatsappMessage::forUserChronological($userId);
        $sms = SmsMessage::forUserChronological($userId);
        $all = array_merge(
            array_map(fn($m) => ['direction' => $m['direction'], 'time' => $m['sent_at'], 'channel' => 'whatsapp'], $whats),
            array_map(fn($m) => ['direction' => $m['direction'], 'time' => $m['sent_at'], 'channel' => 'sms'], $sms)
        );

        $total = count($all);
        if ($total === 0) {
            return ['total' => 0, 'responseRate' => 0, 'lastContact' => null, 'preferredChannel' => null];
        }

        $outbound = count(array_filter($all, fn($m) => $m['direction'] === 'sortant'));
        usort($all, fn($a, $b) => strtotime($b['time']) <=> strtotime($a['time']));

        $byChannel = ['whatsapp' => count($whats), 'sms' => count($sms)];
        arsort($byChannel);
        $preferred = array_key_first($byChannel);

        return [
            'total' => $total,
            'responseRate' => (int) round(($outbound / $total) * 100),
            'lastContact' => $all[0]['time'],
            'preferredChannel' => $byChannel[$preferred] > 0 ? $preferred : null,
        ];
    }
}
