<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ContactMessage;

class ContactController extends Controller
{
    public function index(): void
    {
        $this->view('pages/contact', [
            'title'   => 'Contact — Le Commerce',
            'description' => 'Contactez Le Commerce à Forges-les-Eaux : adresse, téléphone, horaires d\'ouverture et formulaire de contact en ligne.',
            'heading' => 'Contactez-nous',
        ]);
    }

    public function send(): void
    {
        $this->verifyCsrf();

        $name    = trim((string) $this->input('name', ''));
        $email   = trim((string) $this->input('email', ''));
        $message = trim((string) $this->input('message', ''));

        if ($name === '' || $email === '' || $message === '') {
            $this->json(['success' => false, 'error' => 'Tous les champs sont obligatoires.'], 422);
        }

        ContactMessage::create([
            'name'    => $name,
            'email'   => $email,
            'subject' => (string) $this->input('subject', ''),
            'message' => $message,
        ]);

        $this->json(['success' => true, 'message' => 'Votre message a bien été envoyé.']);
    }
}
