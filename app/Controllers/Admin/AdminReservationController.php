<?php

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
}
