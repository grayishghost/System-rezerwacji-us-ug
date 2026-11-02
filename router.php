<?php


$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
if (preg_match('~(?:^|/)(?:\.[^/]*|includes|database|scripts|tests|docs)(?:/|$)~i', $path)
    || str_contains($path, '..') || preg_match('~\.(?:sql|md|py|log|zip|txt|json|lock)$~i', $path)) {
    http_response_code(404);
    exit('Nie znaleziono.');
}
if ($path === '/') {
    header('Location: /public/index.php');
    exit;
}
return false;
