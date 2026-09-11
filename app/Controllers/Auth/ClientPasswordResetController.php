<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Middleware;
use App\Core\View;
use App\Models\PasswordReset;
use App\Models\User;
use App\Service\Mailer;

/**
 * Définition / réinitialisation du mot de passe client par e-mail. Sert à
 * la fois pour le lien "bienvenue" envoyé après une création de compte par
 * l'administrateur (voir AdminClientController) et pour un "mot de passe
 * oublié" classique — les deux ne sont qu'un jeton à usage unique menant au
 * même formulaire.
 */
class ClientPasswordResetController extends Controller
{
    private const GENERIC_MESSAGE = 'Si cette adresse est associée à un compte, un e-mail vient de lui être envoyé.';

    public function forgot(): void
    {
        Middleware::requireGuest('/mon-compte');

        $this->view('auth/forgot-password', [
            'title' => 'Mot de passe oublié — Le Commerce',
            'error' => null,
            'sent'  => false,
            'old'   => [],
        ], 'auth');
    }

    public function sendLink(): void
    {
        Middleware::requireGuest('/mon-compte');
        $this->verifyCsrf();

        $email = trim((string) $this->input('email', ''));

        $lastAttemptAt = (int) ($_SESSION['_client_forgot_ts'] ?? 0);
        if ($lastAttemptAt !== 0 && (time() - $lastAttemptAt) < 5) {
            $this->view('auth/forgot-password', [
                'title' => 'Mot de passe oublié — Le Commerce',
                'error' => 'Veuillez patienter quelques instants avant de réessayer.',
                'sent'  => false,
                'old'   => ['email' => $email],
            ], 'auth');
            return;
        }
        $_SESSION['_client_forgot_ts'] = time();

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->view('auth/forgot-password', [
                'title' => 'Mot de passe oublié — Le Commerce',
                'error' => 'Merci de saisir une adresse e-mail valide.',
                'sent'  => false,
                'old'   => ['email' => $email],
            ], 'auth');
            return;
        }

        $user = User::where('email', $email)[0] ?? null;

        // Message volontairement générique, que le compte existe ou non.
        if ($user && $user['role'] === 'client' && $user['status'] === 'actif') {
            $this->dispatchResetEmail($user);
        }

        $this->view('auth/forgot-password', [
            'title' => 'Mot de passe oublié — Le Commerce',
            'error' => null,
            'sent'  => true,
            'old'   => [],
        ], 'auth');
    }

    private function dispatchResetEmail(array $user): void
    {
        $token = PasswordReset::createForUser((int) $user['id']);

        $appUrl   = rtrim((string) ($this->sharedData['app']['url'] ?? ''), '/');
        $resetUrl = $appUrl . '/reinitialiser-mot-de-passe/' . $token;

        $html = View::render('emails/client-password-reset', [
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
        $valid = $reset !== null && $reset['role'] === 'client';

        $this->view('auth/reset-password', [
            'title' => 'Choisir un mot de passe — Le Commerce',
            'token' => $token,
            'valid' => $valid,
            'error' => null,
        ], 'auth');
    }

    public function update(string $token): void
    {
        $this->verifyCsrf();

        $reset = PasswordReset::findValidByToken($token);
        $valid = $reset !== null && $reset['role'] === 'client';

        if (!$valid) {
            $this->view('auth/reset-password', [
                'title' => 'Choisir un mot de passe — Le Commerce',
                'token' => $token,
                'valid' => false,
                'error' => null,
            ], 'auth');
            return;
        }

        $password        = (string) $this->input('password', '');
        $passwordConfirm = (string) $this->input('password_confirmation', '');

        if (strlen($password) < 6) {
            $this->view('auth/reset-password', [
                'title' => 'Choisir un mot de passe — Le Commerce',
                'token' => $token,
                'valid' => true,
                'error' => 'Le mot de passe doit contenir au moins 6 caractères.',
            ], 'auth');
            return;
        }

        if ($password !== $passwordConfirm) {
            $this->view('auth/reset-password', [
                'title' => 'Choisir un mot de passe — Le Commerce',
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

        $user = User::find((int) $reset['user_id']);
        Middleware::login($user);

        $this->setFlash('success', 'Votre mot de passe est bien défini. Vous pouvez maintenant compléter votre fiche.');
        $this->redirect('/mon-compte/informations');
    }
}
