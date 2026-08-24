<?php

declare(strict_types=1);

/**
 * Configuration de la base de données
 * Le Commerce - Bar/Tabac/PMU/FDJ/Presse
 */

return [
    'driver'   => getenv('DB_DRIVER') ?: 'mysql',
    'host'     => getenv('DB_HOST') ?: '127.0.0.1',
    'port'     => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_NAME') ?: 'le_commerce',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASS') ?: '',
    'charset'  => getenv('DB_CHARSET') ?: 'utf8mb4',
];
