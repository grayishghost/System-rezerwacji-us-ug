<?php
require_once __DIR__ . '/../../includes/auth.php';
wymagaj_roli('pracownik');   // tylko pracownik (admin ma własny panel)

// Stanowisko pracownika - JOIN łączy tabele Pracownicy i Stanowiska
$st = $pdo->prepare(
    'SELECT s.Nazwa_stanowiska
     FROM Pracownicy p
     LEFT JOIN Stanowiska s ON s.ID_Stanowiska = p.ID_Stanowiska
     WHERE p.ID_Uzytkownika = ?'
);
$st->execute([$_SESSION['user_id']]);
$stanowisko = $st->fetchColumn();

$tytul = 'Panel pracownika';
require __DIR__ . '/../../includes/header.php';
?>

<main class="panel-page">
    <h1>Panel pracownika</h1>
    <?php pokaz_komunikat(); ?>

    <section class="panel-box">
        <h2>Witaj, <?= e($_SESSION['imie']) ?>!</h2>
        <p>Stanowisko: <strong><?= e($stanowisko ?: 'nie przypisano') ?></strong></p>
        <p class="field-hint">Wizyty na dziś, przyszłe rezerwacje i zmiana statusów pojawią się w kolejnych etapach projektu.</p>
        <p><a class="booking-button small-button" href="<?= e(BASE_URL) ?>/public/profil.php">Mój profil</a></p>
    </section>
</main>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
