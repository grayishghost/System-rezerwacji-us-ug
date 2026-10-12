<?php
require_once __DIR__ . '/../includes/auth.php';

if (zalogowany()) {
    przekieruj(panel_roli($_SESSION['rola'] ?? 'klient'));
}

$bledy = [];
$dane = ['imie' => '', 'nazwisko' => '', 'email' => '', 'telefon' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_sprawdz();

    $dane['imie']     = trim($_POST['imie'] ?? '');
    $dane['nazwisko'] = trim($_POST['nazwisko'] ?? '');
    $dane['email']    = mb_strtolower(trim($_POST['email'] ?? ''));
    $dane['telefon']  = trim($_POST['telefon'] ?? '');
    $haslo  = $_POST['haslo'] ?? '';
    $haslo2 = $_POST['haslo2'] ?? '';

    $bledy = sprawdz_dane_osobowe($dane['imie'], $dane['nazwisko'], $dane['telefon']);

    if ($dane['email'] === '' || mb_strlen($dane['email']) > 100 || !filter_var($dane['email'], FILTER_VALIDATE_EMAIL)) {
        $bledy[] = 'Podaj poprawny adres e-mail.';
    }

    $blad_hasla = sprawdz_haslo($haslo);
    if ($blad_hasla !== '') {
        $bledy[] = $blad_hasla;
    } elseif ($haslo !== $haslo2) {
        $bledy[] = 'Hasła nie są takie same.';
    }

    if (empty($bledy)) {
        $st = $pdo->prepare('SELECT COUNT(*) FROM Uzytkownicy WHERE Email = ?');
        $st->execute([$dane['email']]);
        if ($st->fetchColumn() > 0) {
            $bledy[] = 'Konto z takim adresem e-mail już istnieje.';
        }
    }

    if (empty($bledy)) {
        try {
            $st = $pdo->prepare(
                'INSERT INTO Uzytkownicy (Imie, Nazwisko, Email, Telefon, Haslo, Rola, Aktywny, DataUtworzenia)
                 VALUES (?, ?, ?, ?, ?, ?, 1, ?)'
            );
            $st->execute([
                $dane['imie'], $dane['nazwisko'], $dane['email'], $dane['telefon'],
                password_hash($haslo, PASSWORD_DEFAULT), 'klient', date('Y-m-d H:i:s'),
            ]);
            ustaw_komunikat('success', 'Konto zostało utworzone. Możesz się teraz zalogować.');
            przekieruj('/public/logowanie.php');
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {
                $bledy[] = 'Konto z takim adresem e-mail już istnieje.';
            } else {
                error_log('Błąd rejestracji: ' . $ex->getMessage());
                $bledy[] = 'Nie udało się utworzyć konta. Spróbuj ponownie później.';
            }
        }
    }
}

$tytul = 'Rejestracja';
require __DIR__ . '/../includes/header.php';
?>

<main class="login-page">
    <section class="login-panel" aria-labelledby="rejestracja-tytul">
        <div class="login-intro">
            <p class="hero-eyebrow">Strefa klienta</p>
            <h1 id="rejestracja-tytul">Załóż konto</h1>
            <p>Po rejestracji możesz rezerwować pobyty i sprawdzać swoje rezerwacje.</p>
        </div>

        <?php if (!empty($bledy)): ?>
            <div class="form-message form-message-error" role="alert">
                <ul>
                    <?php foreach ($bledy as $blad): ?>
                        <li><?= e($blad) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form class="login-form" action="rejestracja.php" method="post">
            <?= csrf_pole() ?>

            <div class="login-field">
                <label for="imie">Imię</label>
                <input type="text" id="imie" name="imie" maxlength="50" required value="<?= e($dane['imie']) ?>">
            </div>

            <div class="login-field">
                <label for="nazwisko">Nazwisko</label>
                <input type="text" id="nazwisko" name="nazwisko" maxlength="50" required value="<?= e($dane['nazwisko']) ?>">
            </div>

            <div class="login-field">
                <label for="email">Adres e-mail</label>
                <input type="email" id="email" name="email" maxlength="100" autocomplete="email" required value="<?= e($dane['email']) ?>">
            </div>

            <div class="login-field">
                <label for="telefon">Telefon</label>
                <input type="tel" id="telefon" name="telefon" maxlength="20" autocomplete="tel" required value="<?= e($dane['telefon']) ?>">
            </div>

            <div class="login-field">
                <label for="haslo">Hasło</label>
                <input type="password" id="haslo" name="haslo" autocomplete="new-password" required>
                <span class="field-hint">Minimum 8 znaków, w tym litera i cyfra.</span>
            </div>

            <div class="login-field">
                <label for="haslo2">Powtórz hasło</label>
                <input type="password" id="haslo2" name="haslo2" autocomplete="new-password" required>
            </div>

            <button class="booking-button login-button" type="submit">Zarejestruj się</button>
        </form>

        <p class="login-note">Masz już konto? <a class="login-back-link" href="logowanie.php">Zaloguj się</a></p>
    </section>
</main>

<script src="<?= e(BASE_URL) ?>/js/formularze.js"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
