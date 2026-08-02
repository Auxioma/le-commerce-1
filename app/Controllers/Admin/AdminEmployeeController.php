<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\Employee;

class AdminEmployeeController extends Controller
{
    public function index(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/employees/index', [
            'title'     => 'Employés — Administration Le Commerce',
            'pageTitle' => 'Employés',
            'employees'    => Employee::allOrdered(),
            'activeCount'  => Employee::countActive(),
            'totalCount'   => Employee::count(),
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

        Employee::create($data);
        $this->setFlash('success', 'Employé ajouté.');
        $this->redirect('/admin/employes');
    }

    public function update(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        if (!Employee::find($id)) {
            $this->setFlash('error', 'Employé introuvable.');
            $this->redirect('/admin/employes');
            return;
        }

        $data = $this->collectInput();
        if ($data === null) {
            return;
        }

        Employee::update($id, $data);
        $this->setFlash('success', 'Fiche employé mise à jour.');
        $this->redirect('/admin/employes');
    }

    public function toggleStatus(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $employee = Employee::find($id);
        if ($employee) {
            Employee::update($id, ['status' => $employee['status'] === 'actif' ? 'inactif' : 'actif']);
        }

        $this->redirect('/admin/employes');
    }

    public function destroy(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        Employee::delete($id);
        $this->setFlash('success', 'Employé supprimé.');
        $this->redirect('/admin/employes');
    }

    /**
     * Valide et normalise les champs communs à l'ajout et la modification.
     * Redirige et renvoie null en cas d'erreur de validation.
     */
    private function collectInput(): ?array
    {
        $firstName = trim((string) $this->input('first_name', ''));
        $lastName  = trim((string) $this->input('last_name', ''));
        $role      = trim((string) $this->input('role', ''));
        $email     = trim((string) $this->input('email', ''));
        $phone     = trim((string) $this->input('phone', ''));
        $status    = (string) $this->input('status', 'actif');
        $hiredAt   = trim((string) $this->input('hired_at', ''));

        if ($firstName === '' || $lastName === '' || $role === '') {
            $this->setFlash('error', 'Le prénom, le nom et le poste sont obligatoires.');
            $this->redirect('/admin/employes');
            return null;
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'Adresse e-mail invalide.');
            $this->redirect('/admin/employes');
            return null;
        }

        return [
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'role'       => $role,
            'email'      => $email !== '' ? $email : null,
            'phone'      => $phone !== '' ? $phone : null,
            'status'     => in_array($status, ['actif', 'inactif'], true) ? $status : 'actif',
            'hired_at'   => $hiredAt !== '' ? $hiredAt : null,
        ];
    }
}
