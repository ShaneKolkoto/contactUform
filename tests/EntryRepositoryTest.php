<?php

declare(strict_types=1);

namespace Tests;

use App\Database;
use App\EntryRepository;
use PDO;

final class EntryRepositoryTest extends TestCase
{
    private PDO $pdo;
    private EntryRepository $repo;

    public function setUp(): void
    {
        global $config;

        $this->pdo = Database::connect($config['database']);
        $this->repo = new EntryRepository($this->pdo);
    }

    // --------------------------------------------------------
    // create()
    // --------------------------------------------------------

    public function test_create_persists_row_and_returns_id(): void
    {
        $this->inTransaction(function (): void {
            $id = $this->repo->create([
                'name'    => 'Test User',
                'email'   => 'test.user@example.co.za',
                'phone'   => '+27821110000',
                'message' => 'Automated test entry, safe to ignore.',
                'ip'      => inet_pton('127.0.0.1') ?: null,
            ]);

            $this->assertTrue($id > 0, 'create() should return a positive id');

            $row = $this->repo->find($id);
            $this->assertNotNull($row);
            $this->assertSame('Test User', $row['name']);
            $this->assertSame('+27821110000', $row['phone']);
        });
    }

    // --------------------------------------------------------
    // find()
    // --------------------------------------------------------

    public function test_find_missing_id_returns_null(): void
    {
        $this->assertNull($this->repo->find(99_999_999));
    }

    public function test_find_negative_id_returns_null(): void
    {
        // Should not throw, should just not find anything
        $this->assertNull($this->repo->find(-1));
    }

    // --------------------------------------------------------
    // all()
    // --------------------------------------------------------

    public function test_all_returns_entries_newest_first(): void
    {
        $rows = $this->repo->all(limit: 5);

        $this->assertTrue(count($rows) > 0, 'Expected at least one seeded entry');

        $dates = array_column($rows, 'created_at');
        $sorted = $dates;
        rsort($sorted);
        $this->assertSame($sorted, $dates, 'Should be sorted newest-first');
    }

    public function test_all_respects_limit_and_offset(): void
    {
        $three = $this->repo->all(limit: 3);
        $this->assertCount(3, $three); // ← fails if fewer than 3 rows exist

        $firstTwo = $this->repo->all(limit: 2, offset: 0);
        $nextTwo  = $this->repo->all(limit: 2, offset: 2);

        // Paginated pages shouldn't overlap
        $firstIds = array_column($firstTwo, 'id');
        $nextIds  = array_column($nextTwo, 'id');
        $this->assertTrue(
            array_intersect($firstIds, $nextIds) === [],
            'Pages overlap: ' . implode(',', $firstIds) . ' vs ' . implode(',', $nextIds)
        );
    }

    public function test_all_clamps_out_of_range_arguments(): void
    {
        // A huge limit should be silently clamped to 500, not crash
        $rows = $this->repo->all(limit: 999_999);
        $this->assertTrue(count($rows) <= 500);

        // Negative offset should be treated as 0
        $rows = $this->repo->all(limit: 1, offset: -10);
        $this->assertCount(1, $rows);
    }

    // --------------------------------------------------------
    // count()
    // --------------------------------------------------------

    public function test_count_matches_raw_sql(): void
    {
        $reported = $this->repo->count();
        $actual = (int) $this->pdo->query('SELECT COUNT(*) FROM entries')->fetchColumn();
        $this->assertSame($actual, $reported);
    }

    public function test_count_increments_after_create(): void
    {
        $this->inTransaction(function (): void {
            $before = $this->repo->count();

            $this->repo->create([
                'name'    => 'Test User',
                'email'   => 'test.count@example.co.za',
                'phone'   => '+27821110000',
                'message' => 'Automated test entry, safe to ignore.',
            ]);

            $this->assertSame($before + 1, $this->repo->count());
        });
    }

    // --------------------------------------------------------
    // Helpers
    // --------------------------------------------------------

    /**
     * Runs the callback inside a transaction and rolls back afterwards,
     * so writes during tests never pollute the seed data.
     */
    private function inTransaction(callable $fn): void
    {
        $this->pdo->beginTransaction();
        try {
            $fn();
        } finally {
            $this->pdo->rollBack();
        }
    }
}