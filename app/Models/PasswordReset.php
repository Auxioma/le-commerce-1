<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class PasswordReset extends Model
{
    protected static string $table = 'password_resets';

    /** Durée de validité d'un jeton, en minutes. */
    public const TTL_MINUTES = 60;

    /**
     * Génère un nouveau jeton de réinitialisation pour cet utilisateur et
     * invalide les jetons précédemment émis (un seul lien actif à la fois).
     * Retourne le jeton en clair (à insérer dans le lien envoyé par e-mail) ;
     * seul son hash SHA-256 est conservé en base.
     */
    public static function createForUser(int $userId): string
    {
        self::invalidateForUser($userId);

        $token = bin2hex(random_bytes(32));

        self::create([
            'user_id'    => $userId,
            'token_hash' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', time() + self::TTL_MINUTES * 60),
        ]);

        return $token;
    }

    /**
     * Retourne la ligne password_resets + l'utilisateur associé pour un
     * jeton en clair valide (non utilisé, non expiré), ou null sinon.
     */
    public static function findValidByToken(string $token): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT pr.*, u.email, u.first_name, u.role
             FROM password_resets pr
             INNER JOIN users u ON u.id = pr.user_id AND u.deleted_at IS NULL
             WHERE pr.token_hash = :hash AND pr.used_at IS NULL AND pr.expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute(['hash' => hash('sha256', $token)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function markUsed(int $id): void
    {
        $stmt = self::db()->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /** Invalide (marque utilisés) tous les jetons actifs d'un utilisateur. */
    public static function invalidateForUser(int $userId): void
    {
        $stmt = self::db()->prepare(
            'UPDATE password_resets SET used_at = NOW() WHERE user_id = :user_id AND used_at IS NULL'
        );
        $stmt->execute(['user_id' => $userId]);
    }
}
