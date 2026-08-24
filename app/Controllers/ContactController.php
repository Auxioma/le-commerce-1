<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ContactMessage;

class ContactController extends Controller
{
    public function index(): void
    {
        // Horodatage posé à l'affichage du formulaire : un envoi trop rapide
        // (avant ce délai) trahit un bot qui remplit et poste sans délai humain.
        $_SESSION['_contact_form_ts'] = time();

        $this->view('pages/contact', [
            'title'   => 'Contact — Le Commerce',
            'description' => 'Contactez Le Commerce à Forges-les-Eaux : adresse, téléphone, horaires d\'ouverture et formulaire de contact en ligne.',
            'heading' => 'Contactez-nous',
        ]);
    }

    public function send(): void
    {
        $this->verifyCsrf();

        // Honeypot : champ invisible pour un humain, que les bots remplissent
        // généralement automatiquement. On répond "succès" sans rien enregistrer,
        // pour ne pas révéler au bot que sa soumission a été filtrée.
        if (trim((string) $this->input('website', '')) !== '') {
            $this->json(['success' => true, 'message' => 'Votre message a bien été envoyé.']);
        }

        // Délai minimum entre l'affichage du formulaire et l'envoi : un humain
        // met toujours plus de quelques secondes à le remplir.
        $formShownAt = (int) ($_SESSION['_contact_form_ts'] ?? 0);
        if ($formShownAt === 0 || (time() - $formShownAt) < 3) {
            $this->json(['success' => false, 'error' => 'Veuillez réessayer dans quelques instants.'], 422);
        }

        $name    = trim((string) $this->input('name', ''));
        $email   = trim((string) $this->input('email', ''));
        $message = trim((string) $this->input('message', ''));

        if ($name === '' || $email === '' || $message === '') {
            $this->json(['success' => false, 'error' => 'Tous les champs sont obligatoires.'], 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'error' => 'Adresse e-mail invalide.'], 422);
        }

        // Limite de fréquence par IP : au plus 3 messages toutes les 10 minutes,
        // pour freiner les envois automatisés répétés depuis une même source.
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        if ($ip !== '' && ContactMessage::countRecentByIp($ip, 10) >= 3) {
            $this->json(['success' => false, 'error' => 'Trop de messages envoyés récemment. Merci de réessayer plus tard.'], 429);
        }

        ContactMessage::create([
            'name'    => $name,
            'email'   => $email,
            'subject' => (string) $this->input('subject', ''),
            'message' => $message,
            'ip'      => $ip !== '' ? $ip : null,
        ]);

        unset($_SESSION['_contact_form_ts']);

        $this->json(['success' => true, 'message' => 'Votre message a bien été envoyé.']);
    }
}
