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
     * @param string|null $search
     * @param int         $days
     * @param string      $sort   One of: newest, oldest, name_asc, name_desc
     */
    public function search(?string $search, int $days, string $sort, int $limit, int $offset): array
    {
        [$where, $params] = $this->buildWhereClause($search, $days);
        $order = $this->buildOrderClause($sort);

        $limit = max(1, min(500, $limit));
        $offset = max(0, $offset);

        $sql = 'SELECT id, name, email, phone, message, created_at
                FROM entries' . $where . '
                ORDER BY ' . $order . '
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
     * @param string|null $search
     * @param int         $days
     * @param string      $sort   One of: newest, oldest, name_asc, name_desc
     */
    public function exportAll(?string $search, int $days, string $sort): array
    {
        [$where, $params] = $this->buildWhereClause($search, $days);
        $order = $this->buildOrderClause($sort);

        $sql = 'SELECT id, name, email, phone, message, created_at
                FROM entries' . $where . '
                ORDER BY ' . $order;

        $statement = $this->pdo->prepare($sql);

        foreach ($params as $name => $value) {
            $statement->bindValue($name, $value, PDO::PARAM_STR);
        }

        $statement->execute();

        return $statement->fetchAll();
    }

    /**
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

    /**
     * Maps a whitelisted sort key to a safe ORDER BY clause.
     * Never interpolate user input directly into ORDER BY — SQL injection
     * via ORDER BY is possible when values aren't whitelisted.
     */
    private function buildOrderClause(string $sort): string
    {
        $map = [
            'newest'    => 'created_at DESC',
            'oldest'    => 'created_at ASC',
            'name_asc'  => 'name ASC, created_at DESC',
            'name_desc' => 'name DESC, created_at DESC',
        ];

        return $map[$sort] ?? $map['newest'];
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