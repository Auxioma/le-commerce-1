<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\ClientLabel;
use App\Models\ClientNote;
use App\Models\ContactMessage;
use App\Models\Reservation;
use App\Models\ScheduledMessage;
use App\Models\SmsMessage;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WhatsappMessage;

/**
 * Boîte de réception admin façon CRM : regroupe les messages du formulaire
 * de contact (e-mail), les SMS et les échanges WhatsApp avec les clients
 * inscrits dans une seule interface de messagerie omnicanale, avec
 * étiquettes clients, notes internes et messages programmés.
 */
class AdminMessageController extends Controller
{
    private const TABS = ['inbox', 'sent', 'scheduled', 'drafts', 'archive', 'spam'];

    public function index(): void
    {
        Middleware::requireRole('admin');

        $tab     = in_array($this->input('tab', 'inbox'), self::TABS, true) ? $this->input('tab', 'inbox') : 'inbox';
        $channel = (string) $this->input('channel', 'tous');
        $label   = (string) $this->input('label', 'tous');
        $q       = trim((string) $this->input('q', ''));

        $contacts    = ContactMessage::allOrdered();
        $whatsThreads = WhatsappMessage::threads();
        $smsThreads   = SmsMessage::threads();

        $userIds = array_merge(array_column($whatsThreads, 'user_id'), array_column($smsThreads, 'user_id'));
        $labelsByUser = ClientLabel::forUsers($userIds);

        // Fil unifié (tri chrono, tous canaux confondus) pour la colonne de gauche
        $inbox = [];
        foreach ($contacts as $c) {
            $inbox[] = [
                'type'    => 'contact',
                'channel' => 'email',
                'id'      => (int) $c['id'],
                'userId'  => null,
                'name'    => $c['name'],
                'preview' => $c['subject'] !== '' ? $c['subject'] : $c['message'],
                'time'    => $c['created_at'],
                'unread'  => !$c['is_read'],
                'labels'  => [],
            ];
        }
        foreach ($whatsThreads as $t) {
            $inbox[] = [
                'type'    => 'whatsapp',
                'channel' => 'whatsapp',
                'id'      => (int) $t['user_id'],
                'userId'  => (int) $t['user_id'],
                'name'    => trim($t['first_name'] . ' ' . $t['last_name']),
                'preview' => $t['last_content'],
                'time'    => $t['last_sent_at'],
                'unread'  => false,
                'labels'  => $labelsByUser[(int) $t['user_id']] ?? [],
            ];
        }
        foreach ($smsThreads as $t) {
            $inbox[] = [
                'type'    => 'sms',
                'channel' => 'sms',
                'id'      => (int) $t['user_id'],
                'userId'  => (int) $t['user_id'],
                'name'    => trim($t['first_name'] . ' ' . $t['last_name']),
                'preview' => $t['last_content'],
                'time'    => $t['last_sent_at'],
                'unread'  => false,
                'labels'  => $labelsByUser[(int) $t['user_id']] ?? [],
            ];
        }
        usort($inbox, fn($a, $b) => strtotime($b['time']) <=> strtotime($a['time']));

        // --- Filtres (canal, étiquette, recherche) ---
        if ($channel !== 'tous') {
            $inbox = array_values(array_filter($inbox, fn($i) => $i['channel'] === $channel));
        }
        if ($label !== 'tous') {
            $inbox = array_values(array_filter($inbox, fn($i) => in_array($label, array_column($i['labels'], 'label'), true)));
        }
        if ($q !== '') {
            $needle = mb_strtolower($q);
            $inbox = array_values(array_filter($inbox, fn($i) => str_contains(mb_strtolower($i['name']), $needle) || str_contains(mb_strtolower($i['preview']), $needle)));
        }

        // --- Sélection de la conversation affichée (uniquement pertinent pour l'onglet Boîte de réception) ---
        $type = (string) $this->input('type', '');
        $selectedContact = null;
        $selectedUser    = null;
        $selectedThread  = null;
        $selectedChannel = null;
        $selectedWallet  = null;
        $clientLabels    = [];
        $clientNotes     = [];
        $engagement      = null;

        if ($tab === 'inbox') {
            if ($type === 'contact' && $this->input('id')) {
                $selectedContact = ContactMessage::find((int) $this->input('id'));
                if ($selectedContact && !$selectedContact['is_read']) {
                    ContactMessage::markRead((int) $selectedContact['id']);
                    $selectedContact['is_read'] = 1;
                }
            } elseif (($type === 'whatsapp' || $type === 'sms') && $this->input('user')) {
                $userId = (int) $this->input('user');
                $selectedUser = User::find($userId);
                if ($selectedUser) {
                    $selectedChannel = $type;
                    $selectedThread  = $type === 'whatsapp'
                        ? WhatsappMessage::forUserChronological($userId)
                        : SmsMessage::forUserChronological($userId);
                    $selectedWallet = Wallet::findByUser($userId);
                }
            } elseif (!empty($inbox)) {
                // Par défaut : la conversation la plus récente
                $first = $inbox[0];
                if ($first['type'] === 'contact') {
                    $selectedContact = ContactMessage::find($first['id']);
                    if ($selectedContact && !$selectedContact['is_read']) {
                        ContactMessage::markRead((int) $selectedContact['id']);
                        $selectedContact['is_read'] = 1;
                    }
                } else {
                    $selectedUser = User::find($first['id']);
                    if ($selectedUser) {
                        $selectedChannel = $first['type'];
                        $selectedThread  = $first['type'] === 'whatsapp'
                            ? WhatsappMessage::forUserChronological($first['id'])
                            : SmsMessage::forUserChronological($first['id']);
                        $selectedWallet = Wallet::findByUser($first['id']);
                    }
                }
            }

            if ($selectedUser) {
                $uid = (int) $selectedUser['id'];
                $clientLabels = ClientLabel::forUser($uid);
                $clientNotes  = ClientNote::forUser($uid);
                $engagement   = $this->buildEngagement($uid);
            }
        }

        $this->view('admin/messages/index', [
            'title'      => 'Messages — Administration Le Commerce',
            'pageTitle'  => 'Messages',
            'pageSubtitle' => 'Communiquez avec vos clients par e-mail, SMS, WhatsApp et plus encore.',

            'tab'     => $tab,
            'channel' => $channel,
            'label'   => $label,
            'q'       => $q,

            'inbox'            => $inbox,
            'allLabels'        => ClientLabel::allDistinctLabels(),
            'allClients'       => User::activeClientsForSelect(),

            'unreadContacts'   => ContactMessage::countUnread(),
            'totalContacts'    => ContactMessage::count(),
            'totalWhatsapp'    => WhatsappMessage::count(),
            'whatsappIncoming' => WhatsappMessage::countByDirection('entrant'),
            'totalSms'         => SmsMessage::count(),
            'smsIncoming'      => SmsMessage::countByDirection('entrant'),
            'notificationsCount' => ContactMessage::countUnread() + Reservation::countByStatus('en_attente'),
            'scheduledCount'   => ScheduledMessage::countPending(),

            'selectedContact' => $selectedContact,
            'selectedUser'    => $selectedUser,
            'selectedThread'  => $selectedThread,
            'selectedChannel' => $selectedChannel,
            'selectedWallet'  => $selectedWallet,
            'clientLabels'    => $clientLabels,
            'clientNotes'     => $clientNotes,
            'engagement'      => $engagement,

            'sentMessages'      => $tab === 'sent' ? $this->sentMessages() : [],
            'scheduledMessages' => $tab === 'scheduled' ? ScheduledMessage::upcomingWithUser() : [],
        ], 'admin');
    }

