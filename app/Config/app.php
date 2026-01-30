<?php

declare(strict_types=1);

return [
    'env' => \App\Support\Env::string('APP_ENV', 'production'),
    'debug' => \App\Support\Env::bool('APP_DEBUG', false),
    'url' => rtrim(\App\Support\Env::string('APP_URL', 'http://localhost'), '/'),
    'key' => \App\Support\Env::string('APP_KEY', ''),
];
