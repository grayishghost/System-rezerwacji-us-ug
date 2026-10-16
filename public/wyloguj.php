<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    przekieruj('/public/index.php');
}
csrf_sprawdz();

wyloguj_uzytkownika();
ustaw_komunikat('success', 'Zostałeś wylogowany.');
przekieruj('/public/logowanie.php');