    /**
     * Envoie (journalise, voir Lot 9 pour l'intégration API réelle) un
     * nouveau message WhatsApp sortant dans le fil d'un client.
     */
    public function sendWhatsapp(int $userId): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $client = User::find($userId);
        if (!$client || $client['role'] !== 'client') {
            $this->setFlash('error', 'Client introuvable.');
            $this->redirect('/admin/messages');
            return;
        }

        if ($this->trySchedule($userId, 'whatsapp')) {
            $this->redirect('/admin/messages?type=whatsapp&user=' . $userId);
            return;
        }

        $content = trim((string) $this->input('content', ''));
        if ($content !== '') {
            WhatsappMessage::create([
                'user_id'   => $userId,
                'direction' => 'sortant',
                'content'   => $content,
            ]);
        }

        $this->redirect('/admin/messages?type=whatsapp&user=' . $userId);
    }

    /**
     * Envoie (journalise) un nouveau SMS sortant dans le fil d'un client.
     */
    public function sendSms(int $userId): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $client = User::find($userId);
        if (!$client || $client['role'] !== 'client') {
            $this->setFlash('error', 'Client introuvable.');
            $this->redirect('/admin/messages');
            return;
        }

        if ($this->trySchedule($userId, 'sms')) {
            $this->redirect('/admin/messages?type=sms&user=' . $userId);
            return;
        }

        $content = trim((string) $this->input('content', ''));
        if ($content !== '') {
            SmsMessage::create([
                'user_id'   => $userId,
                'direction' => 'sortant',
                'content'   => $content,
            ]);
        }

        $this->redirect('/admin/messages?type=sms&user=' . $userId);
    }

    /**
     * Bouton "Nouveau message" : démarre (ou reprend) une conversation
     * WhatsApp/SMS avec un client choisi dans la liste. Le canal e-mail
     * ouvre directement le client mail (mailto), sans passer par ici.
     */
    public function newConversation(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $userId  = (int) $this->input('user_id', 0);
        $channel = (string) $this->input('channel', 'whatsapp');
        $content = trim((string) $this->input('content', ''));

        $client = $userId ? User::find($userId) : null;
        if (!$client || $client['role'] !== 'client') {
            $this->setFlash('error', 'Merci de choisir un client valide.');
            $this->redirect('/admin/messages');
            return;
        }

        if ($content !== '') {
            if ($channel === 'sms') {
                SmsMessage::create(['user_id' => $userId, 'direction' => 'sortant', 'content' => $content]);
            } else {
                WhatsappMessage::create(['user_id' => $userId, 'direction' => 'sortant', 'content' => $content]);
            }
        }

        $channel = $channel === 'sms' ? 'sms' : 'whatsapp';
        $this->redirect('/admin/messages?type=' . $channel . '&user=' . $userId);
    }

    /**
     * Annule un message programmé (ne l'envoie pas).
     */
    public function cancelScheduled(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        ScheduledMessage::cancel($id);
        $this->setFlash('success', 'Message programmé annulé.');
        $this->redirect('/admin/messages?tab=scheduled');
    }

    /**
     * Ajoute une étiquette libre sur un client (ex: "Client fidèle").
     */
    public function addLabel(int $userId): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $label = trim((string) $this->input('label', ''));
        $color = in_array($this->input('color', 'gray'), ClientLabel::COLORS, true) ? $this->input('color', 'gray') : 'gray';

        if ($label !== '' && !ClientLabel::existsForUser($userId, $label)) {
            ClientLabel::create(['user_id' => $userId, 'label' => $label, 'color' => $color]);
        }

        $this->redirect('/admin/messages?type=' . (string) $this->input('back_channel', 'whatsapp') . '&user=' . $userId);
    }

    /**
     * Retire une étiquette client.
     */
    public function removeLabel(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $userId = (int) $this->input('user_id', 0);
        ClientLabel::delete($id);

        $this->redirect('/admin/messages?type=' . (string) $this->input('back_channel', 'whatsapp') . '&user=' . $userId);
    }

    /**
     * Ajoute une note interne (visible uniquement par l'équipe) sur un client.
     */
    public function addNote(int $userId): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $content = trim((string) $this->input('content', ''));
        if ($content !== '') {
            ClientNote::create(['user_id' => $userId, 'content' => $content]);
        }

        $this->redirect('/admin/messages?type=' . (string) $this->input('back_channel', 'whatsapp') . '&user=' . $userId);
    }

    /**
     * Bascule le statut lu/non lu d'un message de contact.
     */
    public function toggleContactRead(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $message = ContactMessage::find($id);
        if ($message) {
            ContactMessage::markRead($id, !$message['is_read']);
        }

        $this->redirect('/admin/messages?type=contact&id=' . $id);
    }

    /**
     * Si le formulaire d'envoi contient une date de programmation, enregistre
     * le message dans scheduled_messages au lieu de l'envoyer immédiatement.
     */
    private function trySchedule(int $userId, string $channel): bool
    {
        $scheduledAt = trim((string) $this->input('scheduled_at', ''));
        if ($scheduledAt === '') {
            return false;
        }

        $content = trim((string) $this->input('content', ''));
        if ($content === '') {
            return true;
        }

        ScheduledMessage::create([
            'user_id'      => $userId,
            'channel'      => $channel,
            'content'      => $content,
            'scheduled_at' => str_replace('T', ' ', $scheduledAt),
        ]);
        $this->setFlash('success', 'Message programmé avec succès.');

        return true;
    }

    /**
     * Historique unifié des messages sortants (WhatsApp + SMS), pour l'onglet "Envoyés".
     */
    private function sentMessages(): array
    {
        $rows = [];
        foreach (WhatsappMessage::threads() as $t) {
            foreach (WhatsappMessage::forUserChronological((int) $t['user_id']) as $m) {
                if ($m['direction'] === 'sortant') {
                    $rows[] = [
                        'channel' => 'whatsapp',
                        'userId'  => (int) $t['user_id'],
                        'name'    => trim($t['first_name'] . ' ' . $t['last_name']),
                        'content' => $m['content'],
                        'time'    => $m['sent_at'],
                    ];
                }
            }
        }
        foreach (SmsMessage::threads() as $t) {
            foreach (SmsMessage::forUserChronological((int) $t['user_id']) as $m) {
                if ($m['direction'] === 'sortant') {
                    $rows[] = [
                        'channel' => 'sms',
                        'userId'  => (int) $t['user_id'],
                        'name'    => trim($t['first_name'] . ' ' . $t['last_name']),
                        'content' => $m['content'],
                        'time'    => $m['sent_at'],
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
    private function buildEngagement(int $userId): array
    {
        $whats = WhatsappMessage::forUserChronological($userId);
        $sms   = SmsMessage::forUserChronological($userId);
        $all   = array_merge(
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
            'total'            => $total,
            'responseRate'     => (int) round(($outbound / $total) * 100),
            'lastContact'      => $all[0]['time'],
            'preferredChannel' => $byChannel[$preferred] > 0 ? $preferred : null,
        ];
    }
}
