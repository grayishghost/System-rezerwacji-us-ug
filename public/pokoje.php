<?php
require_once __DIR__ . '/../includes/rezerwacje.php';
$pokoje = pokoje_do_rezerwacji($pdo);
$czy_klient = zalogowany() && ($_SESSION['rola'] ?? '') === 'klient';
$tytul = 'Dostępne pokoje';
require __DIR__ . '/../includes/header.php';
?>

<main class="rooms-page">
    <section class="rooms-heading">
        <p class="hero-eyebrow">Hotel</p>
        <h1>Dostępne pokoje</h1>
        <p>Wybierz pokój, który najlepiej pasuje do Twojego pobytu.</p>
    </section>

    <section class="rooms-grid" aria-label="Lista dostępnych pokoi">
        <?php foreach ($pokoje as $pokoj): ?>
            <article class="room-card">
                <div class="room-card-image" aria-hidden="true"></div>
                <div class="room-card-content">
                    <p class="room-number">Pokój <?= e($pokoj['NrPokoju']) ?></p>
                    <h2><?= e($pokoj['NazwaTypu']) ?></h2>
                    <p><?= e($pokoj['Opis'] ?: 'Komfortowy pokój w Grand Access Hotel.') ?> Maksymalnie <?= (int)$pokoj['IloscOsob'] ?> os.</p>
                    <strong><?= e(number_format((float)$pokoj['CenaDobowa'], 2, ',', ' ')) ?> zł / doba</strong>
                    <?php if ($czy_klient): ?>
                        <a class="booking-button room-button" href="<?= e(BASE_URL) ?>/public/rezerwacja.php?id_pokoju=<?= (int)$pokoj['ID_Pokoju'] ?>">Wybierz pokój</a>
                    <?php elseif (!zalogowany()): ?>
                        <a class="booking-button room-button" href="<?= e(BASE_URL) ?>/public/logowanie.php">Zaloguj się, aby zarezerwować</a>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (!$pokoje): ?><p>Aktualnie nie ma pokoi dostępnych do rezerwacji.</p><?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
