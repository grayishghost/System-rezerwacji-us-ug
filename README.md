# Grand Access Hotel — etap 5, GAH-ETAP5-008-r1

Autorzy projektu: Oliwia Drygiel, Tobiasz Mućko, Dawid Domagała. Trzyosobowy skład został zaakceptowany przez nauczyciela.

PHP, HTML5, CSS3, JavaScript, PDO oraz MySQL/MariaDB. Wspólne repozytorium: https://github.com/grayishghost/System-rezerwacji-us-ug. Bazą etapu 5 jest działający kod 008 z etapu 4. Odczyt main przy porównaniu: **84cdee975a4f34007250fd1e6135e40ac2ebe9b0**. Kod runtime odpowiadał 008, ale brakowało ETAP4_TESTY.md, ETAP4_WYMAGANIA.md i database/PRZED_008_ODCZYT.sql; dokumenty oraz świeży SQL miały częściowo treści 007. Ten komplet przywraca brakujące pliki i spójne odtwarzanie 008. Nie wykonano push ani uploadu za zespół.

## Zakres etapu 5

Wymagania: strony 18–19 i lista funkcji ze strony 21 PDF. Termin 27.11.2026. Plan pracy zespołu na 24–26.11.2026 (przyjęte trzy dni): ODBIOR_I_PODZIAL.md. Zgodnie z przekazaną decyzją zakres nie obejmuje prezentacji ani obrony.

- Globalne nagłówki: CSP z script-src self bez inline/eval, nosniff, Referrer-Policy, Permissions-Policy i no-store. Skrypt formularza pobytu przeniesiono do js/strona.js. style-src dopuszcza inline ze względu na istniejący panel; nie reklamujemy bezwzględnego zakazu wszystkich stylów inline.
- Wspólna ścisła walidacja dat dla pobytów, administratora i terminarza; poprawne odrzucanie NUL i nieistniejących dat zamiast błędu 500. Wspólna funkcja kolizji półotwartych przedziałów.
- Prywatny eksport aktualnej własnej/przypisanej wizyty do ICS, z kontrolą własności i bez danych kontaktowych oraz uwag klienta: KALENDARZ_ICS.md. To pobranie kopii, nie automatyczna synchronizacja.
- Weryfikacja struktury i zbiorczego hasha manifestu oraz plików; testy CLI bez wpisanych haseł serwera; bieżąca instrukcja startu, testów i hostingu.
- Bez zmiany schematu bazy. Wydanie aplikacji zmienia się na GAH-ETAP5-008-r1; ostatni marker pozostaje 008_terminarz. Nie ma migracji 009.

Mapa punktów **2 → 3 → 4 → 5 → 6**: ETAP5_WYMAGANIA.md. Wyniki i ograniczenia: ETAP5_TESTY.md. Dokumenty ETAP4_* są archiwalnym odniesieniem, nie bieżącym raportem etapu 5.

## Co realizuje etap 4

Podstawą są strony 16–17 dokumentu Projekt_semestralny_System_rezerwacji_uslug.pdf oraz wymagania wspólne ze stron 6–8. Wymagania są narastające: 2 → 3 → 4 → 5 → 6. Dokładna mapa punktów: ETAP4_WYMAGANIA.md. Odbiór i testy: ETAP4_TESTY.md oraz ODBIOR_I_PODZIAL.md.

Wizyty usług na godziny są niezależne od pobytów hotelowych. Rezerwacja pokoju nie zastępuje procesu usługa → pracownik → data → godzina → potwierdzenie.

### Klient

- Publiczna oferta aktywnych usług, kategorie, filtrowanie i szczegóły: nazwa, opis, kategoria, cena, czas i dostępność.
- Wybór wyłącznie aktywnych pracowników wykonujących usługę, daty i automatycznych wolnych godzin.
- Podsumowanie przed zapisem; cena, czas i właściciel ustalane przez serwer, nie z podmienionych pól żądania.
- Moje konto i profil, własne wizyty dzisiejsze, przyszłe i historia/anulowane; szczegóły usługi, pracownika, terminu, ceny i statusu.
- Anulowanie własnej przyszłej aktywnej wizyty; przełożenie na wolny termin z zachowaniem usługi, pracownika, historycznej ceny i czasu.
- Kalendarz miesięczny własnych wizyt oraz alternatywna lista; historia zmian statusów i terminów.
- Dotychczasowa oferta pokoi, rezerwacje pobytów i ich lista pozostają dostępne.

