<?php
require_once __DIR__ . '/../includes/auth.php';
wymagaj_logowania();

$bledy = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_sprawdz();

    $stare = $_POST['stare_haslo'] ?? '';
    $nowe  = $_POST['nowe_haslo'] ?? '';
    $nowe2 = $_POST['nowe_haslo2'] ?? '';

    $st = $pdo->prepare('SELECT Haslo FROM Uzytkownicy WHERE ID_Uzytkownika = ?');
    $st->execute([$_SESSION['user_id']]);
    $hash = $st->fetchColumn();

    if (!$hash || !password_verify($stare, $hash)) {
        $bledy[] = 'Aktualne hasło jest nieprawidłowe.';
    }

    $blad_hasla = sprawdz_haslo($nowe);
    if ($blad_hasla !== '') {
        $bledy[] = $blad_hasla;
    } elseif ($nowe !== $nowe2) {
        $bledy[] = 'Nowe hasła nie są takie same.';
    } elseif ($nowe === $stare) {
        $bledy[] = 'Nowe hasło musi się różnić od aktualnego.';
    }

    if (empty($bledy)) {
        $st = $pdo->prepare('UPDATE Uzytkownicy SET Haslo = ? WHERE ID_Uzytkownika = ?');
        $st->execute([password_hash($nowe, PASSWORD_DEFAULT), $_SESSION['user_id']]);
        session_regenerate_id(true);
        ustaw_komunikat('success', 'Hasło zostało zmienione.');
        przekieruj('/public/profil.php');
    }
}

$tytul = 'Zmiana hasła';
require __DIR__ . '/../includes/header.php';
?>

<main class="login-page">
    <section class="login-panel" aria-labelledby="haslo-tytul">
        <h1 id="haslo-tytul">Zmiana hasła</h1>

        <?php if (!empty($bledy)): ?>
            <div class="form-message form-message-error" role="alert">
                <ul>
                    <?php foreach ($bledy as $blad): ?>
                        <li><?= e($blad) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form class="login-form" action="zmiana_hasla.php" method="post">
            <?= csrf_pole() ?>

            <div class="login-field">
                <label for="stare_haslo">Aktualne hasło</label>
                <input type="password" id="stare_haslo" name="stare_haslo" autocomplete="current-password" required>
            </div>

            <div class="login-field">
                <label for="nowe_haslo">Nowe hasło</label>
                <input type="password" id="nowe_haslo" name="nowe_haslo" autocomplete="new-password" required>
                <span class="field-hint">Minimum 8 znaków, w tym litera i cyfra.</span>
            </div>

            <div class="login-field">
                <label for="nowe_haslo2">Powtórz nowe hasło</label>
                <input type="password" id="nowe_haslo2" name="nowe_haslo2" autocomplete="new-password" required>
            </div>

            <button class="booking-button login-button" type="submit">Zmień hasło</button>
        </form>

        <a class="login-back-link" href="profil.php">Wróć do profilu</a>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
