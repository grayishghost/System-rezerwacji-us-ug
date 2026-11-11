<?php

ini_set('display_errors', '0');
$config = __DIR__ . '/includes/config.php';
if (!is_file($config)) {
    http_response_code(503);
    exit('Aplikacja wymaga lokalnej konfiguracji.');
}
require_once $config;
header('Location: ' . rtrim((string)BASE_URL, '/') . '/public/index.php', true, 302);
exit;
