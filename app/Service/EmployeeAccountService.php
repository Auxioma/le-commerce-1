<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\User;

/**
 * Provisionnement du compte de connexion back-office (rôle 'employe') lié à
 * une fiche employé : création, mise à jour ou désactivation selon la case
 * "accès back-office" du formulaire. Ne dépend d'aucune couche HTTP (pas de
 * redirection/flash) : le Controller traduit le résultat en réponse HTTP.
 */
final class EmployeeAccountService
{
    /**
     * @param array{first_name: string, last_name: string, email: ?string, phone: ?string} $data
     * @return array{success: bool, userId: int|null, error: string|null}
     */
    public function sync(?array $employee, array $data, bool $grantAccess, string $password): array
    {
        $existingUserId = $employee['user_id'] ?? null;

        if (!$grantAccess) {
            if ($existingUserId) {
                User::update((int) $existingUserId, ['status' => 'inactif']);
            }
            return ['success' => true, 'userId' => $existingUserId, 'error' => null];
        }

        if ($data['email'] === null) {
            return ['success' => false, 'userId' => null, 'error' => "Une adresse e-mail est nécessaire pour donner un accès back-office."];
        }

        if ($existingUserId) {
            return $this->updateExisting((int) $existingUserId, $data, $password);
        }

        return $this->createNew($data, $password);
    }

    private function updateExisting(int $userId, array $data, string $password): array
    {
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
        User::update($userId, $userData);

        return ['success' => true, 'userId' => $userId, 'error' => null];
    }

    private function createNew(array $data, string $password): array
    {
        if ($password === '') {
            return ['success' => false, 'userId' => null, 'error' => 'Un mot de passe est nécessaire pour créer un accès back-office.'];
        }
        if ($data['phone'] === null) {
            return ['success' => false, 'userId' => null, 'error' => 'Un numéro de téléphone est nécessaire pour créer un accès back-office.'];
        }

        $phone = User::normalizePhone($data['phone']);
        if (User::phoneExists($phone)) {
            return ['success' => false, 'userId' => null, 'error' => 'Ce numéro de téléphone est déjà utilisé par un autre compte.'];
        }
        if (User::emailExists($data['email'])) {
            return ['success' => false, 'userId' => null, 'error' => 'Cette adresse e-mail est déjà utilisée par un autre compte.'];
        }

        $userId = User::create([
            'first_name'     => $data['first_name'],
            'last_name'      => $data['last_name'],
            'phone_whatsapp' => $phone,
            'email'          => $data['email'],
            'password_hash'  => password_hash($password, PASSWORD_DEFAULT),
            'role'           => 'employe',
            'status'         => 'actif',
        ]);

        return ['success' => true, 'userId' => $userId, 'error' => null];
    }
}
