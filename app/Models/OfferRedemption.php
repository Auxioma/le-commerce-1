<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class OfferRedemption extends Model
{
    protected static string $table = 'offer_redemptions';

    /**
     * Génère un code unique pour (offre, client, canal) et l'enregistre.
     * Si un code valide existe déjà pour ce couple offre/client, il est réutilisé
     * plutôt que d'en créer un nouveau (évite les doublons en caisse).
     */
    public static function generate(int $offerId, int $userId, string $channel = 'qr_caisse'): array
    {
        $existing = self::db()->prepare(
            "SELECT * FROM offer_redemptions
             WHERE offer_id = :offer_id AND user_id = :user_id AND status = 'valide'
             LIMIT 1"
        );
        $existing->execute(['offer_id' => $offerId, 'user_id' => $userId]);
        if ($row = $existing->fetch()) {
            return $row;
        }

        $code = self::generateUniqueCode();

        $id = self::create([
            'offer_id' => $offerId,
            'user_id'  => $userId,
            'code'     => $code,
            'channel'  => $channel,
            'status'   => 'valide',
        ]);

        return self::find($id);
    }

    private static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(bin2hex(random_bytes(4))); // ex: 9F3C7A2B
            $exists = self::db()->prepare('SELECT 1 FROM offer_redemptions WHERE code = :code');
            $exists->execute(['code' => $code]);
        } while ($exists->fetchColumn());

        return $code;
    }

    /**
     * Recherche un code avec les informations de l'offre et du client (sans verrou),
     * utilisée pour l'étape "vérification" avant validation en caisse.
     */
    public static function findByCodeWithDetails(string $code): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT r.*, o.title AS offer_title, o.description AS offer_description,
                    o.valid_until, u.first_name, u.last_name, u.phone_whatsapp
             FROM offer_redemptions r
             JOIN offers o ON o.id = r.offer_id
             JOIN users u ON u.id = r.user_id
             WHERE r.code = :code
             LIMIT 1'
        );
        $stmt->execute(['code' => strtoupper(trim($code))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Valide (consomme) un code de manière atomique et protégée contre les
     * doubles scans concurrents : verrou SELECT ... FOR UPDATE le temps de
     * la transaction, puis UPDATE conditionné sur status = 'valide'.
     *
     * @return array{success:bool, reason:?string, data:?array}
     */
    public static function redeem(string $code): array
    {
        $code = strtoupper(trim($code));
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'SELECT r.*, o.title AS offer_title, o.valid_until, u.first_name, u.last_name
                 FROM offer_redemptions r
                 JOIN offers o ON o.id = r.offer_id
                 JOIN users u ON u.id = r.user_id
                 WHERE r.code = :code
                 FOR UPDATE'
            );
            $stmt->execute(['code' => $code]);
            $row = $stmt->fetch();

            if (!$row) {
                $pdo->rollBack();
                return ['success' => false, 'reason' => 'introuvable', 'data' => null];
            }

            if ($row['status'] === 'utilisee') {
                $pdo->rollBack();
                return ['success' => false, 'reason' => 'deja_utilisee', 'data' => $row];
            }

            if ($row['status'] === 'expiree' || strtotime($row['valid_until']) < strtotime('today')) {
                $upd = $pdo->prepare("UPDATE offer_redemptions SET status = 'expiree' WHERE id = :id AND status = 'valide'");
                $upd->execute(['id' => $row['id']]);
                $pdo->commit();
                return ['success' => false, 'reason' => 'expiree', 'data' => $row];
            }

            $upd = $pdo->prepare(
                "UPDATE offer_redemptions SET status = 'utilisee', used_at = NOW()
                 WHERE id = :id AND status = 'valide'"
            );
            $upd->execute(['id' => $row['id']]);

            if ($upd->rowCount() === 0) {
                // Un autre scan concurrent a validé le code entre-temps
                $pdo->rollBack();
                return ['success' => false, 'reason' => 'deja_utilisee', 'data' => $row];
            }

            $pdo->commit();
            $row['status'] = 'utilisee';
            return ['success' => true, 'reason' => null, 'data' => $row];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return ['success' => false, 'reason' => 'erreur', 'data' => null];
        }
    }

    /**
     * Offres reçues par un client (pour son espace personnel).
     */
    public static function forUser(int $userId): array
    {
        $stmt = self::db()->prepare(
            'SELECT r.*, o.title AS offer_title, o.description AS offer_description, o.valid_until
             FROM offer_redemptions r
             JOIN offers o ON o.id = r.offer_id
             WHERE r.user_id = :user_id
             ORDER BY r.created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Dernières offres validées en caisse, avec le nom du client et de
     * l'offre — pour le fil d'activité du tableau de bord.
     */
    public static function latestUsedWithUser(int $limit = 5): array
    {
        $stmt = self::db()->prepare(
            'SELECT r.used_at, o.title AS offer_title, u.first_name, u.last_name
             FROM offer_redemptions r
             JOIN offers o ON o.id = r.offer_id
             JOIN users u ON u.id = r.user_id
             WHERE r.status = "utilisee"
             ORDER BY r.used_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countUsedThisMonth(): int
    {
        return (int) self::db()->query(
            "SELECT COUNT(*) FROM offer_redemptions
             WHERE status = 'utilisee' AND MONTH(used_at) = MONTH(CURDATE()) AND YEAR(used_at) = YEAR(CURDATE())"
        )->fetchColumn();
    }

    public static function countDistinctClientsTouched(): int
    {
        return (int) self::db()->query('SELECT COUNT(DISTINCT user_id) FROM offer_redemptions')->fetchColumn();
    }

    public static function countAll(): int
    {
        return (int) self::db()->query('SELECT COUNT(*) FROM offer_redemptions')->fetchColumn();
    }

    /**
     * Économies estimées offertes aux clients (somme de la valeur des
     * offres utilisées — le champ `value` sert d'estimation en euros pour
     * tous les types d'offre sauf `reduction_pourcentage`).
     */
    public static function sumSavings(): float
    {
        return (float) self::db()->query(
            "SELECT COALESCE(SUM(o.value), 0)
             FROM offer_redemptions r
             JOIN offers o ON o.id = r.offer_id
             WHERE r.status = 'utilisee' AND o.type != 'reduction_pourcentage'"
        )->fetchColumn();
    }
}
