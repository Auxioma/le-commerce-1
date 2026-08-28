<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\Reservation;

class AdminReservationController extends Controller
{
    public function index(): void
    {
        Middleware::requireRole('admin');

        $filters = [
            'status' => (string) $this->input('status', 'tous'),
            'date'   => (string) $this->input('date', ''),
        ];

        $this->view('admin/reservations/index', [
            'title'       => 'Réservations — Administration Le Commerce',
            'pageTitle'   => 'Réservations',
            'reservations'=> Reservation::filtered($filters),
            'filters'     => $filters,
            'pendingCount'   => Reservation::countByStatus('en_attente'),
            'confirmedCount' => Reservation::countByStatus('confirmee'),
            'upcomingCount'  => Reservation::countUpcoming(),
        ], 'admin');
    }

    public function store(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $data = $this->collectInput();
        if ($data === null) {
            return;
        }

        Reservation::create($data);
        $this->setFlash('success', 'Réservation ajoutée.');
        $this->redirect('/admin/reservations');
    }

    public function update(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        if (!Reservation::find($id)) {
            $this->setFlash('error', 'Réservation introuvable.');
            $this->redirect('/admin/reservations');
            return;
        }

        $data = $this->collectInput();
        if ($data === null) {
            return;
        }

        Reservation::update($id, $data);
        $this->setFlash('success', 'Réservation mise à jour.');
        $this->redirect('/admin/reservations');
    }

    public function updateStatus(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $status = (string) $this->input('status', '');
        if (Reservation::find($id) && in_array($status, ['en_attente', 'confirmee', 'annulee'], true)) {
            Reservation::update($id, ['status' => $status]);
        }

        $this->redirect('/admin/reservations');
    }

    public function destroy(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        Reservation::delete($id);
        $this->setFlash('success', 'Réservation supprimée.');
        $this->redirect('/admin/reservations');
    }

    /**
     * Valide et normalise les champs communs à l'ajout et la modification.
     * Redirige avec un message d'erreur et renvoie null si la saisie est invalide.
     */
    private function collectInput(): ?array
    {
        $name      = trim((string) $this->input('name', ''));
        $phone     = preg_replace('/\s+/', '', (string) $this->input('phone', ''));
        $email     = trim((string) $this->input('email', ''));
        $partySize = (int) $this->input('party_size', 2);
        $date      = trim((string) $this->input('reservation_date', ''));
        $time      = trim((string) $this->input('reservation_time', ''));
        $note      = trim((string) $this->input('note', ''));
        $status    = (string) $this->input('status', 'en_attente');

        if ($name === '' || mb_strlen($name) > 120) {
            return $this->fail('Le nom est obligatoire (120 caractères maximum).');
        }
        if (!preg_match('/^0[1-9]\d{8}$/', $phone)) {
            return $this->fail('Numéro de téléphone invalide (format attendu : 06 12 34 56 78).');
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->fail('Adresse e-mail invalide.');
        }
        if ($partySize < 1 || $partySize > 30) {
            return $this->fail('Nombre de personnes invalide (entre 1 et 30).');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $this->fail('Merci de choisir une date valide.');
        }
        if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
            return $this->fail('Merci de choisir une heure valide.');
        }

        return [
            'name'             => $name,
            'phone'            => $phone,
            'email'            => $email !== '' ? $email : null,
            'party_size'       => $partySize,
            'reservation_date' => $date,
            'reservation_time' => $time,
            'note'             => $note !== '' ? $note : null,
            'status'           => in_array($status, ['en_attente', 'confirmee', 'annulee'], true) ? $status : 'en_attente',
        ];
    }

    private function fail(string $message): null
    {
        $this->setFlash('error', $message);
        $this->redirect('/admin/reservations');
        return null;
    }
}
