<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Regroupe les requêtes d'agrégation utilisées uniquement par la page
 * Statistiques (aucune table dédiée : on interroge les données existantes
 * des autres modules).
 */
class Statistic extends Model
{
    protected static string $table = 'wallet_transactions'; // valeur par défaut, non utilisée directement

    /**
     * Recharges et débits des N derniers jours, groupés par jour.
     */
    public static function walletActivityLastDays(int $days = 14): array
    {
        return self::walletActivityForRange(
            date('Y-m-d', strtotime("-{$days} days")),
            date('Y-m-d')
        );
    }

    /**
     * Recharges et débits entre deux dates, groupés par jour.
     *
     * @param array{from?:string, to?:string} $filters
     */
    public static function walletActivityForRange(?string $from = null, ?string $to = null): array
    {
        $from = $from ?: date('Y-m-d', strtotime('-14 days'));
        $to   = $to ?: date('Y-m-d');

        $stmt = self::db()->prepare(
            "SELECT DATE(created_at) AS jour,
                    SUM(CASE WHEN type = 'recharge' THEN amount ELSE 0 END) AS recharges,
                    SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) AS depenses
             FROM wallet_transactions
             WHERE DATE(created_at) BETWEEN :from AND :to
             GROUP BY DATE(created_at)
             ORDER BY jour ASC"
        );
        $stmt->execute(['from' => $from, 'to' => $to]);
        return $stmt->fetchAll();
    }

    /**
     * Répartition des transactions par moyen de paiement (sur toute la période).
     */
    public static function paymentMethodBreakdown(): array
    {
        return self::paymentMethodBreakdownForRange();
    }

    /**
     * Répartition des transactions par moyen de paiement sur une période.
     *
     * @param array{from?:string, to?:string} $filters
     */
    public static function paymentMethodBreakdownForRange(?string $from = null, ?string $to = null): array
    {
        $from = $from ?: '1970-01-01';
        $to   = $to ?: date('Y-m-d');

        $stmt = self::db()->prepare(
            "SELECT payment_method, COUNT(*) AS nb, COALESCE(SUM(amount), 0) AS total
             FROM wallet_transactions
             WHERE status = 'reussi' AND DATE(created_at) BETWEEN :from AND :to
             GROUP BY payment_method
             ORDER BY total DESC"
        );
        $stmt->execute(['from' => $from, 'to' => $to]);
        return $stmt->fetchAll();
    }

    /**
     * Nouveaux clients par mois sur les N derniers mois.
     */
    public static function newClientsByMonth(int $months = 6): array
    {
        return self::newClientsByRange(
            date('Y-m-d', strtotime("-{$months} months")),
            date('Y-m-d')
        );
    }

    /**
     * Nouveaux clients par mois entre deux dates.
     *
     * @param array{from?:string, to?:string} $filters
     */
    public static function newClientsByRange(?string $from = null, ?string $to = null): array
    {
        $from = $from ?: date('Y-m-d', strtotime('-6 months'));
        $to   = $to ?: date('Y-m-d');

        $stmt = self::db()->prepare(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS mois, COUNT(*) AS nb
             FROM users
             WHERE role = 'client' AND deleted_at IS NULL AND DATE(created_at) BETWEEN :from AND :to
             GROUP BY mois
             ORDER BY mois ASC"
        );
        $stmt->execute(['from' => $from, 'to' => $to]);
        return $stmt->fetchAll();
    }

    public static function offersUsageByType(): array
    {
        $stmt = self::db()->query(
            "SELECT o.type, COUNT(r.id) AS nb
             FROM offer_redemptions r
             JOIN offers o ON o.id = r.offer_id
             WHERE r.status = 'utilisee'
             GROUP BY o.type"
        );
        return $stmt->fetchAll();
    }

    /**
     * Nouvelles inscriptions par jour sur les N derniers jours, avec le
     * total cumulé de clients à chaque jour (pour le graphique du tableau
     * de bord : courbe "Total" + courbe "Nouveaux").
     */
    public static function clientRegistrationsLastDays(int $days = 14): array
    {
        $stmt = self::db()->prepare(
            "SELECT DATE(created_at) AS jour, COUNT(*) AS nouveaux
             FROM users
             WHERE role = 'client' AND deleted_at IS NULL AND created_at >= CURDATE() - INTERVAL :days DAY
             GROUP BY DATE(created_at)
             ORDER BY jour ASC"
        );
        $stmt->bindValue('days', $days, \PDO::PARAM_INT);
        $stmt->execute();
        $byDay = [];
        foreach ($stmt->fetchAll() as $row) {
            $byDay[$row['jour']] = (int) $row['nouveaux'];
        }

        $countStmt = self::db()->prepare(
            "SELECT COUNT(*) FROM users WHERE role = 'client' AND deleted_at IS NULL AND created_at < CURDATE() - INTERVAL :days DAY"
        );
        $countStmt->bindValue('days', $days, \PDO::PARAM_INT);
        $countStmt->execute();
        $running = (int) $countStmt->fetchColumn();

        // Un point par jour du calendrier (même sans inscription ce jour-là),
        // pour que la courbe reste lisible plutôt que de disparaître.
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $jour = date('Y-m-d', strtotime("-{$i} days"));
            $nouveaux = $byDay[$jour] ?? 0;
            $running += $nouveaux;
            $result[] = ['jour' => $jour, 'nouveaux' => $nouveaux, 'total' => $running];
        }
        return $result;
    }

    /**
     * Répartition des clients par segment (nouveau / fidèle / occasionnel).
     */
    public static function clientsBySegment(): array
    {
        return self::db()->query(
            "SELECT segment, COUNT(*) AS nb FROM users WHERE role = 'client' AND deleted_at IS NULL GROUP BY segment"
        )->fetchAll();
    }

    public static function topClientsBySpend(int $limit = 5): array
    {
        return self::topClientsBySpendForRange(null, null, $limit);
    }

    /**
     * Top clients par dépenses sur une période donnée.
     *
     * @param array{from?:string, to?:string} $filters
     */
    public static function topClientsBySpendForRange(?string $from = null, ?string $to = null, int $limit = 5): array
    {
        $from = $from ?: '1970-01-01';
        $to   = $to ?: date('Y-m-d');

        $stmt = self::db()->prepare(
            "SELECT u.id AS user_id, u.first_name, u.last_name, u.phone_whatsapp,
                    COALESCE(SUM(wt.amount), 0) AS total_spent
             FROM users u
             JOIN wallets w ON w.user_id = u.id
             LEFT JOIN wallet_transactions wt ON wt.wallet_id = w.id
                 AND wt.type = 'debit' AND wt.status = 'reussi'
                 AND DATE(wt.created_at) BETWEEN :from AND :to
             WHERE u.role = 'client' AND u.deleted_at IS NULL
             GROUP BY u.id
             ORDER BY total_spent DESC
             LIMIT :limit"
        );
        $params = ['from' => $from, 'to' => $to, 'limit' => $limit];
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
