<?php

require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {

    error_log('Błąd połączenia z bazą: ' . $e->getMessage());
    http_response_code(500);
    die('Wystąpił błąd serwera. Spróbuj ponownie później.');
}

set_exception_handler(function (Throwable $ex) {
    error_log('Błąd aplikacji: ' . $ex->getMessage());
    http_response_code(500);
    echo 'Wystąpił błąd serwera. Spróbuj ponownie później.';
});
