<?php
require_once __DIR__ . '/../includes/auth.php';
wymagaj_logowania();         

$bledy = [];


$st = $pdo->prepare('SELECT Imie, Nazwisko, Email, Telefon, Rola, DataUtworzenia FROM Uzytkownicy WHERE ID_Uzytkownika = ?');
$st->execute([$_SESSION['user_id']]);
$u = $st->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_sprawdz();

    $imie     = trim($_POST['imie'] ?? '');
    $nazwisko = trim($_POST['nazwisko'] ?? '');
    $telefon  = trim($_POST['telefon'] ?? '');

    $bledy = sprawdz_dane_osobowe($imie, $nazwisko, $telefon);

    if (empty($bledy)) {
        $st = $pdo->prepare('UPDATE Uzytkownicy SET Imie = ?, Nazwisko = ?, Telefon = ? WHERE ID_Uzytkownika = ?');
        $st->execute([$imie, $nazwisko, $telefon, $_SESSION['user_id']]);
        $_SESSION['imie'] = $imie;
        ustaw_komunikat('success', 'Dane zostały zapisane.');
        przekieruj('/public/profil.php');    
    }

    $u['Imie'] = $imie;
    $u['Nazwisko'] = $nazwisko;
    $u['Telefon'] = $telefon;
}

$tytul = 'Mój profil';
require __DIR__ . '/../includes/header.php';
?>

<main class="login-page">
    <section class="login-panel login-panel-wide" aria-labelledby="profil-tytul">
        <h1 id="profil-tytul">Mój profil</h1>

        <?php pokaz_komunikat(); ?>

        <?php if (!empty($bledy)): ?>
            <div class="form-message form-message-error" role="alert">
                <ul>
                    <?php foreach ($bledy as $blad): ?>
                        <li><?= e($blad) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form class="login-form" action="profil.php" method="post">
            <?= csrf_pole() ?>

            <div class="login-field">
                <label for="email">Adres e-mail</label>
                <input type="email" id="email" value="<?= e($u['Email']) ?>" readonly>
                <span class="field-hint">Adresu e-mail nie można zmienić. Rola konta: <?= e($u['Rola']) ?>.</span>
            </div>

            <div class="login-field">
                <label for="imie">Imię</label>
                <input type="text" id="imie" name="imie" maxlength="50" required value="<?= e($u['Imie']) ?>">
            </div>

            <div class="login-field">
                <label for="nazwisko">Nazwisko</label>
                <input type="text" id="nazwisko" name="nazwisko" maxlength="50" required value="<?= e($u['Nazwisko']) ?>">
            </div>

            <div class="login-field">
                <label for="telefon">Telefon</label>
                <input type="tel" id="telefon" name="telefon" maxlength="20" required value="<?= e($u['Telefon']) ?>">
            </div>

            <button class="booking-button login-button" type="submit">Zapisz zmiany</button>
        </form>

        <p class="login-note">Konto utworzone: <?= e($u['DataUtworzenia']) ?></p>
        <a class="login-back-link" href="zmiana_hasla.php">Zmień hasło</a>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
