<?php

namespace App\Models;

use App\Core\Model;

class WalletTransaction extends Model
{
    protected static string $table = 'wallet_transactions';

    /**
     * Somme des montants d'un type de transaction sur la période donnée.
     */
    public static function sumByType(string $type, string $period = 'this_month'): float
    {
        $sql = 'SELECT COALESCE(SUM(amount), 0) FROM wallet_transactions WHERE type = :type AND status = "reussi"';
        $sql .= self::periodClause($period);

        $stmt = self::db()->prepare($sql);
        $stmt->execute(['type' => $type]);
        return (float) $stmt->fetchColumn();
    }

    public static function countByType(string $type, string $period = 'this_month'): int
    {
        $sql = 'SELECT COUNT(*) FROM wallet_transactions WHERE type = :type AND status = "reussi"';
        $sql .= self::periodClause($period);

        $stmt = self::db()->prepare($sql);
        $stmt->execute(['type' => $type]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Variation nette du solde total des portefeuilles sur la période
     * (recharges + remboursements - débits).
     */
    public static function netChange(string $period = 'this_month'): float
    {
        $sql = "SELECT COALESCE(SUM(
                    CASE WHEN type IN ('recharge','remboursement') THEN amount ELSE -amount END
                ), 0) FROM wallet_transactions WHERE status = 'reussi'";
        $sql .= self::periodClause($period);

        return (float) self::db()->query($sql)->fetchColumn();
    }

    /**
     * Dernières transactions, avec le nom et le téléphone du client associé.
     */
    public static function latestWithUser(int $limit = 5, array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['from'])) {
            $where[] = 'DATE(wt.created_at) >= :from';
            $params['from'] = $filters['from'];
        }
        if (!empty($filters['to'])) {
            $where[] = 'DATE(wt.created_at) <= :to';
            $params['to'] = $filters['to'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(u.first_name LIKE :q1 OR u.last_name LIKE :q2 OR u.phone_whatsapp LIKE :q3)';
            $needle = '%' . $filters['q'] . '%';
            $params['q1'] = $needle;
            $params['q2'] = $needle;
            $params['q3'] = $needle;
        }

        $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

        $params['limit'] = $limit;

        $stmt = self::db()->prepare(
            'SELECT wt.*, u.id AS user_id, u.first_name, u.last_name, u.phone_whatsapp
             FROM wallet_transactions wt
             JOIN wallets w ON w.id = wt.wallet_id
             JOIN users u ON u.id = w.user_id
             ' . $whereSql . '
             ORDER BY wt.created_at DESC
             LIMIT :limit'
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Historique des transactions d'un client (pour sa fiche détail admin).
     */
    public static function forUser(int $userId, int $limit = 20): array
    {
        $stmt = self::db()->prepare(
            'SELECT wt.* FROM wallet_transactions wt
             JOIN wallets w ON w.id = wt.wallet_id
             WHERE w.user_id = :user_id
             ORDER BY wt.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue('user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countForUser(int $userId): int
    {
        $stmt = self::db()->prepare(
            'SELECT COUNT(*) FROM wallet_transactions wt
             JOIN wallets w ON w.id = wt.wallet_id
             WHERE w.user_id = :user_id'
        );
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public static function forUserPaginated(int $userId, int $page = 1, int $perPage = 10): array
    {
        $total      = self::countForUser($userId);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = max(1, min($page, $totalPages));
        $offset     = ($page - 1) * $perPage;

        $stmt = self::db()->prepare(
            "SELECT wt.* FROM wallet_transactions wt
             JOIN wallets w ON w.id = wt.wallet_id
             WHERE w.user_id = :user_id
             ORDER BY wt.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $stmt->bindValue('user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'       => $stmt->fetchAll(),
            'total'      => $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => $totalPages,
        ];
    }

    public static function sumDebitForUserThisMonth(int $userId): float
    {
        $stmt = self::db()->prepare(
            "SELECT COALESCE(SUM(wt.amount), 0) FROM wallet_transactions wt
             JOIN wallets w ON w.id = wt.wallet_id
             WHERE w.user_id = :user_id AND wt.type = 'debit' AND wt.status = 'reussi'
             AND MONTH(wt.created_at) = MONTH(CURDATE()) AND YEAR(wt.created_at) = YEAR(CURDATE())"
        );
        $stmt->execute(['user_id' => $userId]);
        return (float) $stmt->fetchColumn();
    }

    public static function sumDebitForUserLastMonth(int $userId): float
    {
        $stmt = self::db()->prepare(
            "SELECT COALESCE(SUM(wt.amount), 0) FROM wallet_transactions wt
             JOIN wallets w ON w.id = wt.wallet_id
             WHERE w.user_id = :user_id AND wt.type = 'debit' AND wt.status = 'reussi'
             AND MONTH(wt.created_at) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(wt.created_at) = YEAR(CURDATE() - INTERVAL 1 MONTH)"
        );
        $stmt->execute(['user_id' => $userId]);
        return (float) $stmt->fetchColumn();
    }

    protected static function periodClause(string $period): string
    {
        return match ($period) {
            'this_month' => ' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())',
            'last_month' => ' AND MONTH(created_at) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(created_at) = YEAR(CURDATE() - INTERVAL 1 MONTH)',
            default      => '',
        };
    }
}
