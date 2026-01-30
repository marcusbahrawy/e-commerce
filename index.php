<?php
/**
 * Inngang når document root er satt til public_html (rot av deploy).
 * Viderekaller til public/index.php der appen faktisk starter.
 */
require __DIR__ . '/public/index.php';
