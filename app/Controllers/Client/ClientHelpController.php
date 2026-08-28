<?php

declare(strict_types=1);

namespace App\Controllers\Client;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\ContactMessage;
use App\Models\FaqItem;

/**
 * "Aide & support" de l'espace client : FAQ (100 % en base, table faq_items)
 * regroupée par catégorie + formulaire de demande d'aide dont les messages
 * arrivent dans la boîte de réception admin (table contact_messages, avec
 * user_id renseigné) et dont le client retrouve l'historique ici.
 */
class ClientHelpController extends Controller
{
    /** Sujets proposés dans le formulaire de demande d'aide. */
    private const SUBJECTS = [
        'Mon compte',
        'Portefeuille & paiement',
        'Offres & avantages',
        'Loterie',
        'Sondages',
        'Parrainage',
        'Données personnelles',
        'Autre',
    ];

    public function index(): void
    {
        Middleware::requireRole('client');

        $user = Middleware::user();

        $this->view('client/help', [
            'title'    => 'Aide & support — Le Commerce',
            'user'     => $user,
            'faq'      => FaqItem::groupedByCategory(),
            'subjects' => self::SUBJECTS,
            'requests' => ContactMessage::forUser((int) $user['id']),
            'errors'   => [],
            'old'      => [],
        ], 'client');
    }

    public function sendRequest(): void
    {
        Middleware::requireRole('client');
        $this->verifyCsrf();

        $user = Middleware::user();

        $subject = (string) $this->input('subject', '');
        $details = trim((string) $this->input('details', ''));
        $message = trim((string) $this->input('message', ''));

        $errors = [];
        if (!in_array($subject, self::SUBJECTS, true)) {
            $errors['subject'] = 'Merci de choisir un sujet.';
        }
        if (mb_strlen($message) < 10) {
            $errors['message'] = 'Merci de détailler votre demande (10 caractères minimum).';
        } elseif (mb_strlen($message) > 2000) {
            $errors['message'] = 'Votre message est trop long (2000 caractères maximum).';
        }
        if (mb_strlen($details) > 120) {
            $errors['details'] = 'L\'objet est trop long (120 caractères maximum).';
        }

        // Limite anti-abus : 3 demandes maximum toutes les 10 minutes.
        if (!$errors && ContactMessage::countRecentByUser((int) $user['id'], 10) >= 3) {
            $errors['message'] = 'Vous avez envoyé plusieurs demandes récemment. Merci de patienter avant d\'en envoyer une nouvelle.';
        }

        if ($errors) {
            $this->view('client/help', [
                'title'    => 'Aide & support — Le Commerce',
                'user'     => $user,
                'faq'      => FaqItem::groupedByCategory(),
                'subjects' => self::SUBJECTS,
                'requests' => ContactMessage::forUser((int) $user['id']),
                'errors'   => $errors,
                'old'      => ['subject' => $subject, 'details' => $details, 'message' => $message],
            ], 'client');
            return;
        }

        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

        ContactMessage::create([
            'user_id' => (int) $user['id'],
            'name'    => trim($user['first_name'] . ' ' . $user['last_name']),
            'email'   => (string) ($user['email'] ?? ''),
            'subject' => '[Aide] ' . $subject . ($details !== '' ? ' — ' . $details : ''),
            'message' => $message,
            'ip'      => $ip !== '' ? $ip : null,
        ]);

        $this->setFlash('success', 'Votre demande a bien été envoyée. Notre équipe vous répondra rapidement.');
        $this->redirect('/mon-compte/aide');
    }
}
