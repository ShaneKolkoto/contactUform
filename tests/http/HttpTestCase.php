<?php

declare(strict_types=1);

namespace Tests;

use PDO;
use RuntimeException;

abstract class HttpTestCase extends TestCase
{
    private static $serverProcess = null;
    private static int $port = 0;
    private static string $baseUrl = '';

    protected PDO $pdo;

    /** @var array<string,string> Cookie jar for the current request chain */
    private array $cookies = [];


    public static function bootServer(): void
    {
        if (self::$serverProcess !== null) {
            return;
        }

        self::$port = random_int(20000, 40000);
        self::$baseUrl = 'http://127.0.0.1:' . self::$port;

        $docRoot = dirname(__DIR__) . '/public';
        $cmd = sprintf(
            'php -S 127.0.0.1:%d -t %s',
            self::$port,
            escapeshellarg($docRoot)
        );

        self::$serverProcess = proc_open(
            $cmd,
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            dirname(__DIR__)
        );

        if (!is_resource(self::$serverProcess)) {
            throw new RuntimeException('Failed to start PHP built-in server');
        }

        // Wait for the server to accept connections (up to 5 seconds)
        $deadline = microtime(true) + 5;
        while (microtime(true) < $deadline) {
            $fp = @fsockopen('127.0.0.1', self::$port, $errno, $errstr, 0.1);
            if ($fp) {
                fclose($fp);
                return;
            }
            usleep(50_000);
        }

        throw new RuntimeException('PHP built-in server did not start in time on port ' . self::$port);
    }

    public static function shutdownServer(): void
    {
        if (self::$serverProcess !== null) {
            proc_terminate(self::$serverProcess);
            proc_close(self::$serverProcess);
            self::$serverProcess = null;
        }
    }

    // --------------------------------------------------------
    // Per-test setup
    // --------------------------------------------------------

    public function setUp(): void
    {
        global $config;

        self::bootServer();

        $this->pdo = new PDO(
            sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $config['database']['host'],
                $config['database']['port'],
                $config['database']['database']
            ),
            $config['database']['username'],
            $config['database']['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        $this->cookies = [];
    }

    // --------------------------------------------------------
    // HTTP client helpers
    // --------------------------------------------------------

    /**
     * Performs an HTTP request. Returns the raw response with status,
     * headers, and body.
     *
     * @return array{status:int, headers:array<string,string>, body:string, location:?string}
     */
    protected function request(string $method, string $path, array $body = []): array
    {
        $url = self::$baseUrl . $path;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTPHEADER => $this->cookieHeader(),
            CURLOPT_TIMEOUT => 10,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body));
        }

        $raw = curl_exec($ch);
        if ($raw === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('HTTP request failed: ' . $err);
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $rawHeaders = substr($raw, 0, $headerSize);
        $rawBody = substr($raw, $headerSize);

        $headers = $this->parseHeaders($rawHeaders);

        if (isset($headers['set-cookie'])) {
            $this->storeCookies($headers['set-cookie']);
        }

        return [
            'status' => $status,
            'headers' => $headers,
            'body' => $rawBody,
            'location' => $headers['location'] ?? null,
        ];
    }

    protected function get(string $path): array
    {
        return $this->request('GET', $path);
    }

    protected function post(string $path, array $body): array
    {
        return $this->request('POST', $path, $body);
    }

    /**
     * Fetches a CSRF token by GETting the form and extracting the
     * hidden input value. Uses the current cookie jar, so the resulting
     * token will match the session the server sees.
     */
    protected function fetchCsrfToken(): string
    {
        $response = $this->get('/');
        $this->assertSame(200, $response['status'], 'Form page should return 200');

        if (!preg_match('/name="csrf"\s+value="([a-f0-9]+)"/', $response['body'], $m)) {
            throw new RuntimeException('Could not find CSRF token in form HTML');
        }

        return $m[1];
    }

    protected function clearCookies(): void
    {
        $this->cookies = [];
    }

    // --------------------------------------------------------
    // Cookie handling
    // --------------------------------------------------------

    /** @return array<int,string> */
    private function cookieHeader(): array
    {
        if ($this->cookies === []) {
            return [];
        }

        $pairs = [];
        foreach ($this->cookies as $name => $value) {
            $pairs[] = "{$name}={$value}";
        }
        return ['Cookie: ' . implode('; ', $pairs)];
    }

    private function storeCookies(string $rawSetCookie): void
    {
        $parts = preg_split('/,(?=[^;]*=)/', $rawSetCookie);
        if ($parts === false) {
            return;
        }
        foreach ($parts as $part) {
            if (preg_match('/^([^=]+)=([^;]*)/', trim($part), $m)) {
                $this->cookies[trim($m[1])] = $m[2];
            }
        }
    }

    /** @return array<string,string> */
    private function parseHeaders(string $raw): array
    {
        $headers = [];
        foreach (explode("\r\n", $raw) as $line) {
            if (str_contains($line, ':')) {
                [$name, $value] = explode(':', $line, 2);
                $headers[strtolower(trim($name))] = trim($value);
            }
        }
        return $headers;
    }

    // --------------------------------------------------------
    // DB helpers
    // --------------------------------------------------------

    protected function countEntries(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM entries')->fetchColumn();
    }

    protected function findEntryByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM entries WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    protected function deleteByEmail(string $email): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM entries WHERE email = :email');
        $stmt->execute([':email' => $email]);
    }
}