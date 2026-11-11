<?php



require_once __DIR__ . '/db.php';

date_default_timezone_set('Europe/Warsaw');


if (session_status() === PHP_SESSION_NONE) {
    
    
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    $https = !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off';
    $secure = defined('SESSION_SECURE') ? SESSION_SECURE : $https;
    session_set_cookie_params(['httponly' => true, 'secure' => $secure, 'samesite' => 'Lax', 'path' => '/']);
    session_start();
}


define('CZAS_SESJI', 1800);
if (isset($_SESSION['user_id'])) {
    if (time() - ($_SESSION['ostatnia_aktywnosc'] ?? time()) > CZAS_SESJI) {
        wyloguj_uzytkownika();
        ustaw_komunikat('info', 'Zostałeś wylogowany z powodu bezczynności. Zaloguj się ponownie.');
    } else {
        $_SESSION['ostatnia_aktywnosc'] = time();
    }
}




function e($tekst)
{
    return htmlspecialchars((string)$tekst, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function pole_tekstowe($nazwa)
{
    return isset($_POST[$nazwa]) && is_string($_POST[$nazwa]) ? $_POST[$nazwa] : '';
}


function przekieruj($sciezka)
{
    header('Location: ' . BASE_URL . $sciezka);
    exit;
}


function ustaw_komunikat($typ, $tekst)   
{
    $_SESSION['komunikat'] = [$typ, $tekst];
}

function pokaz_komunikat()
{
    if (!empty($_SESSION['komunikat'])) {
        [$typ, $tekst] = $_SESSION['komunikat'];
        unset($_SESSION['komunikat']);
        echo '<p class="form-message form-message-' . e($typ) . '" role="alert">' . e($tekst) . '</p>';
    }
}




function sprawdz_haslo($haslo)
{
    if (str_contains($haslo, "\0")) {
        return 'Hasło zawiera niedozwolony znak.';
    }
    if (strlen($haslo) < 8) {
        return 'Hasło musi mieć co najmniej 8 znaków.';
    }
    if (strlen($haslo) > 72) {            
        return 'Hasło może mieć maksymalnie 72 bajty (polskie znaki mogą zajmować więcej niż jeden bajt).';
    }
    if (!preg_match('/[A-Za-z]/', $haslo) || !preg_match('/[0-9]/', $haslo)) {
        return 'Hasło musi zawierać co najmniej jedną literę i jedną cyfrę.';
    }
    return '';
}


function sprawdz_dane_osobowe($imie, $nazwisko, $telefon)
{
    $bledy = [];
    if ($imie === '' || mb_strlen($imie) > 50) {
        $bledy[] = 'Podaj imię (maksymalnie 50 znaków).';
    }
    if ($nazwisko === '' || mb_strlen($nazwisko) > 50) {
        $bledy[] = 'Podaj nazwisko (maksymalnie 50 znaków).';
    }
    $cyfry = preg_replace('/\D/', '', $telefon);
    if (!preg_match('/^\+?[0-9][0-9 -]{8,19}$/', $telefon) || strlen($cyfry) < 9 || strlen($cyfry) > 15) {
        $bledy[] = 'Podaj poprawny numer telefonu (9-15 cyfr; dozwolone spacje i myślniki).';
    }
    return $bledy;
}


function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}


function csrf_pole()
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}


function csrf_sprawdz()
{
    $wyslany = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !is_string($wyslany) || !hash_equals($_SESSION['csrf_token'], $wyslany)) {
        http_response_code(400);
        die('Nieprawidłowe lub wygasłe żądanie formularza. Wróć do poprzedniej strony, odśwież ją i spróbuj ponownie.');
    }
}


function zalogowany()
{
    return isset($_SESSION['user_id']);
}

function wyloguj_uzytkownika()
{
    session_unset();
    session_destroy();
    session_start();               
    session_regenerate_id(true);   
}


function panel_roli($rola)
{
    if ($rola === 'admin') {
        return '/public/admin/index.php';
    }
    if ($rola === 'pracownik') {
        return '/public/pracownik/index.php';
    }
    return '/public/klient/index.php';
}



function wymagaj_logowania()
{
    if (!zalogowany()) {
        if (empty($_SESSION['komunikat'])) {
            ustaw_komunikat('error', 'Zaloguj się, aby zobaczyć tę stronę.');
        }
        przekieruj('/public/logowanie.php');
    }
}



function odswiez_sesje_uzytkownika()
{
    global $pdo;
    if (!zalogowany()) {
        return;
    }
    $st = $pdo->prepare('SELECT Rola, Aktywny, Imie FROM Uzytkownicy WHERE ID_Uzytkownika = ?');
    $st->execute([$_SESSION['user_id']]);
    $u = $st->fetch();
    if (!$u || !$u['Aktywny']) {
        wyloguj_uzytkownika();
        ustaw_komunikat('error', 'Twoje konto jest nieaktywne.');
        przekieruj('/public/logowanie.php');
    }
    $_SESSION['imie'] = $u['Imie'];
    $_SESSION['rola'] = $u['Rola'];   
}


function wymagaj_roli(...$dozwolone)
{
    wymagaj_logowania();
    if (!in_array($_SESSION['rola'], $dozwolone, true)) {
        http_response_code(403);
        $tytul = 'Brak dostępu';
        require __DIR__ . '/header.php';
        echo '<main class="login-page"><section class="login-panel">'
           . '<h1>Brak dostępu</h1>'
           . '<p>Nie masz uprawnień do tej strony.</p>'
           . '<a class="login-back-link" href="' . e(BASE_URL . panel_roli($_SESSION['rola'])) . '">Wróć do swojego panelu</a>'
           . '</section></main>';
        require __DIR__ . '/footer.php';
        exit;
    }
}

odswiez_sesje_uzytkownika();
