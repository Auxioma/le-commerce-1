<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\User;
use App\Models\LoginLog;

class LoginController extends Controller
{
    public function index(): void
    {
        Middleware::requireGuest('/mon-compte');

        $this->view('auth/login', [
            'title' => 'Connexion — Le Commerce',
            'error' => null,
            'old'   => [],
        ], 'auth');
    }

    public function store(): void
    {
        Middleware::requireGuest('/mon-compte');
        $this->verifyCsrf();

        $phone    = User::normalizePhone((string) $this->input('phone_whatsapp', ''));
        $password = (string) $this->input('password', '');

        $user = User::findByPhone($phone);

        if (!$user || $user['role'] !== 'client' || !password_verify($password, (string) $user['password_hash'])) {
            $this->view('auth/login', [
                'title' => 'Connexion — Le Commerce',
                'error' => 'Numéro WhatsApp ou mot de passe incorrect.',
                'old'   => ['phone' => $phone],
            ], 'auth');
            return;
        }

        if ($user['status'] === 'inactif') {
            $this->view('auth/login', [
                'title' => 'Connexion — Le Commerce',
                'error' => 'Ce compte est désactivé. Contactez-nous pour plus d\'informations.',
                'old'   => ['phone' => $phone],
            ], 'auth');
            return;
        }

        Middleware::login($user);
        LoginLog::record((int) $user['id']);

        $intended = $_SESSION['intended_url'] ?? '/mon-compte';
        unset($_SESSION['intended_url']);

        $this->setFlash('success', 'Heureux de vous revoir, ' . $user['first_name'] . ' !');
        $this->redirect($intended);
    }
}