### Pracownik

Panel wizyt na dziś, przyszłych i historii. Szczegóły własnej wizyty, niezbędne dane klienta i zmiana statusu. Nie ma dostępu do cudzych wizyt ani operacji innego pracownika. Nieaktywny profil nie uprawnia do obsługi wizyt. Ukończenie jest możliwe po końcu wizyty, statusy końcowe są nieodwracalne.

### Administrator i wcześniejsze etapy

Zachowano konta i profile, stanowiska, kompetencje, kategorie w Usługach, typy i ilustracje pokoi, grafik/urlopy, dostępność, pobyty i archiwum, bezpieczne usuwanie, log, CSV, kalendarz, pulpit i kontrolę instalacji. Tworzenie wizyt i zmiany statusu administratora korzystają teraz ze wspólnej logiki oraz historii wizyt.

## Kilka znaczących rozszerzeń etapu 4

1. AJAX — wolne godziny aktualizują się bez przeładowania po wyborze daty. Podstawowy proces działa także bez JavaScript.
2. Automatyczne wolne godziny co 15 minut, z uwzględnieniem czasu usługi i istniejących przedziałów.
3. Integracja różnych godzin pracy, urlopów i dni wolnych z grafikiem administratora.
4. Graficzny kalendarz miesięczny klienta z listą dostępną na telefonie.
5. Przełożenie własnej przyszłej wizyty z ponowną kontrolą i zachowaniem warunków historycznych.
6. Wspólna historia statusów i terminów, również po operacjach administratora/pracownika.

Nie implementowano wysyłki e-maili, SMS, przypomnień ani osobnego modelu przerw. To przykłady z listy rozszerzeń, nie deklarowane funkcje. Dodatki nie zastępują obowiązkowych punktów ocen 2–5.

## Integralność i bezpieczeństwo

18 tabel, 21 FK, 44 CHECK. HistoriaWizyt ma dodatkowo unikalne (ID_Wizyty,Wersja). Prepared statements, escaping HTML, CSRF, sprawdzanie ról i właściciela po stronie serwera, kontrola wersji edycji oraz odświeżanie sesji. Hasła są hashowane; reset unieważnia wcześniejsze sesje.

Zapis wizyty i historii jest transakcyjny. Nadrzędny rekord pracownika jest blokowany FOR UPDATE; po blokadzie serwer ponownie kontroluje usługę, kompetencje, grafik, czas oraz wszystkie konflikty. Dwie równoległe próby tego samego terminu dają jeden zapis. Wybór i podsumowanie nie są rezerwacją wstępną. Jednorazowy token potwierdzenia ważny 15 minut zapobiega ponowieniu zapisu; zmiana ceny/czasu oferty wymaga nowego podsumowania.

PHP i sesja bazy używają czasu lokalnego Europe/Warsaw; offset jest ustalany na każde żądanie bez zależności od dostępności nazwanych stref MySQL. Daty wizyt są lokalnymi DATETIME. Aplikacja nie obsługuje wielostrefowych rezerwacji; plik ICS zamienia zapisany czas Warszawy na UTC.

Szczegółowy zakres: INTEGRALNOSC.md. Reguły kolizji między rekordami są egzekwowane w aplikacji; dowolny bezpośredni SQL może je ominąć. Historia nie jest kryptograficznie niezmiennym rejestrem. Migracja nie odtwarza nieznanej dawnej historii — oznacza stan początkowy.

## Uruchomienie

Wymagania: PHP 8.1+ z pdo_mysql i mbstring, MySQL 8.0.16+ lub współczesna MariaDB z egzekwowanym CHECK. GD/fileinfo są potrzebne do uploadu ilustracji. Apache 2.4 z .htaccess na hostingu; lokalnie można użyć router.php, ale wbudowany serwer PHP nie zastępuje ochrony .htaccess.

