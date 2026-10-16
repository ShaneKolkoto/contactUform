<?php

declare(strict_types=1);

namespace App;

use PDO;

/**
 * Every SQL query that touches the entries table lives here.
 * Nothing outside this class writes SQL for entries.
 *
 * Typical use:
 *
 *     $repo = new EntryRepository(Database::connect($config['database']));
 *     $id   = $repo->create([...]);
 *     $rows = $repo->all();
 */
final class EntryRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Inserts a new entry and returns its id.
     *
     * Expects already-validated data — name, email, phone (E.164),
     * message, and an optional binary-packed IP.
     */
    public function create(array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO entries (name, email, phone, message, ip_address)
             VALUES (:name, :email, :phone, :message, :ip)'
        );

        $statement->execute([
            ':name'    => $data['name'],
            ':email'   => $data['email'],
            ':phone'   => $data['phone'],
            ':message' => $data['message'],
            ':ip'      => $data['ip'] ?? null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Returns entries newest-first, with optional pagination.
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(int $limit = 50, int $offset = 0): array
    {
        // Clamp so a bad query string can't ask for the whole table
        $limit = max(1, min(500, $limit));
        $offset = max(0, $offset);

        $statement = $this->pdo->prepare(
            'SELECT id, name, email, phone, message, created_at
             FROM entries
             ORDER BY created_at DESC
             LIMIT :limit OFFSET :offset'
        );

        // MySQL rejects LIMIT/OFFSET placeholders bound as strings
        // when emulated prepares are off, so bind them as ints
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * Total number of entries. Used for pagination and empty-state checks.
     */
    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM entries')->fetchColumn();
    }

    /**
     * Finds a single entry by id, or null if it doesn't exist.
     *
     * @return array<string, mixed>|null
     */
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