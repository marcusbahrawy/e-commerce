<?php

declare(strict_types=1);

return [
    'host' => \App\Support\Env::string('DB_HOST', '127.0.0.1'),
    'port' => \App\Support\Env::int('DB_PORT', 3306),
    'database' => \App\Support\Env::string('DB_DATABASE', 'motorleaks'),
    'username' => \App\Support\Env::string('DB_USERNAME', 'root'),
    'password' => \App\Support\Env::string('DB_PASSWORD', ''),
    'charset' => \App\Support\Env::string('DB_CHARSET', 'utf8mb4'),
];
