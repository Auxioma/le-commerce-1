<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Lottery;
use App\Models\LotteryEntry;
use App\Models\User;
use App\Models\Wallet;

/**
 * Page publique de participation à une loterie, atteinte en scannant le QR
 * code généré à la création de la loterie (voir AdminLotteryController).
 * Ne nécessite aucun compte : Nom, Prénom, e-mail et téléphone suffisent —
 * un compte client "léger" (sans mot de passe) est créé ou retrouvé à partir
 * du numéro de téléphone.
 */
class LotteryPublicController extends Controller
{
    public function show(string $token): void
    {
        $lottery = Lottery::findByQrToken($token);

        if (!$lottery || $lottery['status'] !== 'active' || strtotime($lottery['ends_at']) < strtotime('today')) {
            $this->view('lottery/unavailable', [
                'title' => 'Loterie indisponible — Le Commerce',
            ], 'auth');
            return;
        }

        $this->view('lottery/entry', [
            'title'   => 'Participer — ' . $lottery['title'] . ' — Le Commerce',
            'lottery' => $lottery,
            'errors'  => [],
            'old'     => [],
        ], 'auth');
    }

    public function store(string $token): void
    {
        $this->verifyCsrf();

        $lottery = Lottery::findByQrToken($token);

        if (!$lottery || $lottery['status'] !== 'active' || strtotime($lottery['ends_at']) < strtotime('today')) {
            $this->view('lottery/unavailable', [
                'title' => 'Loterie indisponible — Le Commerce',
            ], 'auth');
            return;
        }

        $firstName = trim((string) $this->input('first_name', ''));
        $lastName  = trim((string) $this->input('last_name', ''));
        $email     = trim((string) $this->input('email', ''));
        $phone     = User::normalizePhone((string) $this->input('phone_whatsapp', ''));
        $whatsappOptIn = (bool) $this->input('whatsapp_opt_in');

        $errors = [];
        if ($firstName === '' || mb_strlen($firstName) > 80) {
            $errors['first_name'] = 'Le prénom est obligatoire.';
        }
        if ($lastName === '' || mb_strlen($lastName) > 80) {
            $errors['last_name'] = 'Le nom est obligatoire.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Adresse e-mail invalide.';
        }
        if (!preg_match('/^0[1-9]\d{8}$/', $phone)) {
            $errors['phone_whatsapp'] = 'Numéro de téléphone invalide (format attendu : 06 12 34 56 78).';
        }

        if ($errors) {
            $this->view('lottery/entry', [
                'title'   => 'Participer — ' . $lottery['title'] . ' — Le Commerce',
                'lottery' => $lottery,
                'errors'  => $errors,
                'old'     => compact('firstName', 'lastName', 'email', 'phone', 'whatsappOptIn'),
            ], 'auth');
            return;
        }

        $user = User::findByPhone($phone);
        if ($user) {
            // Compte déjà existant (client ou lead précédent) : on ne
            // touche pas à son identité (nom/e-mail), seulement au
            // consentement WhatsApp — le formulaire n'étant pas authentifié,
            // rien ne garantit que le numéro appartient à la personne qui le
            // soumet.
            User::updateWhatsappOptIn((int) $user['id'], $whatsappOptIn);
            $userId = (int) $user['id'];
        } else {
            $userId = User::createLead([
                'first_name'      => $firstName,
                'last_name'       => $lastName,
                'email'           => $email,
                'phone_whatsapp'  => $phone,
                'whatsapp_opt_in' => $whatsappOptIn,
            ]);
            Wallet::createForUser($userId);
        }

        $entry = LotteryEntry::generate((int) $lottery['id'], $userId);

        $this->view('lottery/confirmation', [
            'title'   => 'Inscription confirmée — Le Commerce',
            'lottery' => $lottery,
            'entry'   => $entry,
            'firstName' => $firstName,
        ], 'auth');
    }
}
