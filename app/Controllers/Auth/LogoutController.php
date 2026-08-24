<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Middleware;

class LogoutController extends Controller
{
    public function destroy(): void
    {
        $this->verifyCsrf();

        $wasAdmin = Middleware::role() === 'admin';
        Middleware::logout();

        $this->redirect($wasAdmin ? '/admin/connexion' : '/');
    }
}
