<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\User;
use App\Models\LoginLog;

class AdminLoginController extends Controller
{
    public function index(): void
    {
        if (Middleware::isLoggedIn() && Middleware::role() === 'admin') {
            $this->redirect('/admin');
            return;
        }

        $this->view('auth/admin-login', [
            'title' => 'Espace Administrateur — Le Commerce',
            'error' => null,
            'old'   => [],
        ], 'auth');
    }

    public function store(): void
    {
        $this->verifyCsrf();

        $email    = trim((string) $this->input('email', ''));
        $password = (string) $this->input('password', '');

        $stmt = null;
        $user = User::where('email', $email)[0] ?? null;

        if (!$user || !in_array($user['role'], ['admin', 'employe'], true) || !password_verify($password, (string) $user['password_hash'])) {
            $this->view('auth/admin-login', [
                'title' => 'Espace Administrateur — Le Commerce',
                'error' => 'Identifiants incorrects.',
                'old'   => ['email' => $email],
            ], 'auth');
            return;
        }

        Middleware::login($user);
        LoginLog::record((int) $user['id']);

        $this->redirect('/admin');
    }
}
