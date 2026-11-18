<?php
declare(strict_types=1);

$env = static function (string $name, string $default): string {
    $value = getenv($name);
    return $value === false ? $default : $value;
};

return [
    'db_host' => $env('DB_HOST', '127.0.0.1'),
    'db_port' => $env('DB_PORT', '3306'),
    'db_name' => $env('DB_NAME', 'db_keamanan'),
    'db_user' => $env('DB_USER', 'root'),
    'db_password' => $env('DB_PASSWORD', ''),
    'private_key' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'keys' . DIRECTORY_SEPARATOR . 'private.pem',
    'public_key' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'keys' . DIRECTORY_SEPARATOR . 'public.pem',
];
