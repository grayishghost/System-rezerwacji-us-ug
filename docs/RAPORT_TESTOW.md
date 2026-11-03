# Rzeczywisty raport V2 — 2 listopada 2026

Testy wykonał asystent na lokalnych, odizolowanych bazach. Nie jest to stwierdzenie, że kod został już sprawdzony na Waszym hostingu lub że nauczyciel zatwierdził ocenę.

## Środowisko

PHP 8.4.27, MariaDB 11.8.6, PDO MySQL, mbstring; Python/requests/PyMySQL/BeautifulSoup; Chromium/Playwright. Serwer PHP z routerem, wersja V2. Punkt wyjścia kodu: `ea6f2c2388a0394a35ab6dd66780f98995838222`.

## Wyniki

| Sprawdzenie | Wynik |
|---|---|
| Składnia całego połączonego projektu, z lokalnym configiem | **37 plików PHP bez błędów składni**. |
| Migracja bazowego etapu 2 → V2 | 003 i 004 wykonane poprawnie. |
| Aktualizacja pierwszej paczki etapu 3 → V2 | 003 pominięta, 004 wykonana; zachowane 6 kont, 2 usługi i 1 istniejący pobyt. |
| Ponowne uruchomienie migratora | Obie migracje rozpoznane jako już zainstalowane. |
| Pełny database.sql do pustej bazy | Poprawny import: 16 tabel, obie migracje i fikcyjne wizyty startowe. |
| Kontrola konfiguracji/schematu | `scripts/sprawdz_instalacje.php`: wszystkie kontrole OK. |
| Zestaw HTTP/SQL admina, `etap3_integration.py` | **91/91 PASS**. |
| Etapy 1–2 i wizyty, `etapy12_i_wizyty.py` | **51/51 PASS**. |
| Łącznie dwa zestawy HTTP/SQL | **142/142 asercje PASS**. |
| Chromium, 11 stron × 2 szerokości | **22 widoki** (1440 i 390 px): status 200, brak globalnego poziomego przepełnienia. |
| JS w sprawdzanych widokach | Brak zgłoszonych błędów; dialog potwierdzenia działa i można go anulować. |
| Dane pokazowe wizyt | Dwie wizyty dodane; drugie wywołanie nie dubluje danych. |

Surowe wyjścia dwóch zestawów testowych: `tests/wynik_dostarczony.txt`.

## Co potwierdzają testy

- Dostęp gościa/klienta/pracownika do endpointów admina, także nowego terminarza i eksportów.
- CRUD kategorii/usług, N:M, profil pracownika, grafik, urlopy, zależności i rollback.
- Rejestrację, różne hasła, zły e-mail, unikalność e-maila, brak samodzielnego nadania admina.
- Hash hasła, zmianę hasła z poprawnym starym, regenerację sesji, cookie HttpOnly/SameSite.
- Ochronę profilu przed podmianą ID i XSS; wylogowanie POST, limit prób, błędne długie dane i NUL.
- Trzy bezpośrednie FK wizyty, usunięcie zdublowanej kategorii, indeksy i odmowy CHECK/FK.
- Dodawanie wizyty na godziny, czas z cennika, cenę historyczną, konflikty i styk terminów, urlop, grafik i kompetencje.
- Zmianę statusu/wersji, ochronę starej karty, filtrowany CSV i historię; blokadę usunięcia usług/pracowników z wizytami.
- Zachowane testy pobytów, filtrów, CSV/formuł, CSRF i blokowania kont.

## Granice

Nie jest to audyt bezpieczeństwa ani test obciążeniowy. Kontrola starej wersji nie zastępuje testu setek równoległych zapisów. Obsługę równoległego tworzenia przewidziano przez transakcyjną blokadę rekordu pracownika, ale nie wykonano osobnego testu obciążenia wieloprocesowego.

Wykonano testy na wskazanych wersjach PHP/MariaDB, nie na każdej wspieranej wersji. Nie sprawdzono docelowego Apache/nginx ani obecnego hostingu zespołu. Klientowy terminarz i pełny panel wizyt pracownika to zakres kolejnego etapu.

Wynik testów potwierdza konkretny zakres funkcji na tym środowisku; **nie jest gwarancją oceny 6** ani automatycznym podniesieniem wcześniejszych ocen.

## Wasz wynik po wdrożeniu

- Data/osoba: ................................
- Lokalny XAMPP lub adres hostingu: ................................
- PHP/MySQL, BASE_URL: ................................
- Wynik obu zestawów testowych: ................................
- Poprawki/commit: ................................
