<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Database;

$dbStatus = '';
try {
    $pdo = Database::connect($config['database']);
    $count = $pdo->query('SELECT COUNT(*) FROM entries')->fetchColumn();
    $dbStatus = "✅ Connected — {$count} entries in DB";
} catch (Throwable $e) {
    $dbStatus = '❌ ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bootstrap Test</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 640px; margin: 3rem auto; line-height: 1.6; }
        code { background: #f2f4f7; padding: 0.1em 0.35em; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>Bootstrap working</h1>
    <p>PHP: <strong><?= PHP_VERSION ?></strong></p>
    <p>App env: <strong><?= e($config['app']['env']) ?></strong></p>
    <p>Debug: <strong><?= $config['app']['debug'] ? 'on' : 'off' ?></strong></p>
    <p>DB host: <strong><?= e($config['database']['host']) ?></strong></p>
    <p>DB status: <?= e($dbStatus) ?></p>
    <p>CSRF token: <code><?= e(substr(csrf_token(), 0, 16)) ?>…</code></p>
</body>
</html>