<?php
require_once __DIR__ . '/../includes/auth.php';

// Zalogowany użytkownik od razu trafia do swojego panelu
if (zalogowany()) {
    przekieruj(panel_roli($_SESSION['rola'] ?? 'klient'));
}

define('MAX_PROB', 5);          // tyle błędnych prób wolno wykonać...
define('OKNO_PROB', 900);       // ...w ciągu 15 minut (900 sekund)

$blad = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_sprawdz();

    $email = mb_strtolower(trim(pole_tekstowe('email')));
    $haslo = pole_tekstowe('haslo');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $blad = 'Podaj poprawny adres e-mail.';
    } elseif ($haslo === '') {
        $blad = 'Podaj hasło.';
    } else {
        // 1. Ile błędnych prób było dla tego e-maila w ostatnich 15 minutach?
        $od = date('Y-m-d H:i:s', time() - OKNO_PROB);
        $st = $pdo->prepare('SELECT COUNT(*) FROM Proby_logowania WHERE Email = ? AND Czas > ?');
        $st->execute([$email, $od]);

        if ($st->fetchColumn() >= MAX_PROB) {
            $blad = 'Zbyt wiele nieudanych prób logowania. Spróbuj ponownie za 15 minut.';
        } else {
            // 2. Szukamy użytkownika po e-mailu
            $st = $pdo->prepare('SELECT ID_Uzytkownika, Imie, Haslo, Rola, Aktywny FROM Uzytkownicy WHERE Email = ?');
            $st->execute([$email]);
            $u = $st->fetch();

            // 3. Sprawdzamy hasło (password_verify porównuje hasło z hashem z bazy)
            if ($u && password_verify($haslo, $u['Haslo'])) {
                if (!$u['Aktywny']) {
                    $blad = 'To konto zostało zablokowane. Skontaktuj się z recepcją hotelu.';
                } else {
                    // SUKCES: nowy identyfikator sesji (ochrona przed session fixation) i zapis danych w sesji
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = (int)$u['ID_Uzytkownika'];
                    $_SESSION['imie'] = $u['Imie'];
                    $_SESSION['rola'] = $u['Rola'];
                    $_SESSION['ostatnia_aktywnosc'] = time();

                    // po udanym logowaniu czyścimy licznik błędnych prób
                    $pdo->prepare('DELETE FROM Proby_logowania WHERE Email = ?')->execute([$email]);

                    przekieruj(panel_roli($u['Rola']));
                }
            } else {
                // BŁĄD: zapisujemy próbę. Komunikat jest taki sam, gdy e-mail nie istnieje
                // i gdy hasło jest złe - dzięki temu nikt nie sprawdzi, kto ma konto.
                $pdo->prepare('INSERT INTO Proby_logowania (Email, IP, Czas) VALUES (?, ?, ?)')
                    ->execute([$email, $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', date('Y-m-d H:i:s')]);
                // sprzątanie starych wpisów (starszych niż doba)
                $pdo->prepare('DELETE FROM Proby_logowania WHERE Czas < ?')
                    ->execute([date('Y-m-d H:i:s', time() - 86400)]);

                $blad = 'Nieprawidłowy e-mail lub hasło.';
            }
        }
    }
}

$tytul = 'Logowanie';
require __DIR__ . '/../includes/header.php';
?>

<main class="login-page">
    <section class="login-panel" aria-labelledby="login-title">
        <div class="login-intro">
            <p class="hero-eyebrow">Strefa klienta</p>
            <h1 id="login-title">Witaj ponownie</h1>
            <p>Zaloguj się, aby sprawdzić swoje rezerwacje i zaplanować chwilę relaksu.</p>
        </div>

        <?php pokaz_komunikat(); ?>

        <?php if ($blad !== ''): ?>
            <p class="form-message form-message-error" role="alert"><?= e($blad) ?></p>
        <?php endif; ?>

        <form class="login-form" action="logowanie.php" method="post">
            <?= csrf_pole() ?>

            <div class="login-field">
                <label for="email">Adres e-mail</label>
                <input type="email" id="email" name="email" autocomplete="email" required value="<?= e($email) ?>">
            </div>

            <div class="login-field">
                <label for="haslo">Hasło</label>
                <input type="password" id="haslo" name="haslo" autocomplete="current-password" required>
            </div>

            <button class="booking-button login-button" type="submit">Zaloguj się</button>
        </form>

        <p class="login-note">Konto klienta, pracownika i administratora logują się tym samym formularzem.
            Nie masz konta? <a class="login-back-link" href="rejestracja.php">Zarejestruj się</a></p>
        <a class="login-back-link" href="index.php">Wróć na stronę główną</a>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
