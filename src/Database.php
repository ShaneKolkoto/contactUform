<?php
declare(strict_types=1);

namespace App;

use PDO;
use PDOException;
use RuntimeException;

/**
 * PDO connection factory.
 *
 * Usage:
 *     $pdo = Database::connect($config['database']);
 *
 * Kept deliberately thin — no query builder, no ORM. Queries live in
 * repositories (e.g. EntryRepository) so this class stays replaceable.
 */
final class Database
{
    private static ?PDO $instance = null;

    /**
     * Return a shared PDO connection, creating it on first call.
     *
     * @param array{host:string,port:string|int,database:string,username:string,password:string,charset:string} $config
     */
    public static function connect(array $config): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        self::$instance = self::createConnection($config);
        return self::$instance;
    }

    /**
     * Force a new connection on next call to connect().
     * Useful in tests or if you ever need to reconnect.
     */
    public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * @param array{host:string,port:string|int,database:string,username:string,password:string,charset:string} $config
     */
    private static function createConnection(array $config): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            return new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    // Throw exceptions on error (never silent failures)
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

                    // Return rows as associative arrays by default
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                    // Use real prepared statements, not client-side emulation
                    PDO::ATTR_EMULATE_PREPARES   => false,

                    // Keep the connection alive across requests (harmless here)
                    PDO::ATTR_PERSISTENT         => false,

                    // Force utf8mb4 at the connection level too
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']} COLLATE {$config['charset']}_unicode_ci",
                ]
            );
        } catch (PDOException $e) {
            // Log the real error server-side, but never leak credentials
            // or connection strings to the browser.
            error_log('[Database] Connection failed: ' . $e->getMessage());

            throw new RuntimeException(
                'Database connection failed. Please try again later.',
                previous: $e
            );
        }
    }

    // Prevent instantiation — this is a static-only utility class
    private function __construct() {}
}