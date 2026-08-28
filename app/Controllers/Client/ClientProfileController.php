<?php

declare(strict_types=1);

namespace App\Controllers\Client;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\User;
use App\Models\UserLocation;

/**
 * "Mes informations" : le client consulte et met à jour son identité, ses
 * coordonnées et son adresse. À la saisie de l'adresse, le navigateur peut
 * transmettre la position GPS (opt-in géolocalisation) — on la conserve dans
 * user_locations, comme le fait déjà ProximityController::check().
 */
class ClientProfileController extends Controller
{
    public function edit(): void
    {
        Middleware::requireRole('client');

        $user = Middleware::user();

        $this->view('client/profile', [
            'title'    => 'Mes informations — Le Commerce',
            'user'     => $user,
            'location' => UserLocation::forUser((int) $user['id']),
            'errors'   => [],
            'old'      => [],
        ], 'client');
    }

    public function update(): void
    {
        Middleware::requireRole('client');
        $this->verifyCsrf();

        $user = Middleware::user();

        $firstName = trim((string) $this->input('first_name', ''));
        $lastName  = trim((string) $this->input('last_name', ''));
        $phone     = User::normalizePhone((string) $this->input('phone_whatsapp', ''));
        $email     = trim((string) $this->input('email', ''));
        $address   = trim((string) $this->input('address_line', ''));
        $postal    = trim((string) $this->input('postal_code', ''));
        $city      = trim((string) $this->input('city', ''));
        $geoOptIn  = $this->input('geolocation_opt_in') ? 1 : 0;

        $errors = [];

        if ($firstName === '' || mb_strlen($firstName) > 80) {
            $errors['first_name'] = 'Le prénom est obligatoire.';
        }
        if ($lastName === '' || mb_strlen($lastName) > 80) {
            $errors['last_name'] = 'Le nom est obligatoire.';
        }
        if (!preg_match('/^0[1-9]\d{8}$/', $phone)) {
            $errors['phone_whatsapp'] = 'Numéro WhatsApp invalide (format attendu : 06 12 34 56 78).';
        } elseif (User::phoneExistsForOther($phone, (int) $user['id'])) {
            $errors['phone_whatsapp'] = 'Ce numéro est déjà associé à un autre compte.';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Adresse e-mail invalide.';
        } elseif ($email !== '' && User::emailExistsForOther($email, (int) $user['id'])) {
            $errors['email'] = 'Cette adresse e-mail est déjà utilisée.';
        }
        if ($address !== '' && mb_strlen($address) > 255) {
            $errors['address_line'] = 'Adresse trop longue (255 caractères maximum).';
        }
        if ($postal !== '' && !preg_match('/^\d{5}$/', $postal)) {
            $errors['postal_code'] = 'Code postal invalide (5 chiffres attendus).';
        }
        if ($city !== '' && mb_strlen($city) > 120) {
            $errors['city'] = 'Nom de ville trop long.';
        }

        if ($errors) {
            $this->view('client/profile', [
                'title'    => 'Mes informations — Le Commerce',
                'user'     => $user,
                'location' => UserLocation::forUser((int) $user['id']),
                'errors'   => $errors,
                'old'      => [
                    'first_name'   => $firstName,
                    'last_name'    => $lastName,
                    'phone'        => $phone,
                    'email'        => $email,
                    'address_line' => $address,
                    'postal_code'  => $postal,
                    'city'         => $city,
                    'geo'          => $geoOptIn,
                ],
            ], 'client');
            return;
        }

        User::update((int) $user['id'], [
            'first_name'         => $firstName,
            'last_name'          => $lastName,
            'phone_whatsapp'     => $phone,
            'email'              => $email !== '' ? $email : null,
            'address_line'       => $address !== '' ? $address : null,
            'postal_code'        => $postal !== '' ? $postal : null,
            'city'               => $city !== '' ? $city : null,
            'geolocation_opt_in' => $geoOptIn,
        ]);

        // Géolocalisation transmise par le navigateur pendant la saisie de
        // l'adresse : on mémorise la dernière position connue du client.
        $lat = $this->input('latitude', '');
        $lng = $this->input('longitude', '');
        if (is_numeric($lat) && is_numeric($lng)
            && abs((float) $lat) <= 90 && abs((float) $lng) <= 180
            && ((float) $lat !== 0.0 || (float) $lng !== 0.0)) {
            UserLocation::upsert((int) $user['id'], (float) $lat, (float) $lng);
        }

        $this->setFlash('success', 'Vos informations ont bien été enregistrées.');
        $this->redirect('/mon-compte/informations');
    }

    public function updatePassword(): void
    {
        Middleware::requireRole('client');
        $this->verifyCsrf();

        $user = Middleware::user();

        $current = (string) $this->input('current_password', '');
        $new     = (string) $this->input('new_password', '');
        $confirm = (string) $this->input('new_password_confirm', '');

        $errors = [];

        if (!empty($user['password_hash']) && !password_verify($current, $user['password_hash'])) {
            $errors['current_password'] = 'Mot de passe actuel incorrect.';
        }
        if (mb_strlen($new) < 6) {
            $errors['new_password'] = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
        }
        if ($new !== $confirm) {
            $errors['new_password_confirm'] = 'La confirmation ne correspond pas.';
        }

        if ($errors) {
            $this->view('client/profile', [
                'title'         => 'Mes informations — Le Commerce',
                'user'          => $user,
                'location'      => UserLocation::forUser((int) $user['id']),
                'errors'        => $errors,
                'old'           => [],
                'openPasswordCard' => true,
            ], 'client');
            return;
        }

        User::update((int) $user['id'], [
            'password_hash' => password_hash($new, PASSWORD_DEFAULT),
        ]);

        $this->setFlash('success', 'Votre mot de passe a bien été modifié.');
        $this->redirect('/mon-compte/informations');
    }
}
