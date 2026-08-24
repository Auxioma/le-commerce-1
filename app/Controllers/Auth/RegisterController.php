<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\User;
use App\Models\Wallet;

class RegisterController extends Controller
{
    public function index(): void
    {
        Middleware::requireGuest('/mon-compte');

        $this->view('auth/register', [
            'title' => 'Créer mon compte — Le Commerce',
            'errors' => [],
            'old' => [],
        ], 'auth');
    }

    public function store(): void
    {
        Middleware::requireGuest('/mon-compte');
        $this->verifyCsrf();

        $firstName = trim((string) $this->input('first_name', ''));
        $lastName  = trim((string) $this->input('last_name', ''));
        $phone     = User::normalizePhone((string) $this->input('phone_whatsapp', ''));
        $email     = trim((string) $this->input('email', ''));
        $password  = (string) $this->input('password', '');
        $geoOptIn  = $this->input('geolocation_opt_in') ? 1 : 0;
        $source    = (string) $this->input('registration_source', 'bar');
        if (!array_key_exists($source, User::SOURCE_LABELS)) {
            $source = 'bar';
        }

        $errors = [];

        if ($firstName === '' || mb_strlen($firstName) > 80) {
            $errors['first_name'] = 'Le prénom est obligatoire.';
        }
        if ($lastName === '' || mb_strlen($lastName) > 80) {
            $errors['last_name'] = 'Le nom est obligatoire.';
        }
        if (!preg_match('/^0[1-9]\d{8}$/', $phone)) {
            $errors['phone_whatsapp'] = 'Numéro WhatsApp invalide (format attendu : 06 12 34 56 78).';
        } elseif (User::phoneExists($phone)) {
            $errors['phone_whatsapp'] = 'Ce numéro est déjà associé à un compte.';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Adresse e-mail invalide.';
        } elseif ($email !== '' && User::emailExists($email)) {
            $errors['email'] = 'Cette adresse e-mail est déjà utilisée.';
        }
        if (mb_strlen($password) < 6) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 6 caractères.';
        }

        if ($errors) {
            $this->view('auth/register', [
                'title'  => 'Créer mon compte — Le Commerce',
                'errors' => $errors,
                'old'    => compact('firstName', 'lastName', 'phone', 'email', 'source'),
            ], 'auth');
            return;
        }

        $userId = User::createClient([
            'first_name'     => $firstName,
            'last_name'      => $lastName,
            'phone_whatsapp' => $phone,
            'email'          => $email,
            'password'       => $password,
            'geolocation_opt_in' => $geoOptIn,
            'registration_source' => $source,
        ]);

        Wallet::createForUser($userId);

        $user = User::find($userId);
        Middleware::login($user);

        $this->setFlash('success', 'Bienvenue ' . $firstName . ' ! Votre compte a bien été créé.');
        $this->redirect('/mon-compte');
    }
}