1. Pusta fikcyjna baza: import database/nowa_baza_INFINITYFREE.sql. Ten plik kasuje tabele i nie służy do aktualizacji istniejących danych. database/database.sql również odtwarza całość i wybiera lokalną grand_access_hotel.
2. Istniejące poprawne 008: backup, aktualizacja wyłącznie plików, bez importu SQL i bez dopisywania markerów. Dla starszej/innej bazy zatrzymaj się i ustal rzeczywisty stan — etap 5 nie jest instrukcją ponowienia 008. Szczegóły: WDROZENIE_INFINITYFREE.md.
3. Utwórz prywatny includes/config.php z config.example.php albo zachowaj hostingowy. Nie publikuj konfiguracji. Dla domeny bez podkatalogu BASE_URL jest pustym ciągiem.
4. Lokalnie uruchom php -S 0.0.0.0:8000 router.php, na hostingu rozpakowane pliki trafiają bezpośrednio do htdocs domeny.
5. Sprawdź /public/admin/instalacja.php, ofertę /public/uslugi.php, panel klienta i pracownika.

Świeża fikcyjna baza zawiera grafik kolejnych 14 dni dla demonstracyjnych pracowników: zmienne godziny, weekendy wolne i przykładowy urlop. Nie nadpisuje się istniejącego dnia. Ten grafik nie jest automatycznie dodawany przy migracji hostingu. Na istniejącym hostingu administrator powinien ustalić prawdziwy grafik i kompetencje. DEMO_TERMINARZ.sql jest wyłącznie do fikcyjnego demo, nie do bezmyślnego importu na istniejące dane.

## Konta fikcyjnego demo

| Rola | Login | Hasło |
|---|---|---|
| Administrator | admin@hotel.pl | Admin123! |
| Pracownik | john.doe@email.com | Pracownik123! |
| Pracownik | will.dafie@email.com | Pracownik123! |
| Klient | anna.kowal@dk.pl | Klient123! |

Publiczne hasła demonstracyjne zmień po próbach. Produkcyjnych haseł, eksportów ani konfiguracji nie umieszczaj w repozytorium. .gitignore nie usuwa sekretów z historii Git.

## Pliki i utrzymanie

public — strony, includes — wspólna logika, css/js/assets — zasoby, database — schemat/migracje/demo/ERD, scripts — CLI. Kod aplikacji jest bez komentarzy, wyjaśnienia są w dokumentacji. Zachowano obowiązkową licencję assets/fonts/OFL.txt.

Dynamiczne ilustracje i config.php zachowujesz w backupie i przy aktualizacjach. Nie należą do stałego manifestu. Manifest sprawdza wymienione pliki, nie wszystkie dodatkowe pliki serwera. GitHub i hosting aktualizuje się osobno. Lokalny raport nie zastępuje odbioru hostingu ani nie gwarantuje oceny.

## Testy możliwe do powtórzenia

W katalogu projektu, terminal lokalny (nie adres w przeglądarce):

```text
php scripts/test_regul.php
php scripts/test_jednostkowe.php
php scripts/test_manifestu.php
python3 scripts/test_http.py --url http://127.0.0.1:8000
php scripts/test_instalacji.php
```

Pierwsze trzy nie potrzebują bazy ani haseł i nie zmieniają projektu. HTTP odczytuje pięć publicznych stron; na hostingu antybot może zamiast aplikacji zwrócić wyzwanie JavaScript — wtedy nie uznawaj wyniku za zaliczenie. Ostatni test czyta bazę przez prywatny includes/config.php i sprawdza schemat, pliki oraz 29 reguł danych; uruchamiaj wyłącznie w bezpiecznym terminalu. InfinityFree nie musi mieć SSH/CLI: odpowiednikiem odczytu instalacji jest panel administratora /public/admin/instalacja.php. Nie wystawiaj skryptów CLI jako stron. Apache blokuje scripts przez .htaccess, a skrypty PHP dodatkowo sprawdzają PHP_SAPI.

Pełniejsze testy HTTP/SQL i Chromium są w osobnym folderze testy głównej paczki, poza publicznym projektem. Tworzą fikcyjne dane; wyłącznie dedykowana baza z końcówką _test i jawne TEST_ALLOW_WRITE=YES. Nie uruchamiaj ich na InfinityFree ani na danych użytkowników. Instrukcja w testy/URUCHOMIENIE.md. Raport nie potwierdza zdalnego odbioru i nie gwarantuje stopnia.
