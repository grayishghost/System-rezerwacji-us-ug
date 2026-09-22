<?php
$blad = '';
$zalogowano = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $haslo = $_POST['haslo'] ?? '';

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $blad = 'Podaj poprawny adres e-mail.';
    } elseif ($haslo === '') {
        $blad = 'Podaj hasło.';
    } else {
        $blad = 'Logowanie będzie dostępne po skonfigurowaniu konta.';
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie | Hotel</title>
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Faculty+Glyphic&display=swap" rel="stylesheet">
</head>
<body>
<header class="header">
    <div class="logo">
        <img src="../assets/logo.png" alt="Grand Access Hotel">
    </div>
    <nav class="nav">
        <a href="index.html">Strona główna</a>
        <a href="pokoje.html">Pokoje</a>
        <a href="logowanie.php" aria-current="page">Logowanie</a>
    </nav>
</header>

<main class="login-page">
    <section class="login-panel" aria-labelledby="login-title">
        <div class="login-intro">
            <p class="hero-eyebrow">Strefa klienta</p>
            <h1 id="login-title">Witaj ponownie</h1>
            <p>Zaloguj się, aby sprawdzić swoje rezerwacje i zaplanować chwilę relaksu.</p>
        </div>

        <?php if ($blad !== ''): ?>
            <p class="form-message form-message-error" role="alert">
                <?= htmlspecialchars($blad, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <form class="login-form" action="logowanie.php" method="post">
            <div class="login-field">
                <label for="email">Adres e-mail</label>
                <input type="email" id="email" name="email" autocomplete="email" required value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="login-field">
                <label for="haslo">Hasło</label>
                <input type="password" id="haslo" name="haslo" autocomplete="current-password" required>
            </div>

            <button class="booking-button login-button" type="submit">Zaloguj się</button>
        </form>

        <p class="login-note">Konto administratora, pracownika i klienta będzie obsługiwane przez jeden formularz.</p>
        <a class="login-back-link" href="index.html">Wróć na stronę główną</a>
    </section>
</main>

<footer class="footer">
    <p>&copy; 2026 Hotel. Wszelkie prawa zastrzeżone.</p>
</footer>
</body>
</html>
