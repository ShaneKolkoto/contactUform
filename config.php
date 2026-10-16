<?php
declare(strict_types=1);

$env = static function (string $key, string $default = ''): string {
    $value = getenv($key);
    return ($value === false || $value === '') ? $default : $value;
};

return [
    'app' => [
        'env'   => $env('APP_ENV', 'production'),
        'debug' => filter_var($env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOL),
        'name'  => $env('APP_NAME', 'Acme Contact'),
    ],

    'database' => [
        'host'     => $env('DB_HOST',     '127.0.0.1'),
        'port'     => $env('DB_PORT',     '3306'),
        'database' => $env('DB_NAME',     'contact_form'),
        'username' => $env('DB_USER',     'root'),
        'password' => $env('DB_PASS',     ''),
        'charset'  => 'utf8mb4',
    ],
];