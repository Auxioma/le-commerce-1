<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Modèle de base : fournit un mini Active-Record (CRUD générique)
 * Chaque modèle métier étend cette classe et définit $table + $fillable
 */
abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    /**
     * Quand true, delete() ne supprime plus la ligne mais renseigne
     * deleted_at ; all()/find()/where() masquent alors les lignes supprimées.
     * La table doit posséder une colonne deleted_at DATETIME NULL.
     */
    protected static bool $softDeletes = false;

    protected static function db(): PDO
    {
        return Database::connection();
    }

    public static function all(string $orderBy = 'id DESC'): array
    {
        $sql = 'SELECT * FROM ' . static::$table;
        if (static::$softDeletes) {
            $sql .= ' WHERE deleted_at IS NULL';
        }
        $sql .= ' ORDER BY ' . $orderBy;
        $stmt = self::db()->query($sql);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $sql = 'SELECT * FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = :id';
        if (static::$softDeletes) {
            $sql .= ' AND deleted_at IS NULL';
        }
        $stmt = self::db()->prepare($sql . ' LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function where(string $column, $value, string $operator = '='): array
    {
        $sql = "SELECT * FROM " . static::$table . " WHERE {$column} {$operator} :value";
        if (static::$softDeletes) {
            $sql .= ' AND deleted_at IS NULL';
        }
        $stmt = self::db()->prepare($sql);
        $stmt->execute(['value' => $value]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($c) => ':' . $c, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            static::$table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = self::db()->prepare($sql);
        $stmt->execute($data);

        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $assignments = implode(', ', array_map(fn($c) => "{$c} = :{$c}", array_keys($data)));
        $sql = 'UPDATE ' . static::$table . " SET {$assignments} WHERE " . static::$primaryKey . ' = :id';

        $data['id'] = $id;
        $stmt = self::db()->prepare($sql);
        return $stmt->execute($data);
    }

    public static function delete(int $id): bool
    {
        if (static::$softDeletes) {
            $stmt = self::db()->prepare(
                'UPDATE ' . static::$table . ' SET deleted_at = NOW() WHERE ' . static::$primaryKey . ' = :id'
            );
            return $stmt->execute(['id' => $id]);
        }

        $stmt = self::db()->prepare('DELETE FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Réhabilite une ligne soft-supprimée.
     */
    public static function restore(int $id): bool
    {
        $stmt = self::db()->prepare(
            'UPDATE ' . static::$table . ' SET deleted_at = NULL WHERE ' . static::$primaryKey . ' = :id'
        );
        return $stmt->execute(['id' => $id]);
    }

    public static function count(): int
    {
        $sql = 'SELECT COUNT(*) FROM ' . static::$table;
        if (static::$softDeletes) {
            $sql .= ' WHERE deleted_at IS NULL';
        }
        return (int) self::db()->query($sql)->fetchColumn();
    }
}
