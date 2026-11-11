<?php


require_once __DIR__ . '/assets.php';
?><!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tytul ?? 'Strona') ?> | Grand Access Hotel</title>
    <link rel="stylesheet" type="text/css" href="<?= e(gah_asset_url('css/main.css')) ?>">
    <link rel="stylesheet" type="text/css" href="<?= e(gah_asset_url('css/panel.css')) ?>">
</head>
<body>
<header class="header">
    <div class="logo">
        <a href="<?= e(BASE_URL) ?>/public/index.php"><img class="brand-logo" src="<?= e(gah_asset_url('assets/logo.png')) ?>" width="52" height="52" alt="Logo Grand Access Hotel"><span>Grand Access Hotel</span></a>
    </div>
    <nav class="nav">
        <a href="<?= e(BASE_URL) ?>/public/index.php">Strona główna</a>
        <a href="<?= e(BASE_URL) ?>/public/pokoje.php">Pokoje</a>
        <?php if (zalogowany()): ?>
            <a href="<?= e(BASE_URL . panel_roli($_SESSION['rola'] ?? 'klient')) ?>">Mój panel</a>
            <a href="<?= e(BASE_URL) ?>/public/profil.php">Profil</a>
            <form action="<?= e(BASE_URL) ?>/public/wyloguj.php" method="post" class="nav-logout">
                <?= csrf_pole() ?>
                <button type="submit">Wyloguj (<?= e($_SESSION['imie'] ?? '') ?>)</button>
            </form>
        <?php else: ?>
            <a href="<?= e(BASE_URL) ?>/public/logowanie.php">Logowanie</a>
            <a href="<?= e(BASE_URL) ?>/public/rejestracja.php">Rejestracja</a>
        <?php endif; ?>
    </nav>
</header>
