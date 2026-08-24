<?php

/**
 * Configuration Phinx — lit les mêmes variables d'environnement que
 * config/database.php (DB_HOST/DB_PORT/DB_NAME/DB_USER/DB_PASS/DB_CHARSET),
 * via le même parseur .env que l'application (App::loadEnvFile()).
 */

require __DIR__ . '/vendor/autoload.php';

App\Core\App::loadEnvFile(__DIR__ . '/.env');

$driver  = getenv('DB_DRIVER') ?: 'mysql';
$host    = getenv('DB_HOST') ?: '127.0.0.1';
$port    = getenv('DB_PORT') ?: '3306';
$user    = getenv('DB_USER') ?: 'root';
$pass    = getenv('DB_PASS') ?: '';
$charset = getenv('DB_CHARSET') ?: 'utf8mb4';

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinx_migrations',
        'default_environment' => 'development',
        'development' => [
            'adapter' => $driver,
            'host' => $host,
            'name' => getenv('DB_NAME') ?: 'le_commerce',
            'user' => $user,
            'pass' => $pass,
            'port' => $port,
            'charset' => $charset,
        ],
        'test' => [
            'adapter' => $driver,
            'host' => $host,
            'name' => getenv('DB_NAME_TEST') ?: 'le_commerce_phinx_test',
            'user' => $user,
            'pass' => $pass,
            'port' => $port,
            'charset' => $charset,
        ],
    ],
    'version_order' => 'creation',
];
