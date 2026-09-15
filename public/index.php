<?php
$host = 'localhost';
$user = 'root';           
$password = '';           
$database = 'moja_baza';  

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Błąd połączenia: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

echo "Połączono z bazą danych!";

mysqli_close($conn);
?>