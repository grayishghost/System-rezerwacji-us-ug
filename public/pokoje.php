<?php
require_once __DIR__ . '/../includes/auth.php';
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
        <article class="room-card">
            <img src="<?= e(BASE_URL) ?>/assets/scroll (2).jpg" alt="Pokój standardowy">
            <div class="room-card-content">
                <p class="room-number">Pokój 101</p>
                <h2>Standard dla 1 osoby</h2>
                <p>Przytulny pokój z wygodnym łóżkiem i spokojnym widokiem.</p>
                <strong>200 zł / doba</strong>
                <a class="booking-button room-button" href="index.php#rezerwacja">Wybierz pokój</a>
            </div>
        </article>

        <article class="room-card">
            <img src="<?= e(BASE_URL) ?>/assets/scroll (1).jpg" alt="Apartament deluxe">
            <div class="room-card-content">
                <p class="room-number">Pokój 102</p>
                <h2>Apartament deluxe</h2>
                <p>Więcej przestrzeni, eleganckie wnętrze i dodatkowy komfort.</p>
                <strong>500 zł / doba</strong>
                <a class="booking-button room-button" href="index.php#rezerwacja">Wybierz pokój</a>
            </div>
        </article>
    </section>
</main>



<?php require __DIR__ . '/../includes/footer.php'; ?>
