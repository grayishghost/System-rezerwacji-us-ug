<?php

ini_set('display_errors', '0');
set_exception_handler(function (Throwable $ex) {
    error_log('Błąd aplikacji: ' . $ex->getMessage());
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, "Błąd aplikacji. Sprawdź konfigurację i log serwera.\n");
        exit(1);
    }
    http_response_code(500);
    echo 'Wystąpił błąd serwera. Spróbuj ponownie później.';
});
$config = __DIR__ . '/config.php';
if (!is_file($config)) {
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, "Brak includes/config.php. Utwórz lokalną kopię config.example.php.\n");
        exit(1);
    }
    http_response_code(503);
    exit('Aplikacja wymaga konfiguracji połączenia z bazą.');
}
require_once $config;
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::ATTR_EMULATE_PREPARES => false]
    );
} catch (PDOException $ex) {
    error_log('Błąd połączenia z bazą: ' . $ex->getMessage());
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, "Nie udało się połączyć z bazą. Sprawdź lokalny config i log.\n");
        exit(1);
    }
    http_response_code(500);
    exit('Wystąpił błąd serwera. Spróbuj ponownie później.');
}
