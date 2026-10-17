<?php

declare(strict_types=1);

namespace App;

use PDO;

final class EntryRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO entries (name, email, phone, message, ip_address)
             VALUES (:name, :email, :phone, :message, :ip)'
        );

        $statement->bindValue(':name', $data['name'], PDO::PARAM_STR);
        $statement->bindValue(':email', $data['email'], PDO::PARAM_STR);
        $statement->bindValue(':phone', $data['phone'], PDO::PARAM_STR);
        $statement->bindValue(':message', $data['message'], PDO::PARAM_STR);

        if (isset($data['ip']) && is_string($data['ip']) && $data['ip'] !== '') {
            $statement->bindValue(':ip', $data['ip'], PDO::PARAM_LOB);
        } else {
            $statement->bindValue(':ip', null, PDO::PARAM_NULL);
        }

        $statement->execute();

        return (int) $this->pdo->lastInsertId();
    }

    public function all(int $limit = 50, int $offset = 0): array
    {
        $limit = max(1, min(500, $limit));
        $offset = max(0, $offset);

        $statement = $this->pdo->prepare(
            'SELECT id, name, email, phone, message, created_at
             FROM entries
             ORDER BY created_at DESC
             LIMIT :limit OFFSET :offset'
        );

        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * Returns entries matching an optional search term and time range,
     * newest first, with pagination.
     *
     * @param string|null $search  Matches name, email, or message (LIKE)
     * @param int         $days    Restrict to the last N days (0 = no filter)
     * @return array<int, array<string, mixed>>
     */
    public function search(?string $search, int $days, int $limit, int $offset): array
    {
        [$where, $params] = $this->buildWhereClause($search, $days);

        $limit = max(1, min(500, $limit));
        $offset = max(0, $offset);

        $sql = 'SELECT id, name, email, phone, message, created_at
                FROM entries' . $where . '
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset';

        $statement = $this->pdo->prepare($sql);

        foreach ($params as $name => $value) {
            $statement->bindValue($name, $value, PDO::PARAM_STR);
        }

        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * Counts entries matching the same filter conditions as search().
     */
    public function searchCount(?string $search, int $days): int
    {
        [$where, $params] = $this->buildWhereClause($search, $days);

        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM entries' . $where);

        foreach ($params as $name => $value) {
            $statement->bindValue($name, $value, PDO::PARAM_STR);
        }

        $statement->execute();

        return (int) $statement->fetchColumn();
    }

    /**
     * Builds a WHERE clause from search and day-range filters.
     *
     * Uses three distinct parameter names (:search_name, :search_email,
     * :search_message) instead of reusing :search. With
     * PDO::ATTR_EMULATE_PREPARES => false, MySQL 8 rejects the same named
     * parameter appearing multiple times in one statement (HY093).
     *
     * @return array{0:string, 1:array<string,string>}
     */
    private function buildWhereClause(?string $search, int $days): array
    {
        $conditions = [];
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $conditions[] = '(name LIKE :search_name OR email LIKE :search_email OR message LIKE :search_message)';
            $like = '%' . trim($search) . '%';
            $params[':search_name']    = $like;
            $params[':search_email']   = $like;
            $params[':search_message'] = $like;
        }

        if ($days > 0) {
            $conditions[] = 'created_at >= NOW() - INTERVAL ' . (int) $days . ' DAY';
        }

        $where = $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions);

        return [$where, $params];
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM entries')->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, name, email, phone, message, created_at
             FROM entries
             WHERE id = :id
             LIMIT 1'
        );

        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $row = $statement->fetch();

        return $row === false ? null : $row;
    }
}