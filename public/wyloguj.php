<?php
require_once __DIR__ . '/../includes/auth.php';

wyloguj_uzytkownika();
ustaw_komunikat('success', 'Zostałeś wylogowany.');
przekieruj('/public/logowanie.php');
