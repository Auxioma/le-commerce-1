<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected static string $table = 'users';

    public static function findByPhone(string $phone): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM users WHERE phone_whatsapp = :phone LIMIT 1');
        $stmt->execute(['phone' => $phone]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function phoneExists(string $phone): bool
    {
        return self::findByPhone($phone) !== null;
    }

    public static function emailExists(string $email): bool
    {
        $stmt = self::db()->prepare('SELECT 1 FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Normalise un numéro de téléphone français saisi sous différents formats
     * (espaces, +33...) vers un format local à 10 chiffres commençant par 0.
     */
    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '33') && strlen($digits) === 11) {
            $digits = '0' . substr($digits, 2);
        }
        return $digits;
    }

    public static function countReferrals(int $userId): int
    {
        $stmt = self::db()->prepare("SELECT COUNT(*) FROM users WHERE referred_by = :id");
        $stmt->execute(['id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public static function findByReferralCode(string $code): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM users WHERE referral_code = :code LIMIT 1');
        $stmt->execute(['code' => $code]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Liste légère des clients actifs, pour les listes déroulantes admin
     * (génération de code, envoi de message, etc.).
     */
    public static function activeClientsForSelect(): array
    {
        $stmt = self::db()->query(
            "SELECT id, first_name, last_name, phone_whatsapp FROM users
             WHERE role = 'client' AND status = 'actif' ORDER BY first_name ASC"
        );
        return $stmt->fetchAll();
    }

    public static function addLoyaltyPoints(int $userId, int $points): void
    {
        $stmt = self::db()->prepare('UPDATE users SET loyalty_points = loyalty_points + :points WHERE id = :id');
        $stmt->execute(['points' => $points, 'id' => $userId]);
    }
    public static function createClient(array $data): int
    {
        return self::create([
            'first_name'     => $data['first_name'],
            'last_name'      => $data['last_name'],
            'phone_whatsapp' => $data['phone_whatsapp'],
            'email'          => $data['email'] ?: null,
            'password_hash'  => password_hash($data['password'], PASSWORD_DEFAULT),
            'role'           => 'client',
            'segment'        => 'nouveau',
            'status'         => 'actif',
            'referral_code'  => strtoupper(substr($data['first_name'], 0, 4)) . random_int(1000, 9999),
            'geolocation_opt_in' => (int) ($data['geolocation_opt_in'] ?? 0),
        ]);
    }

    /**
     * Derniers clients inscrits — pour le fil d'activité du tableau de bord.
     */
    public static function latestClients(int $limit = 5): array
    {
        $stmt = self::db()->prepare(
            "SELECT id, first_name, last_name, created_at FROM users
             WHERE role = 'client' ORDER BY created_at DESC LIMIT :limit"
        );
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countAll(): int
    {
        return (int) self::db()->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetchColumn();
    }

    public static function countThisMonth(): int
    {
        return (int) self::db()->query(
            "SELECT COUNT(*) FROM users WHERE role = 'client' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())"
        )->fetchColumn();
    }

    /**
     * Liste paginée et filtrée des clients pour le back-office.
     *
     * @param array{q?:string, status?:string, segment?:string, from?:string, to?:string} $filters
     * @return array{data: array, total: int, page: int, perPage: int, totalPages: int}
     */
    public static function paginateClients(array $filters, int $page = 1, int $perPage = 8): array
    {
        $where  = ["role = 'client'"];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = '(first_name LIKE :q1 OR last_name LIKE :q2 OR phone_whatsapp LIKE :q3)';
            $needle = '%' . $filters['q'] . '%';
            $params['q1'] = $needle;
            $params['q2'] = $needle;
            $params['q3'] = $needle;
        }
        if (!empty($filters['status']) && $filters['status'] !== 'tous') {
            $where[] = 'status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['segment']) && $filters['segment'] !== 'tous') {
            $where[] = 'segment = :segment';
            $params['segment'] = $filters['segment'];
        }
        if (!empty($filters['from'])) {
            $where[] = 'DATE(created_at) >= :from';
            $params['from'] = $filters['from'];
        }
        if (!empty($filters['to'])) {
            $where[] = 'DATE(created_at) <= :to';
            $params['to'] = $filters['to'];
        }

        $whereSql = implode(' AND ', $where);

        $countStmt = self::db()->prepare("SELECT COUNT(*) FROM users WHERE {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $page      = max(1, $page);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page      = min($page, $totalPages);
        $offset    = ($page - 1) * $perPage;

        $sql = "SELECT u.*, w.balance AS wallet_balance
                FROM users u
                LEFT JOIN wallets w ON w.user_id = u.id
                WHERE {$whereSql}
                ORDER BY u.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);

        return [
            'data'       => $stmt->fetchAll(),
            'total'      => $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => $totalPages,
        ];
    }
}
