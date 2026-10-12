<?php
// Połączenie z bazą danych (PDO). Ten plik jest dołączany przez includes/auth.php.
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // błędy jako wyjątki
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // wiersze jako tablice asocjacyjne
            PDO::ATTR_EMULATE_PREPARES   => false,                    // prawdziwe prepared statements
        ]
    );
} catch (PDOException $e) {
    // Szczegóły błędu zapisujemy do logu serwera, a użytkownik widzi tylko ogólny komunikat.
    error_log('Błąd połączenia z bazą: ' . $e->getMessage());
    http_response_code(500);
    die('Wystąpił błąd serwera. Spróbuj ponownie później.');
}
