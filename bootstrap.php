<?php

declare(strict_types=1);

/**
 * Application bootstrap.
 * Every public entry point starts with:
 *     require __DIR__ . '/../bootstrap.php';
 *
 * Responsibilities:
 *   1. Load .env (if present)
 *   2. Load config.php
 *   3. Configure error reporting based on APP_DEBUG
 *   4. Register PSR-4-style autoloader for App\ → src/
 *   5. Start session with safe cookie params
 *   6. Reject any request that tries to upload a file
 *   7. Define global helpers used by templates
 */

// ------------------------------------------------------------
// 1. Load .env (simple parser, no Composer dependency)
// ------------------------------------------------------------
$envFile = __DIR__ . '/.env';
if (is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // Strip surrounding quotes
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last = $value[strlen($value) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        // Don't overwrite real env vars (Docker / hosting win)
        if (getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// ------------------------------------------------------------
// 2. Load config
// ------------------------------------------------------------
/** @var array{app:array,database:array} $config */
$config = require __DIR__ . '/config.php';

// ------------------------------------------------------------
// 3. Error reporting
// ------------------------------------------------------------
if ($config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
}
ini_set('log_errors', '1');

// ------------------------------------------------------------
// 4. Autoloader for App\ → src/
// ------------------------------------------------------------
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/src/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relative) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

// ------------------------------------------------------------
// 5. Session (safe cookie params)
// ------------------------------------------------------------
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ------------------------------------------------------------
// 6. Reject file uploads outright — this app has no upload endpoint
// ------------------------------------------------------------
if (!empty($_FILES)) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    exit('File uploads are not accepted.');
}

// ------------------------------------------------------------
// 7. Global helpers
// ------------------------------------------------------------

/**
 * Escape a value for safe HTML output.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Get or create a CSRF token for the current session.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/**
 * Verify a submitted CSRF token against the session.
 */
function csrf_verify(?string $token): bool
{
    return !empty($_SESSION['csrf'])
        && is_string($token)
        && hash_equals($_SESSION['csrf'], $token);
}

/**
 * 303 redirect and exit. Always use after a POST to prevent resubmission.
 */
function redirect(string $url): never
{
    header('Location: ' . $url, true, 303);
    exit;
}

/**
 * Set a one-shot flash message.
 *
 * @param 'success'|'error'|'info'|'warning' $type
 */
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Read and clear the current flash message.
 *
 * @return array{type:string,message:string}|null
 */
function flash_get(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($flash) ? $flash : null;
}