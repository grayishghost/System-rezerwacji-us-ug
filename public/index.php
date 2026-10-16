<?php
require_once __DIR__ . '/../includes/rezerwacje.php';
$czy_klient = zalogowany() && ($_SESSION['rola'] ?? '') === 'klient';
$pokoje = $czy_klient ? pokoje_do_rezerwacji($pdo) : [];
$tytul = 'Strona główna';
require __DIR__ . '/../includes/header.php';
?>

<main>
  <section class="hero" aria-label="Witamy w Grand Access Hotel">
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <p class="hero-eyebrow">Grand Access Hotel</p>
      <h1>Znajdź chwilę tylko dla siebie</h1>
      <p>Witamy w naszym hotelu. Odpocznij, odzyskaj energię i pozwól nam zadbać o Twój komfort.</p>
      <?php if ($czy_klient): ?><a class="hero-link" href="#rezerwacja">Zarezerwuj pobyt</a><?php endif; ?>
    </div>
  </section>

<?php if ($czy_klient): ?>
<section class="rezerwacja" id="rezerwacja">
  <form class="rezerwacja-form" action="rezerwacja.php" method="get">
    <div class="pola">
      <div class="pole">
        <label for="id_pokoju">Wybrany pokój:</label>
        <select id="id_pokoju" name="id_pokoju" required>
          <option value="">-- Wybierz pokój --</option>
          <?php foreach ($pokoje as $pokoj): ?>
            <option value="<?= (int)$pokoj['ID_Pokoju'] ?>">Pokój <?= e($pokoj['NrPokoju']) ?> - <?= e($pokoj['NazwaTypu']) ?> (<?= e($pokoj['CenaDobowa']) ?> zł/doba, do <?= (int)$pokoj['IloscOsob'] ?> os.)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="pole">
        <label for="data_od">Data zameldowania:</label>
        <input type="date" id="data_od" name="data_od" min="<?= date('Y-m-d') ?>" required>
      </div>

      <div class="pole">
        <label for="data_do">Data wymeldowania:</label>
        <input type="date" id="data_do" name="data_do" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
      </div>

      <div class="pole">
        <label for="ilosc_osob">Liczba osób:</label>
        <input type="number" id="ilosc_osob" name="ilosc_osob" min="1" value="1" required>
      </div>
    </div>
    <button class="booking-button" type="submit">Przejdź do rezerwacji</button>
  </form>

</section>
<?php endif; ?>
</main>

<?php if ($czy_klient): ?>
<script>
  const checkIn = document.querySelector("#data_od");
  const checkOut = document.querySelector("#data_do");

  checkIn.addEventListener("change", () => {
    checkOut.min = checkIn.value;
  });

  checkOut.addEventListener("change", () => {
    checkOut.setCustomValidity(
      checkOut.value && checkOut.value <= checkIn.value
        ? "Data wymeldowania musi być późniejsza niż data zameldowania."
        : ""
    );
  });
</script>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
