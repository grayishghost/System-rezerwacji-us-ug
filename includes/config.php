<?php
// KOPIA TEGO PLIKU: skopiuj go jako includes/config.php i wpisz swoje dane.
// Plik config.php jest w .gitignore, więc nie trafi na GitHub.

define('DB_HOST', 'localhost');
define('DB_NAME', 'grand_access_hotel');
define('DB_USER', 'root');
define('DB_PASS', '');            // w XAMPP domyślnie puste

// Adres folderu projektu w przeglądarce, BEZ ukośnika na końcu.
// Przykład: http://localhost/System-rezerwacji-us-ug/public/  ->  '/System-rezerwacji-us-ug'
// Gdy uruchamiasz serwer poleceniem "php -S localhost:8000" w folderze projektu -> ''
define('BASE_URL', '/System-rezerwacji-us-ug');
