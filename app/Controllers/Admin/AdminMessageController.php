<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\ContactMessage;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WhatsappMessage;

/**
 * Boîte de réception admin : regroupe les messages du formulaire de contact
 * (e-mail, non liés à un compte) et les échanges WhatsApp avec les clients
 * inscrits dans une seule interface, façon messagerie.
 */
class AdminMessageController extends Controller
{
    public function index(): void
    {
        Middleware::requireRole('admin');

        $contacts = ContactMessage::allOrdered();
        $threads  = WhatsappMessage::threads();

        // Fil d'actualité unifié (tri chrono, tous canaux confondus) pour la colonne de gauche
        $inbox = [];
        foreach ($contacts as $c) {
            $inbox[] = [
                'type'    => 'contact',
                'id'      => (int) $c['id'],
                'name'    => $c['name'],
                'preview' => $c['subject'] !== '' ? $c['subject'] : $c['message'],
                'time'    => $c['created_at'],
                'unread'  => !$c['is_read'],
            ];
        }
        foreach ($threads as $t) {
            $inbox[] = [
                'type'    => 'whatsapp',
                'id'      => (int) $t['user_id'],
                'name'    => trim($t['first_name'] . ' ' . $t['last_name']),
                'preview' => $t['last_content'],
                'time'    => $t['last_sent_at'],
                'unread'  => false,
            ];
        }
        usort($inbox, fn($a, $b) => strtotime($b['time']) <=> strtotime($a['time']));

        $type = (string) $this->input('type', '');
        $selectedContact = null;
        $selectedUser    = null;
        $selectedThread  = null;
        $selectedWallet  = null;

        if ($type === 'contact' && $this->input('id')) {
            $selectedContact = ContactMessage::find((int) $this->input('id'));
            if ($selectedContact && !$selectedContact['is_read']) {
                ContactMessage::markRead((int) $selectedContact['id']);
                $selectedContact['is_read'] = 1;
            }
        } elseif ($type === 'whatsapp' && $this->input('user')) {
            $userId = (int) $this->input('user');
            $selectedUser = User::find($userId);
            if ($selectedUser) {
                $selectedThread = WhatsappMessage::forUserChronological($userId);
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
                    $selectedThread = WhatsappMessage::forUserChronological($first['id']);
                    $selectedWallet = Wallet::findByUser($first['id']);
                }
            }
        }

        $this->view('admin/messages/index', [
            'title'           => 'Messages — Administration Le Commerce',
            'pageTitle'       => 'Messages',
            'inbox'           => $inbox,
            'unreadContacts'  => ContactMessage::countUnread(),
            'totalContacts'   => ContactMessage::count(),
            'totalWhatsapp'   => WhatsappMessage::count(),
            'whatsappIncoming'=> WhatsappMessage::countByDirection('entrant'),
            'selectedContact' => $selectedContact,
            'selectedUser'    => $selectedUser,
            'selectedThread'  => $selectedThread,
            'selectedWallet'  => $selectedWallet,
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
}
