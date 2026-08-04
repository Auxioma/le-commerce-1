<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Reservation;

class ReservationController extends Controller
{
    public function index(): void
    {
        $this->view('pages/reservation', [
            'title'   => 'Réserver une table — Le Commerce',
            'heading' => 'Réserver une table',
            'errors'  => [],
            'old'     => [],
        ]);
    }

    public function store(): void
    {
        $this->verifyCsrf();

        $name      = trim((string) $this->input('name', ''));
        $phone     = trim((string) $this->input('phone', ''));
        $email     = trim((string) $this->input('email', ''));
        $partySize = (int) $this->input('party_size', 2);
        $date      = trim((string) $this->input('reservation_date', ''));
        $time      = trim((string) $this->input('reservation_time', ''));
        $note      = trim((string) $this->input('note', ''));

        $errors = [];

        if ($name === '' || mb_strlen($name) > 120) {
            $errors['name'] = 'Le nom est obligatoire.';
        }
        if (!preg_match('/^0[1-9]\d{8}$/', preg_replace('/\s+/', '', $phone))) {
            $errors['phone'] = 'Numéro de téléphone invalide (format attendu : 06 12 34 56 78).';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Adresse e-mail invalide.';
        }
        if ($partySize < 1 || $partySize > 30) {
            $errors['party_size'] = 'Nombre de personnes invalide.';
        }
        $today = date('Y-m-d');
        if ($date === '' || $date < $today) {
            $errors['reservation_date'] = 'Merci de choisir une date à partir d\'aujourd\'hui.';
        }
        if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
            $errors['reservation_time'] = 'Merci de choisir une heure.';
        }

        if ($errors) {
            $this->view('pages/reservation', [
                'title'   => 'Réserver une table — Le Commerce',
                'heading' => 'Réserver une table',
                'errors'  => $errors,
                'old'     => compact('name', 'phone', 'email', 'partySize', 'date', 'time', 'note'),
            ]);
            return;
        }

        Reservation::create([
            'name'             => $name,
            'phone'            => preg_replace('/\s+/', '', $phone),
            'email'            => $email !== '' ? $email : null,
            'party_size'       => $partySize,
            'reservation_date' => $date,
            'reservation_time' => $time,
            'note'             => $note !== '' ? $note : null,
        ]);

        $this->setFlash('success', 'Votre demande de réservation a bien été envoyée ! Nous vous confirmerons rapidement par téléphone.');
        $this->redirect('/reservation');
    }
}
