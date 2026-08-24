<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Middleware;
use App\Core\View;
use App\Models\PasswordReset;
use App\Models\User;
use App\Service\Mailer;

class AdminPasswordResetController extends Controller
{
    private const GENERIC_MESSAGE = 'Si cette adresse est associée à un compte administrateur, un e-mail de réinitialisation vient de lui être envoyé.';

    public function forgot(): void
    {
        if (Middleware::isLoggedIn() && Middleware::role() === 'admin') {
            $this->redirect('/admin');
            return;
        }

        $this->view('auth/admin-forgot-password', [
            'title'   => 'Mot de passe oublié — Espace Administrateur',
            'error'   => null,
            'sent'    => false,
            'old'     => [],
        ], 'auth');
    }

    public function sendLink(): void
    {
        $this->verifyCsrf();

        $email = trim((string) $this->input('email', ''));

        // Anti-spam : au plus une tentative toutes les 5 secondes, pour freiner
        // les soumissions automatisées répétées (même logique que le formulaire
        // de contact public).
        $lastAttemptAt = (int) ($_SESSION['_admin_forgot_ts'] ?? 0);
        if ($lastAttemptAt !== 0 && (time() - $lastAttemptAt) < 5) {
            $this->view('auth/admin-forgot-password', [
                'title' => 'Mot de passe oublié — Espace Administrateur',
                'error' => 'Veuillez patienter quelques instants avant de réessayer.',
                'sent'  => false,
                'old'   => ['email' => $email],
            ], 'auth');
            return;
        }
        $_SESSION['_admin_forgot_ts'] = time();

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->view('auth/admin-forgot-password', [
                'title' => 'Mot de passe oublié — Espace Administrateur',
                'error' => 'Merci de saisir une adresse e-mail valide.',
                'sent'  => false,
                'old'   => ['email' => $email],
            ], 'auth');
            return;
        }

        $user = User::where('email', $email)[0] ?? null;

        // Message volontairement générique, que le compte existe ou non, pour
        // ne pas permettre de deviner quelles adresses sont enregistrées.
        if ($user && in_array($user['role'], ['admin', 'employe'], true) && $user['status'] === 'actif') {
            $this->dispatchResetEmail($user);
        }

        $this->view('auth/admin-forgot-password', [
            'title' => 'Mot de passe oublié — Espace Administrateur',
            'error' => null,
            'sent'  => true,
            'old'   => [],
        ], 'auth');
    }

    private function dispatchResetEmail(array $user): void
    {
        $token = PasswordReset::createForUser((int) $user['id']);

        $appUrl   = rtrim((string) ($this->sharedData['app']['url'] ?? ''), '/');
        $resetUrl = $appUrl . '/admin/reinitialiser-mot-de-passe/' . $token;

        $html = View::render('emails/admin-password-reset', [
            'shop'       => $this->sharedData['shop'],
            'firstName'  => $user['first_name'],
            'resetUrl'   => $resetUrl,
            'ttlMinutes' => PasswordReset::TTL_MINUTES,
        ]);

        (new Mailer())->send(
            (string) $user['email'],
            'Réinitialisation de votre mot de passe — ' . $this->sharedData['shop']['name'],
            $html
        );
    }

    public function reset(string $token): void
    {
        $reset = PasswordReset::findValidByToken($token);

        $this->view('auth/admin-reset-password', [
            'title' => 'Nouveau mot de passe — Espace Administrateur',
            'token' => $token,
            'valid' => $reset !== null,
            'error' => null,
        ], 'auth');
    }

    public function update(string $token): void
    {
        $this->verifyCsrf();

        $reset = PasswordReset::findValidByToken($token);

        if (!$reset) {
            $this->view('auth/admin-reset-password', [
                'title' => 'Nouveau mot de passe — Espace Administrateur',
                'token' => $token,
                'valid' => false,
                'error' => null,
            ], 'auth');
            return;
        }

        $password        = (string) $this->input('password', '');
        $passwordConfirm = (string) $this->input('password_confirmation', '');

        if (strlen($password) < 8) {
            $this->view('auth/admin-reset-password', [
                'title' => 'Nouveau mot de passe — Espace Administrateur',
                'token' => $token,
                'valid' => true,
                'error' => 'Le mot de passe doit contenir au moins 8 caractères.',
            ], 'auth');
            return;
        }

        if ($password !== $passwordConfirm) {
            $this->view('auth/admin-reset-password', [
                'title' => 'Nouveau mot de passe — Espace Administrateur',
                'token' => $token,
                'valid' => true,
                'error' => 'Les deux mots de passe ne correspondent pas.',
            ], 'auth');
            return;
        }

        User::update((int) $reset['user_id'], [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        PasswordReset::markUsed((int) $reset['id']);

        $this->setFlash('success', 'Votre mot de passe a bien été réinitialisé. Vous pouvez vous connecter.');
        $this->redirect('/admin/connexion');
    }
}
