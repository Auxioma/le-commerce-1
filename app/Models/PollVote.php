<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;
use PDOException;

class PollVote extends Model
{
    protected static string $table = 'poll_votes';

    public static function hasVoted(int $pollId, int $userId): bool
    {
        $stmt = self::db()->prepare('SELECT 1 FROM poll_votes WHERE poll_id = :poll_id AND user_id = :user_id');
        $stmt->execute(['poll_id' => $pollId, 'user_id' => $userId]);
        return (bool) $stmt->fetchColumn();
    }

    public static function optionVotedFor(int $pollId, int $userId): ?int
    {
        $stmt = self::db()->prepare('SELECT option_id FROM poll_votes WHERE poll_id = :poll_id AND user_id = :user_id');
        $stmt->execute(['poll_id' => $pollId, 'user_id' => $userId]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (int) $val : null;
    }

    /**
     * Votes d'un client, avec la question du sondage et l'option choisie —
     * pour son fil de notifications personnel.
     */
    public static function forUserWithPoll(int $userId, int $limit = 50): array
    {
        $stmt = self::db()->prepare(
            'SELECT v.poll_id, v.created_at, p.question, o.label AS option_label
             FROM poll_votes v
             JOIN polls p ON p.id = v.poll_id
             JOIN poll_options o ON o.id = v.option_id
             WHERE v.user_id = :user_id
             ORDER BY v.created_at DESC, v.id DESC
             LIMIT :limit'
        );
        $stmt->bindValue('user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Enregistre un vote de façon atomique : la contrainte UNIQUE(poll_id, user_id)
     * en base empêche tout double vote, y compris en cas de doubles clics ou
     * de requêtes concurrentes (on s'appuie sur la BDD plutôt que sur une
     * vérification applicative préalable, qui laisserait une fenêtre de course).
     *
     * @return array{success:bool, reason:?string, reward:?array}
     */
    public static function castVote(int $pollId, int $optionId, int $userId): array
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO poll_votes (poll_id, option_id, user_id) VALUES (:poll_id, :option_id, :user_id)'
            );
            $stmt->execute(['poll_id' => $pollId, 'option_id' => $optionId, 'user_id' => $userId]);
        } catch (PDOException $e) {
            $pdo->rollBack();
            // Code SQLSTATE 23000 = violation de contrainte d'unicité -> déjà voté
            if ($e->getCode() === '23000') {
                return ['success' => false, 'reason' => 'deja_vote', 'reward' => null];
            }
            return ['success' => false, 'reason' => 'erreur', 'reward' => null];
        }

        $upd = $pdo->prepare('UPDATE poll_options SET votes_count = votes_count + 1 WHERE id = :id');
        $upd->execute(['id' => $optionId]);

        $pollStmt = $pdo->prepare('SELECT reward_type, reward_value FROM polls WHERE id = :id');
        $pollStmt->execute(['id' => $pollId]);
        $poll = $pollStmt->fetch();

        $reward = null;
        if ($poll) {
            $reward = self::applyReward($pdo, $poll, $userId);
        }

        $pdo->commit();

        return ['success' => true, 'reason' => null, 'reward' => $reward];
    }

    /**
     * Applique la récompense de participation configurée sur le sondage.
     * Exécuté dans la même transaction que le vote pour garantir la cohérence.
     */
    private static function applyReward(\PDO $pdo, array $poll, int $userId): ?array
    {
        $value = (float) ($poll['reward_value'] ?? 0);

        switch ($poll['reward_type']) {
            case 'points':
                $points = (int) $value;
                $stmt = $pdo->prepare('UPDATE users SET loyalty_points = loyalty_points + :points WHERE id = :id');
                $stmt->execute(['points' => $points, 'id' => $userId]);
                return ['type' => 'points', 'value' => $points];

            case 'credit':
                $walletStmt = $pdo->prepare('SELECT id FROM wallets WHERE user_id = :user_id LIMIT 1');
                $walletStmt->execute(['user_id' => $userId]);
                $walletId = $walletStmt->fetchColumn();
                if ($walletId) {
                    $creditStmt = $pdo->prepare('UPDATE wallets SET balance = balance + :amount WHERE id = :id');
                    $creditStmt->execute(['amount' => $value, 'id' => $walletId]);

                    $txStmt = $pdo->prepare(
                        "INSERT INTO wallet_transactions (wallet_id, type, amount, payment_method, status, label)
                         VALUES (:wallet_id, 'remboursement', :amount, 'portefeuille', 'reussi', 'Récompense participation sondage')"
                    );
                    $txStmt->execute(['wallet_id' => $walletId, 'amount' => $value]);
                }
                return ['type' => 'credit', 'value' => $value];

            case 'tirage_sort':
                return ['type' => 'tirage_sort', 'value' => 1];

            default:
                return null;
        }
    }
}
