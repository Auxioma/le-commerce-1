<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/**
 * Middleware d'authentification
 * Utilisé en première ligne des méthodes de contrôleur à protéger :
 *
 *   Middleware::requireAuth();
 *   Middleware::requireRole('admin');
 */
class Middleware
{
    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    /**
     * Retourne l'utilisateur courant (tableau associatif) ou null.
     * Mis en cache statique le temps de la requête pour éviter les requêtes SQL répétées.
     */
    public static function user(): ?array
    {
        static $cached = null;
        static $resolved = false;

        if ($resolved) {
            return $cached;
        }
        $resolved = true;

        if (empty($_SESSION['user_id'])) {
            return null;
        }

        $cached = User::find((int) $_SESSION['user_id']);
        return $cached;
    }

    public static function role(): ?string
    {
        return self::user()['role'] ?? null;
    }

    /**
     * Bloque l'accès si l'utilisateur n'est pas connecté.
     */
    public static function requireAuth(string $redirectTo = '/connexion'): void
    {
        if (!self::isLoggedIn()) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? null;
            header('Location: ' . BASE_PATH . $redirectTo);
            exit;
        }
    }

    /**
     * Bloque l'accès si le rôle ne correspond pas (ex: 'admin').
     * Le rôle 'employe' (Lot 19 : comptes employés) a accès aux mêmes
     * écrans que 'admin' — il n'existe pas encore de permissions distinctes
     * par onglet, un employé avec un accès back-office voit tout l'admin.
     */
    public static function requireRole(string $role, string $redirectTo = '/'): void
    {
        self::requireAuth($role === 'admin' ? '/admin/connexion' : '/connexion');

        $allowedRoles = $role === 'admin' ? ['admin', 'employe'] : [$role];
        if (!in_array(self::role(), $allowedRoles, true)) {
            http_response_code(403);
            $redirectTo = $role === 'admin' ? '/admin/connexion' : $redirectTo;
            header('Location: ' . BASE_PATH . $redirectTo);
            exit;
        }
    }

    /**
     * Empêche l'accès à une page (ex: /connexion) si déjà connecté.
     */
    public static function requireGuest(string $redirectTo = '/'): void
    {
        if (self::isLoggedIn()) {
            header('Location: ' . BASE_PATH . $redirectTo);
            exit;
        }
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
    }
}
