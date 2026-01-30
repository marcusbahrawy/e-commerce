<?php

// For PHP built-in server: php -S localhost:8000 -t public public/router.php
// Serves static files; forwards everything else to index.php

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$file = __DIR__ . $uri;

if ($uri !== '/' && is_file($file) && !is_dir($file)) {
    return false;
}

require __DIR__ . '/index.php';
