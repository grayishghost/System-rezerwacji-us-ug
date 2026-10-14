<?php
// Wspólny początek każdej strony. Przed dołączeniem ustaw $tytul = 'Nazwa strony';
// Zakładamy, że includes/auth.php został już załadowany przez stronę.
?><!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tytul ?? 'Strona') ?> | Grand Access Hotel</title>
    <link rel="stylesheet" type="text/css" href="<?= e(BASE_URL) ?>/css/main.css">
    <link rel="stylesheet" type="text/css" href="<?= e(BASE_URL) ?>/css/panel.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Faculty+Glyphic&display=swap" rel="stylesheet">
</head>
<body>
<header class="header">
    <div class="logo">
        <img src="<?= e(BASE_URL) ?>/assets/logo.png" alt="Grand Access Hotel">
    </div>
    <nav class="nav">
        <a href="<?= e(BASE_URL) ?>/public/index.php">Strona główna</a>
        <a href="<?= e(BASE_URL) ?>/public/pokoje.php">Pokoje</a>
        <?php if (zalogowany()): ?>
            <a href="<?= e(BASE_URL . panel_roli($_SESSION['rola'] ?? 'klient')) ?>">Mój panel</a>
            <a href="<?= e(BASE_URL) ?>/public/profil.php">Profil</a>
            <a href="<?= e(BASE_URL) ?>/public/wyloguj.php">Wyloguj (<?= e($_SESSION['imie'] ?? '') ?>)</a>
        <?php else: ?>
            <a href="<?= e(BASE_URL) ?>/public/logowanie.php">Logowanie</a>
            <a href="<?= e(BASE_URL) ?>/public/rejestracja.php">Rejestracja</a>
        <?php endif; ?>
    </nav>
</header>
