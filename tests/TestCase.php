<?php

declare(strict_types=1);

namespace Tests;

use RuntimeException;
use Throwable;

/**
 * Base class for tests. Subclasses define test_* methods; the runner
 * instantiates the class fresh per test and calls each one.
 *
 * Assertions throw TestFailure on mismatch. Any other exception means
 * the test itself broke — the runner reports those separately.
 */ 
abstract class TestCase
{
    private int $assertions = 0;

    public function assertions(): int
    {
        return $this->assertions;
    }

    public function assertTrue(mixed $value, string $message = ''): void
    {
        $this->assertions++;
        if ($value !== true) {
            throw new TestFailure($message ?: 'Expected true, got ' . var_export($value, true));
        }
    }

    public function assertFalse(mixed $value, string $message = ''): void
    {
        $this->assertions++;
        if ($value !== false) {
            throw new TestFailure($message ?: 'Expected false, got ' . var_export($value, true));
        }
    }

    public function assertSame(mixed $expected, mixed $actual, string $message = ''): void
    {
        $this->assertions++;
        if ($expected !== $actual) {
            throw new TestFailure(
                ($message ? $message . ' — ' : '') .
                'Expected ' . var_export($expected, true) .
                ', got ' . var_export($actual, true)
            );
        }
    }

    public function assertNull(mixed $value, string $message = ''): void
    {
        $this->assertions++;
        if ($value !== null) {
            throw new TestFailure(
                ($message ? $message . ' — ' : '') .
                'Expected null, got ' . var_export($value, true)
            );
        }
    }

    public function assertNotNull(mixed $value, string $message = ''): void
    {
        $this->assertions++;
        if ($value === null) {
            throw new TestFailure($message ?: 'Expected non-null value');
        }
    }

    public function assertCount(int $expected, array $actual, string $message = ''): void
    {
        $this->assertions++;
        $count = count($actual);
        if ($count !== $expected) {
            throw new TestFailure(
                ($message ? $message . ' — ' : '') .
                "Expected count {$expected}, got {$count}"
            );
        }
    }

    public function assertArrayHasKey(string|int $key, array $array, string $message = ''): void
    {
        $this->assertions++;
        if (!array_key_exists($key, $array)) {
            throw new TestFailure(
                ($message ? $message . ' — ' : '') .
                "Missing key '{$key}'. Present: " . implode(', ', array_keys($array))
            );
        }
    }

    public function assertThrows(string $exceptionClass, callable $callback, string $message = ''): void
    {
        $this->assertions++;

        try {
            $callback();
        } catch (Throwable $e) {
            if ($e instanceof $exceptionClass) {
                return;
            }
            throw new TestFailure(
                ($message ? $message . ' — ' : '') .
                "Expected {$exceptionClass}, got " . get_class($e)
            );
        }

        throw new TestFailure(
            ($message ? $message . ' — ' : '') .
            "Expected {$exceptionClass}, but nothing was thrown"
        );
    }
}

/**
 * Marker exception for assertion failures. The runner catches this
 * specifically so it can distinguish "failed" from "errored".
 */
final class TestFailure extends RuntimeException
{
}