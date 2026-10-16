<?php
// Wspólne funkcje: sesja, logowanie, role, komunikaty, ochrona CSRF.
// Każda strona zaczyna się od:  require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/db.php';

date_default_timezone_set('Europe/Warsaw');

// ---------- SESJA ----------
if (session_status() === PHP_SESSION_NONE) {
    // httponly: JavaScript nie ma dostępu do ciasteczka sesji (ochrona przed kradzieżą przez XSS)
    // samesite: przeglądarka nie wysyła ciasteczka z innych stron (dodatkowa ochrona przed CSRF)
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'path' => '/']);
    session_start();
}

// Automatyczne wylogowanie po 30 minutach bezczynności
define('CZAS_SESJI', 1800);
if (isset($_SESSION['user_id'])) {
    if (time() - ($_SESSION['ostatnia_aktywnosc'] ?? time()) > CZAS_SESJI) {
        wyloguj_uzytkownika();
        ustaw_komunikat('info', 'Zostałeś wylogowany z powodu bezczynności. Zaloguj się ponownie.');
    } else {
        $_SESSION['ostatnia_aktywnosc'] = time();
    }
}

// ---------- FUNKCJE POMOCNICZE ----------

// Zabezpieczenie tekstu przed XSS - używaj ZAWSZE przy wypisywaniu danych w HTML
function e($tekst)
{
    return htmlspecialchars((string)$tekst, ENT_QUOTES, 'UTF-8');
}

function pole_tekstowe($nazwa)
{
    return isset($_POST[$nazwa]) && is_string($_POST[$nazwa]) ? $_POST[$nazwa] : '';
}

// Przekierowanie na inną stronę projektu, np. przekieruj('/public/logowanie.php')
function przekieruj($sciezka)
{
    header('Location: ' . BASE_URL . $sciezka);
    exit;
}

// Komunikaty "flash": ustawiamy je przed przekierowaniem, wyświetlamy raz na następnej stronie
function ustaw_komunikat($typ, $tekst)   // $typ: 'error', 'success' albo 'info'
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

// ---------- WALIDACJA (po stronie serwera; wspólna dla rejestracji, profilu i zmiany hasła) ----------

// Zwraca tekst błędu albo '' gdy hasło jest poprawne
function sprawdz_haslo($haslo)
{
    if (strlen($haslo) < 8) {
        return 'Hasło musi mieć co najmniej 8 znaków.';
    }
    if (strlen($haslo) > 72) {            // bcrypt bierze pod uwagę tylko 72 bajty
        return 'Hasło może mieć maksymalnie 72 znaki.';
    }
    if (!preg_match('/[A-Za-z]/', $haslo) || !preg_match('/[0-9]/', $haslo)) {
        return 'Hasło musi zawierać co najmniej jedną literę i jedną cyfrę.';
    }
    return '';
}

// Sprawdza imię, nazwisko i telefon. Zwraca tablicę błędów (pustą, gdy wszystko jest OK)
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

// ---------- CSRF (ochrona formularzy przed fałszywymi żądaniami z obcych stron) ----------
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Wstawiasz ją w każdym formularzu POST (w środku znaczników form)
function csrf_pole()
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

// Wywołujesz na początku obsługi każdego formularza POST
function csrf_sprawdz()
{
    $wyslany = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !is_string($wyslany) || !hash_equals($_SESSION['csrf_token'], $wyslany)) {
        http_response_code(400);
        die('Nieprawidłowe lub wygasłe żądanie formularza. Wróć do poprzedniej strony, odśwież ją i spróbuj ponownie.');
    }
}

// ---------- LOGOWANIE I ROLE ----------
function zalogowany()
{
    return isset($_SESSION['user_id']);
}

function wyloguj_uzytkownika()
{
    session_unset();
    session_destroy();
    session_start();               // nowa, pusta sesja (potrzebna choćby na komunikaty)
    session_regenerate_id(true);   // nowy identyfikator sesji
}

// Zwraca adres panelu zależnie od roli
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

// Strona dostępna tylko dla zalogowanych. Stan konta i rola są odświeżane
// podczas ładowania auth.php, także dla stron publicznych.
function wymagaj_logowania()
{
    if (!zalogowany()) {
        if (empty($_SESSION['komunikat'])) {
            ustaw_komunikat('error', 'Zaloguj się, aby zobaczyć tę stronę.');
        }
        przekieruj('/public/logowanie.php');
    }
}

// Odświeżamy rolę i stan konta także na stronach publicznych. Zablokowany
// użytkownik nie może widzieć formularza rezerwacji z dawnej sesji.
function odswiez_sesje_uzytkownika()
{
    global $pdo;
    if (!zalogowany()) {
        return;
    }
    $st = $pdo->prepare('SELECT Rola, Aktywny FROM Uzytkownicy WHERE ID_Uzytkownika = ?');
    $st->execute([$_SESSION['user_id']]);
    $u = $st->fetch();
    if (!$u || !$u['Aktywny']) {
        wyloguj_uzytkownika();
        ustaw_komunikat('error', 'Twoje konto jest nieaktywne.');
        przekieruj('/public/logowanie.php');
    }
    $_SESSION['rola'] = $u['Rola'];   // rola zawsze świeża z bazy
}

// Strona dostępna tylko dla podanych ról, np. wymagaj_roli('admin') albo wymagaj_roli('admin', 'pracownik')
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
