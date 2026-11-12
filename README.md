# Grand Access Hotel — wydanie GAH-FULL-007-r1

Autorzy projektu: Oliwia Drygiel, Tobiasz Mućko, Dawid Domagała.

Kompletny projekt rezerwacji hotelowych w PHP i MySQL/MariaDB. To rozwinięcie wydania 006, nie potwierdzenie automatycznego wdrożenia repozytorium na hosting. Kod aplikacji jest bez komentarzy; objaśnienia znajdują się w dokumentacji.

## Panel administratora

- Kolory zgodne ze stroną hotelu: granat #1d3045, jasne tło #edf0ed i złote akcenty. Logo w lewym górnym rogu otwiera stronę hotelu.
- Konta: dodawanie, edycja danych/e-maila/roli, ustawienie nowego hasła, blokowanie i bezpieczne usuwanie klientów, pracowników oraz administratorów. Konto pracownika automatycznie otrzymuje profil. Konto z historią pozostaje do dezaktywacji. Własnego konta nie można usunąć, zablokować ani pozbawić roli admina. Reset hasła unieważnia wcześniejsze sesje.
- Pracownicy: profile, stanowiska, kompetencje, aktywność i powiązane konta. Kompetencje i CRUD stanowisk są sekcjami modułu Pracownicy, nie oddzielnymi pozycjami głównego menu. Używanego stanowiska nie usuwamy bez zmiany przypisań.
- Usługi: CRUD usług i kategorii w jednym module. Kategorii z usługami nie można skasować bez przeniesienia usług.
- Pokoje: CRUD, publikowanie/wycofanie, serwis, wybór ilustracji, przesyłanie nowych JPG/PNG/WEBP. Typy pokoi mają własną sekcję CRUD wewnątrz Pokoi. Używanego typu nie usuwamy bez zmiany przypisań.
- Pobyty: tworzenie, wyszukiwanie, szczegóły, status/opiekun, edycja dat/pokoju/liczby osób przed rozpoczęciem, edycja uwag także dla historii. Dla zmiany terminu/pokoju można przeliczyć kwotę albo zachować uzgodnioną kwotę z uzasadnieniem. Opłacony pobyt wymaga zachowania zapisanej kwoty; edytor nie rozlicza dopłat ani zwrotów.
- Archiwizacja pobytu i przywracanie z archiwum. Archiwizacja nie anuluje pobytu ani nie zwalnia pokoju. Trwałe usuwanie tylko anulowanego pobytu bez płatności i zamówień, z pozostawieniem wpisu w historii.
- Dostępność pokoi i pracowników, blokady terminów, grafiki/urlopy, wizyty usług, kalendarz, historia operacji, CSV, statystyki i spójna nawigacja.
- Kontrola instalacji: schemat, markery migracji, sumy plików, odczyt danych i warunki przesyłania ilustracji.

Starsze adresy kategorie.php i przypisania.php pozostają zgodne z wcześniejszymi linkami, ale prowadzą do sekcji właściwych modułów.

## Integralność i bezpieczeństwo

17 tabel, 19 FK, 42 CHECK. Klucze unikalne chronią m.in. e-maile, numery pokoi, nazwy kategorii, stanowisk i typów pokoi. Płatności i zamówienia mają RESTRICT wobec usunięcia pobytu. SQL i PHP pełnią różne role: kolizji przedziałów, pojemności oraz kompetencji pilnuje aplikacja w transakcjach. Dowolny bezpośredni SQL może ominąć te reguły aplikacji — szczegółowy zakres i ograniczenia opisuje INTEGRALNOSC.md.

Zapisy używają prepared statements, CSRF, kontroli ról, transakcji, blokad rekordów i kontroli wersji edycji. Hasła są hashowane i nie są zapisywane w logu operacji. Ilustracje są dekodowane przez GD i zapisywane ponownie jako JPEG z losową nazwą, bez kopiowania metadanych.

## Wymagania

PHP 8.1+ z pdo_mysql i mbstring; dla przesyłania nowych ilustracji także GD i fileinfo. MySQL 8.0.16+ albo współczesna MariaDB obsługująca CHECK. Apache 2.4 z .htaccess lub lokalny router PHP. Testy wykonano lokalnie na PHP 8.4 i MariaDB 11.8; wynik hostingu trzeba sprawdzić po wdrożeniu.

Limity ilustracji: do 5 MB, 6000 px na bok i 8 mln pikseli; zapis JPEG do 1600 px. Limit pliku i żądania w PHP hostingu może być niższy. Brak GD/fileinfo blokuje upload, ale nie wybór istniejącej ilustracji. Katalog assets/pokoje musi być zapisywalny dla PHP. Nie ustawiaj 777.

## Uruchomienie i aktualizacja

- Istniejące wydanie 006: backup, odczyt PRZED_007_ODCZYT.sql, migracja 007_panel.sql i marker dopiero po pełnym sukcesie. Nie uruchamiaj ponownie 003–006.
- Starsza baza: ustal stan i wykonaj wyłącznie brakujące migracje 003–007. Procedura: WDROZENIE_INFINITYFREE.md.
- Pusta baza: pełne odtworzenie database/nowa_baza_INFINITYFREE.sql. Ten plik kasuje tabele — nie jest aktualizacją istniejących danych.
- database/database.sql również odtwarza dane i dodatkowo wybiera lokalną bazę grand_access_hotel.
- Prywatny includes/config.php tworzysz z config.example.php albo zachowujesz z hostingu. Nie dodawaj go do repozytorium.
- Dla domeny bez podkatalogu BASE_URL jest pustym ciągiem. Lokalnie: php -S 0.0.0.0:8000 router.php.
- Sprawdź /public/admin/instalacja.php, następnie jawny protokół ODBIOR_I_PODZIAL.md.

Pliki przesłane przez formularz do assets/pokoje nie są częścią stałego manifestu ani automatyczną zawartością repozytorium. Zachowaj je i prywatny config.php podczas uploadu kolejnego wydania oraz w backupach hostingu. Nie zastępuj całego katalogu tak, by skasować istniejące ilustracje.

## Demonstracyjne konta fikcyjnej bazy

| Rola | Login | Hasło |
|---|---|---|
| Administrator | admin@hotel.pl | Admin123! |
| Pracownik | john.doe@email.com | Pracownik123! |
| Klient | anna.kowal@dk.pl | Klient123! |

Po testach zmień publicznie znane hasła administratorów. Nie publikuj danych dostępowych do bazy ani jej produkcyjnych eksportów. .gitignore nie usuwa wcześniej opublikowanych sekretów z historii Git.

## Pliki i dokumentacja

public/ — strony, includes/ — wspólna logika, database/ — migracje/schemat/diagram, css/js/assets — zasoby, scripts/ — narzędzia CLI. .htaccess chronią katalogi prywatne i katalog ilustracji. Licencja lokalnych fontów: assets/fonts/OFL.txt.

GitHub i hosting wymagają osobnego uploadu oraz kontroli tego samego wydania. Manifest sprawdza wymienione pliki, nie dowolne pliki dodatkowe i nie zawartość dynamicznych ilustracji. Raport testów opisuje faktycznie wykonane sprawdzenia, nie gwarantuje oceny ani nie zastępuje odbioru działającej strony na hostingu.
