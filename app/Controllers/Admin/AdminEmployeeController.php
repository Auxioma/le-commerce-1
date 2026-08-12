<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\Employee;
use App\Models\User;

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

        $userId = $this->syncBackofficeAccess(null, $data);
        if ($userId === false) {
            return;
        }
        $data['user_id'] = $userId;

        Employee::create($data);
        $this->setFlash('success', 'Employé ajouté.');
        $this->redirect('/admin/employes');
    }

    public function update(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $employee = Employee::find($id);
        if (!$employee) {
            $this->setFlash('error', 'Employé introuvable.');
            $this->redirect('/admin/employes');
            return;
        }

        $data = $this->collectInput();
        if ($data === null) {
            return;
        }

        $userId = $this->syncBackofficeAccess($employee, $data);
        if ($userId === false) {
            return;
        }
        $data['user_id'] = $userId;

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

        $employee = Employee::find($id);
        if ($employee && $employee['user_id']) {
            // On désactive le compte de connexion plutôt que de le supprimer,
            // pour conserver l'historique (journal de connexions, etc.).
            User::update((int) $employee['user_id'], ['status' => 'inactif']);
        }

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

    /**
     * Crée, met à jour ou désactive le compte `users` (rôle 'employe') lié
     * à cette fiche employé, selon la case "accès back-office" du formulaire.
     * Redirige et renvoie false en cas d'erreur de validation ; renvoie
     * l'id du compte lié (ou null si aucun accès) sinon.
     *
     * @return int|null|false
     */
    private function syncBackofficeAccess(?array $employee, array $data)
    {
        $grantAccess   = (bool) $this->input('grant_access');
        $password      = (string) $this->input('password', '');
        $existingUserId = $employee['user_id'] ?? null;

        if (!$grantAccess) {
            if ($existingUserId) {
                User::update((int) $existingUserId, ['status' => 'inactif']);
            }
            return $existingUserId;
        }

        if ($data['email'] === null) {
            $this->setFlash('error', "Une adresse e-mail est nécessaire pour donner un accès back-office.");
            $this->redirect('/admin/employes');
            return false;
        }

        if ($existingUserId) {
            $userData = [
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'email'      => $data['email'],
                'role'       => 'employe',
                'status'     => 'actif',
            ];
            if ($password !== '') {
                $userData['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }
            User::update((int) $existingUserId, $userData);
            return $existingUserId;
        }

        if ($password === '') {
            $this->setFlash('error', 'Un mot de passe est nécessaire pour créer un accès back-office.');
            $this->redirect('/admin/employes');
            return false;
        }
        if ($data['phone'] === null) {
            $this->setFlash('error', 'Un numéro de téléphone est nécessaire pour créer un accès back-office.');
            $this->redirect('/admin/employes');
            return false;
        }

        $phone = User::normalizePhone($data['phone']);
        if (User::phoneExists($phone)) {
            $this->setFlash('error', 'Ce numéro de téléphone est déjà utilisé par un autre compte.');
            $this->redirect('/admin/employes');
            return false;
        }
        if (User::emailExists($data['email'])) {
            $this->setFlash('error', 'Cette adresse e-mail est déjà utilisée par un autre compte.');
            $this->redirect('/admin/employes');
            return false;
        }

        return User::create([
            'first_name'     => $data['first_name'],
            'last_name'      => $data['last_name'],
            'phone_whatsapp' => $phone,
            'email'          => $data['email'],
            'password_hash'  => password_hash($password, PASSWORD_DEFAULT),
            'role'           => 'employe',
            'status'         => 'actif',
        ]);
    }
}
