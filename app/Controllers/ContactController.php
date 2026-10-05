<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ContactMessage;
use App\Service\ContactSpamFilter;

class ContactController extends Controller
{
    /** Sujets proposés dans le <select> : tout autre valeur trahit un envoi forgé. */
    private const SUBJECTS = ['Renseignement général', 'Réservation', 'Suggestion', 'Autre'];

    public function index(): void
    {
        // Horodatage posé à l'affichage du formulaire : un envoi trop rapide
        // (avant ce délai) trahit un bot qui remplit et poste sans délai humain.
        $_SESSION['_contact_form_ts'] = time();

        // Jeton que le JS de la page renvoie inversé, seulement après une
        // interaction réelle (clavier/souris/tactile) : bloque les bots sans JS
        // et ceux qui postent directement sans charger ni utiliser la page.
        $_SESSION['_contact_challenge'] = bin2hex(random_bytes(8));

        $this->view('pages/contact', [
            'title'   => 'Contact — Le Commerce',
            'description' => 'Contactez Le Commerce à Forges-les-Eaux : adresse, téléphone, horaires d\'ouverture et formulaire de contact en ligne.',
            'heading' => 'Contactez-nous',
            'contact_challenge' => $_SESSION['_contact_challenge'],
        ]);
    }

    public function send(): void
    {
        $this->verifyCsrf();

        // Honeypot : champ invisible pour un humain, que les bots remplissent
        // généralement automatiquement. On répond "succès" sans rien enregistrer,
        // pour ne pas révéler au bot que sa soumission a été filtrée.
        if (trim((string) $this->input('website', '')) !== '') {
            $this->rejectSilently('honeypot');
        }

        // Délai minimum entre l'affichage du formulaire et l'envoi : un humain
        // met toujours plus de quelques secondes à le remplir.
        $formShownAt = (int) ($_SESSION['_contact_form_ts'] ?? 0);
        if ($formShownAt === 0 || (time() - $formShownAt) < 5) {
            $this->json(['success' => false, 'error' => 'Veuillez réessayer dans quelques instants.'], 422);
        }

        // Preuve JS + interaction humaine (cf. index()).
        $challenge = (string) ($_SESSION['_contact_challenge'] ?? '');
        if ($challenge === '' || !hash_equals(strrev($challenge), (string) $this->input('js_check', ''))) {
            $this->rejectSilently('preuve JS absente');
        }

        $name    = trim((string) $this->input('name', ''));
        $email   = trim((string) $this->input('email', ''));
        $subject = (string) $this->input('subject', '');
        $message = trim((string) $this->input('message', ''));

        if ($name === '' || $email === '' || $message === '') {
            $this->json(['success' => false, 'error' => 'Tous les champs sont obligatoires.'], 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'error' => 'Adresse e-mail invalide.'], 422);
        }

        if (!in_array($subject, self::SUBJECTS, true)) {
            $this->rejectSilently('sujet inconnu');
        }

        if (mb_strlen($name) > 80 || mb_strlen($message) > 5000) {
            $this->json(['success' => false, 'error' => 'Votre message est trop long.'], 422);
        }

        $spamReason = ContactSpamFilter::check($name, $email, $message);
        if ($spamReason !== null) {
            $this->rejectSilently($spamReason, $email);
        }

        // Limite de fréquence par IP : au plus 3 messages toutes les 10 minutes
        // et 5 par jour ; et au plus 2 messages par heure pour une même adresse.
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        if (($ip !== '' && (ContactMessage::countRecentByIp($ip, 10) >= 3 || ContactMessage::countRecentByIp($ip, 1440) >= 5))
            || ContactMessage::countRecentByEmail($email, 60) >= 2) {
            $this->json(['success' => false, 'error' => 'Trop de messages envoyés récemment. Merci de réessayer plus tard.'], 429);
        }

        ContactMessage::create([
            'name'    => $name,
            'email'   => $email,
            'subject' => $subject,
            'message' => $message,
            'ip'      => $ip !== '' ? $ip : null,
        ]);

        unset($_SESSION['_contact_form_ts']);

        $this->json(['success' => true, 'message' => 'Votre message a bien été envoyé.']);
    }

    /**
     * Répond "succès" au bot sans rien enregistrer (pour qu'il ne s'adapte pas),
     * et trace le rejet dans le log PHP pour pouvoir repérer d'éventuels faux positifs.
     */
    private function rejectSilently(string $reason, string $email = ''): void
    {
        error_log('[ContactController] Message filtré (' . $reason . ') ip=' . ($_SERVER['REMOTE_ADDR'] ?? '?') . ($email !== '' ? ' email=' . $email : ''));
        $this->json(['success' => true, 'message' => 'Votre message a bien été envoyé.']);
    }
}
