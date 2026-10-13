<?php
require_once __DIR__ . '/../../includes/auth.php';
wymagaj_roli('klient');      // wchodzi tylko klient; reszta dostaje komunikat "Brak dostępu"

// Liczba rezerwacji TEGO klienta (id z sesji - nigdy z adresu URL)
$st = $pdo->prepare('SELECT COUNT(*) FROM Rezerwacje WHERE ID_Klienta = ?');
$st->execute([$_SESSION['user_id']]);
$liczba_rezerwacji = (int)$st->fetchColumn();

$tytul = 'Panel klienta';
require __DIR__ . '/../../includes/header.php';
?>

<main class="panel-page">
    <h1>Panel klienta</h1>
    <?php pokaz_komunikat(); ?>

    <section class="panel-box">
        <h2>Witaj, <?= e($_SESSION['imie']) ?>!</h2>
        <p>Liczba Twoich rezerwacji: <strong><?= $liczba_rezerwacji ?></strong></p>
        <p class="field-hint">Lista rezerwacji i tworzenie nowych pojawią się w kolejnym etapie projektu.</p>
        <p>
            <a class="booking-button small-button" href="<?= e(BASE_URL) ?>/public/profil.php">Mój profil</a>
            <a class="booking-button small-button" href="<?= e(BASE_URL) ?>/public/pokoje.php">Zobacz pokoje</a>
        </p>
    </section>
</main>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
