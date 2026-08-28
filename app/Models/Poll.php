<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Poll extends Model
{
    protected static string $table = 'polls';

    public const REWARD_LABELS = [
        'points'      => 'Points fidélité',
        'credit'      => 'Crédit portefeuille',
        'tirage_sort' => 'Tirage au sort',
        'aucune'      => 'Aucune récompense',
    ];

    /**
     * Liste des sondages avec leur nombre total de participations.
     */
    public static function listWithStats(?string $status = null): array
    {
        $sql = "SELECT p.*, COALESCE(SUM(o.votes_count), 0) AS participations
                FROM polls p
                LEFT JOIN poll_options o ON o.poll_id = p.id";
        $params = [];
        if ($status) {
            $sql .= ' WHERE p.status = :status';
            $params['status'] = $status;
        }
        $sql .= ' GROUP BY p.id ORDER BY p.created_at DESC';

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function countByStatus(string $status): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM polls WHERE status = :status');
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public static function totalParticipations(): int
    {
        return (int) self::db()->query('SELECT COALESCE(SUM(votes_count), 0) FROM poll_options')->fetchColumn();
    }

    /**
     * Taux de participation moyen = votants distincts / total clients actifs.
     */
    public static function averageParticipationRate(): float
    {
        $voters = (int) self::db()->query('SELECT COUNT(DISTINCT user_id) FROM poll_votes')->fetchColumn();
        $totalClients = User::countAll();
        return $totalClients > 0 ? round(($voters / $totalClients) * 100) : 0;
    }

    /**
     * Total des récompenses en crédit portefeuille distribuées (les points
     * fidélité et tirages au sort ne sont pas convertibles en euros).
     */
    public static function totalRewardsGiven(): float
    {
        return (float) self::db()->query(
            "SELECT COALESCE(SUM(p.reward_value * o.votes), 0)
             FROM polls p
             JOIN (SELECT poll_id, SUM(votes_count) AS votes FROM poll_options GROUP BY poll_id) o ON o.poll_id = p.id
             WHERE p.reward_type = 'credit'"
        )->fetchColumn();
    }

    /**
     * Le sondage actif le plus récent, pour l'encart "Sondage à la une".
     */
    public static function featured(): ?array
    {
        $stmt = self::db()->query(
            "SELECT * FROM polls WHERE status = 'actif' ORDER BY created_at DESC LIMIT 1"
        );
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function countCreatedThisMonth(): int
    {
        return (int) self::db()->query(
            "SELECT COUNT(*) FROM polls WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())"
        )->fetchColumn();
    }

    /**
     * Sondages actifs et non terminés proposés aux clients.
     */
    public static function activeForClients(): array
    {
        $stmt = self::db()->query(
            "SELECT * FROM polls WHERE status = 'actif' AND ends_at >= CURDATE() ORDER BY ends_at ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Sondages ouverts auxquels le client n'a pas encore répondu — sert à
     * lui notifier « Nouveau sondage disponible » dans son fil.
     */
    public static function openNotVotedBy(int $userId): array
    {
        $stmt = self::db()->prepare(
            "SELECT p.id, p.question, p.created_at, p.ends_at
             FROM polls p
             WHERE p.status = 'actif' AND p.ends_at >= CURDATE()
               AND NOT EXISTS (
                   SELECT 1 FROM poll_votes v WHERE v.poll_id = p.id AND v.user_id = :user_id
               )
             ORDER BY p.created_at DESC"
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
