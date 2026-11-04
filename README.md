# Grand Access Hotel

Aplikacja internetowa do obsługi rezerwacji hotelowych, kont użytkowników, pracowników i usług dodatkowych. Projekt semestralny z przedmiotu **Tworzenie stron i aplikacji internetowych**, napisany w PHP z bazą MySQL/MariaDB.

**Autorzy:** Oliwia Drygiel, Tobiasz Mućko, Dawid Domagała.

- **Repozytorium:** [grayishghost/System-rezerwacji-us-ug](https://github.com/grayishghost/System-rezerwacji-us-ug)
- **Adres strony podany w repozytorium:** [gah.gt.tc](https://gah.gt.tc/)
- **Stan opisany w README:** gałąź `main`, commit [`4343492`](https://github.com/grayishghost/System-rezerwacji-us-ug/tree/4343492474442d28b7a6826f408e89541bfd9d99) z **4 listopada 2026 r.**

> README opisuje zawartość wskazanego commita, a nie funkcje planowane ani pliki dostępne poza repozytorium. Projekt jest rozwijany: moduły administracyjne są już częściowo dodane, ale nie wszystkie pozycje docelowego menu mają odpowiadające im strony. Obecność kodu w GitHub nie potwierdza jego wdrożenia na hostingu.

## Spis treści

- [Funkcjonalności](#funkcjonalności)
- [Status i ograniczenia](#status-i-ograniczenia)
- [Technologie i wymagania](#technologie-i-wymagania)
- [Struktura repozytorium](#struktura-repozytorium)
- [Baza danych](#baza-danych)
- [Uruchomienie lokalne](#uruchomienie-lokalne)
- [Aktualizacja istniejącej bazy](#aktualizacja-istniejącej-bazy)
- [InfinityFree](#infinityfree)
- [Konta demonstracyjne](#konta-demonstracyjne)
- [Kontrola instalacji i sprawdzanie działania](#kontrola-instalacji-i-sprawdzanie-działania)
- [Bezpieczeństwo](#bezpieczeństwo)

## Funkcjonalności

### Użytkownicy i logowanie

- Rejestracja klienta z walidacją imienia, nazwiska, telefonu, adresu e-mail i haseł.
- Sprawdzanie unikalności adresu e-mail.
- Logowanie jednym formularzem dla trzech ról: `klient`, `pracownik`, `admin`.
- Hasła przechowywane jako hashe, z użyciem `password_hash()` i `password_verify()`.
- Profil użytkownika: edycja imienia, nazwiska i telefonu.
- Zmiana hasła po podaniu dotychczasowego.
- Wylogowanie, limit nieudanych logowań i wygasanie sesji po bezczynności.
- Blokowanie kont oraz odświeżanie ich aktywności i roli przy kolejnych żądaniach.

### Pobyty hotelowe

- Lista pokoi pobierana z bazy, z typem, opisem, ceną dobową i liczbą miejsc.
- Wybór pokoju, dat pobytu i liczby osób przez zalogowanego klienta.
- Sprawdzenie dat, pojemności pokoju i kolizji rezerwacji.
- Obliczenie ceny, podsumowanie i osobne potwierdzenie zapisu.
- Transakcja i blokada rekordu pokoju podczas końcowego zapisu.
- Wyświetlanie własnych pobytów w panelu klienta.

Lista pokoi pomija pokoje oznaczone jako `Serwis`. Dostępność w konkretnym terminie jest sprawdzana w procesie rezerwacji — samo wyświetlenie pokoju na liście nie oznacza wolnego terminu.

### Dodane moduły administracyjne

Po wykonaniu migracji dostępne są następujące pliki:

| Moduł | Ścieżka względem katalogu projektu | Zakres |
|---|---|---|
| Podstawowy panel administratora | `public/admin/index.php` | Lista użytkowników i blokowanie/odblokowywanie kont. |
| Konta użytkowników | `public/admin/uzytkownicy.php` | Wyszukiwanie, filtrowanie rolą, paginacja, zmiana uprawnień i aktywności z kontrolą powiązań. |
| Kategorie | `public/admin/kategorie.php` | Dodawanie, wyświetlanie, edycja, usuwanie i wyszukiwanie kategorii. |
| Usługi | `public/admin/uslugi.php` | CRUD usług, cena, czas trwania, kategoria, aktywność i filtry. |
| Pracownicy | `public/admin/pracownicy.php` | Nowe konto lub przypisanie istniejącego klienta, edycja profilu, stanowisko, aktywność i usuwanie profilu bez historii rezerwacji. |
| Kompetencje | `public/admin/przypisania.php` | Przypisywanie wielu usług pracownikowi. |
| Grafik i urlopy | `public/admin/grafik.php` | Godziny pracy, urlop, zwolnienie, dzień wolny, zakres dat i świadome nadpisywanie wpisów. |
| Rezerwacje pobytów | `public/admin/rezerwacje.php` | Lista i szczegóły, filtry, statusy, przypisanie opiekuna oraz kontrola zmiany ze starej karty. |

Zmiany w nowych modułach zapisują historię w tabeli `LogAdministratora`. Usuwanie powiązanych kategorii, usług i pracowników jest ograniczone przez kod oraz relacje w bazie.

### Panel pracownika

Obecny panel wyświetla powitanie, stanowisko i odnośnik do profilu.

## Status i ograniczenia

Aktualny kod zawiera zarówno wcześniejszy panel kont, jak i nowe moduły etapu 3. Nie stanowi jeszcze kompletnej integracji wszystkich planowanych funkcji.

- Po zalogowaniu administrator trafia do starszego `public/admin/index.php`. Nie ma tam jeszcze nowego pulpitu ze statystykami ani menu wszystkich nowych modułów. Do nowych ekranów można wejść przez ścieżki wymienione powyżej, np. `public/admin/uzytkownicy.php`.
- W `includes/admin.php` są odnośniki do **Wizyt usług**, **Kalendarza** i **Historii**, lecz w tym commicie brakuje plików `wizyty.php`, `kalendarz.php` i `logi.php` w `public/admin/`.
- Lista rezerwacji zawiera odnośnik do eksportu CSV, ale brakuje pliku `public/admin/eksport.php`.
- Migracja 004 tworzy tabelę `WizytyUslug`, jednak nie ma jeszcze strony do dodawania i obsługi godzinowych wizyt usług. Sam model bazy nie oznacza gotowego interfejsu.
- Panel klienta pokazuje własne pobyty, ale nie zawiera formularza ich samodzielnego anulowania.
- W repozytorium pozostają starsze makiety `public/index.html` i `public/pokoje.html`. Do korzystania z sesji i bazy należy używać stron **`.php`**.

## Technologie i wymagania

| Element | Wymagania / zastosowanie |
|---|---|
| PHP | **8.1+**, zgodnie ze skryptem kontroli instalacji |
| Rozszerzenia PHP | `pdo_mysql`, `mbstring` |
| Baza danych | MySQL 8.0.16+ lub MariaDB 10.5+ — wymagane m.in. klucze obce i egzekwowane `CHECK` |
| Frontend | HTML, CSS, JavaScript |
| Serwer lokalny | Wbudowany serwer PHP z `router.php` lub Apache, np. XAMPP |
| Ochrona katalogów na hostingu | Reguły `.htaccess` dla Apache 2.4 lub odpowiadająca im konfiguracja innego serwera |
| Dodatkowe zasoby | Czcionka Faculty Glyphic ładowana z Google Fonts; style mają czcionki zastępcze |

Repozytorium nie zawiera `composer.json` ani `package.json`; uruchomienie nie wymaga Composera, npm ani budowania frontendu.

## Struktura repozytorium

```text
.
├── README.md
├── .gitignore
├── assets/                       # Logo i grafiki
├── css/
│   ├── main.css                  # Strony publiczne i formularze
│   ├── panel.css                 # Podstawowe panele użytkowników
│   └── admin.css                 # Nowe moduły administracyjne
├── database/
│   ├── .htaccess
│   ├── database.sql              # Bazowy schemat i dane demonstracyjne
│   ├── diagram.svg               # ERD modelu po migracjach
│   └── migrations/
│       ├── 003_panel.sql
│       └── 004_uzupelnienia.sql
├── includes/
│   ├── .htaccess
│   ├── config.php                # Konfiguracja połączenia i BASE_URL
│   ├── db.php                    # Połączenie PDO
│   ├── auth.php                  # Sesje, role, CSRF i walidacja
│   ├── admin.php                 # Funkcje wspólne nowych modułów admina
│   ├── admin_rezerwacje.php       # Filtry i reguły statusów pobytów
│   ├── rezerwacje.php            # Dostępność i wycena pokoi
│   ├── header.php
│   └── footer.php
├── js/
│   ├── formularze.js
│   └── admin.js
├── public/
│   ├── admin/                    # Moduły administracyjne
│   ├── klient/                   # Lista własnych pobytów
│   ├── pracownik/                # Podstawowy panel pracownika
│   └── ...                       # Strony publiczne, konta i rezerwacja
├── scripts/
│   ├── .htaccess
│   ├── migrate_etap3.php          # Migracje 003 i 004, wyłącznie CLI
│   └── sprawdz_instalacje.php     # Kontrola konfiguracji i schematu, CLI
└── router.php                    # Router lokalnego serwera PHP
```

W tym commicie **nie ma** `includes/config.example.php`, katalogu `docs/` ani głównego `index.php`. Instrukcja poniżej nie wymaga tych plików.

## Baza danych

[Diagram ERD — SVG](database/diagram.svg)

Plik [`database/database.sql`](database/database.sql) zawiera **12 tabel bazowych**. Po migracjach **003 i 004** baza ma **16 tabel**:

| Tabela | Przeznaczenie |
|---|---|
| `Uzytkownicy` | Konta, hashe haseł, role i aktywność. |
| `Pracownicy` | Profile pracowników powiązane z kontami. |
| `Stanowiska` | Stanowiska pracowników. |
| `TypyPokoi` | Typy i opisy pokoi. |
| `Pokoje` | Numery, ceny dobowe, pojemność i statusy pokoi. |
| `Rezerwacje` | Pobyty klienta w pokoju, kwota, status i opcjonalny opiekun. |
| `Platnosci` | Dane płatności powiązanych z pobytami. |
| `Uslugi_dodatkowe` | Oferta usług; po migracji także kategoria przez FK i czas trwania. |
| `ZamowieniaUslugi` | Pozycje usług w rachunku pobytu, z ilością i ceną historyczną. |
| `Pracownicy_Uslugi` | Kompetencje pracowników — relacja N:M. |
| `GrafikPracownikow` | Dni pracy, godziny, urlopy, zwolnienia i dni wolne. |
| `Proby_logowania` | Nieudane logowania i ograniczenie liczby prób. |
| `KategorieUslug` | Kategorie usług, dodawane przez migrację 003. |
| `LogAdministratora` | Historia operacji nowych modułów, dodawana przez migrację 003. |
| `Migracje` | Rejestr wykonanych migracji, tworzony przez migrator. |
| `WizytyUslug` | Model godzinowej wizyty z FK do klienta, pracownika i usługi; dodawany przez migrację 004. |

**Pobyt, zamówienie dodatkowej usługi i godzinowa wizyta to różne pojęcia.** Pracownik przypisany do pobytu jest opiekunem/recepcjonistą, a nie zasobem zajętym przez cały okres pobytu.

Migracja 003 przenosi kategorie do osobnej tabeli i nadaje starym usługom domyślny czas **30 minut**. Migracja 004 usuwa dawną tekstową kolumnę kategorii i dodaje kolejne ograniczenia integralności. Domyślny czas usług należy dopasować do rzeczywistej oferty.

## Uruchomienie lokalne

### 1. Pobranie projektu

```sh
git clone https://github.com/grayishghost/System-rezerwacji-us-ug.git
cd System-rezerwacji-us-ug
```

### 2. Utworzenie nowej bazy i import

Uruchom MySQL/MariaDB, utwórz nową pustą bazę `grand_access_hotel` z kodowaniem `utf8mb4`, a następnie zaimportuj `database/database.sql` przez phpMyAdmin.

Przykład dla lokalnego klienta MySQL:

```sh
mysql -u root -p -e "CREATE DATABASE grand_access_hotel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql --default-character-set=utf8mb4 -u root -p grand_access_hotel < database/database.sql
```

> **Uwaga:** obecny `database.sql` nie tworzy bazy i nie wybiera jej poleceniem `USE`. Zawiera `DROP TABLE`, więc usuwa dane z wymienionych tabel w wybranej bazie. Importuj go tylko do nowej bazy lub przy świadomym odtwarzaniu. Nie służy do aktualizacji istniejących danych ani bezpiecznego resetowania bazy, która przeszła już migracje.

### 3. Konfiguracja PHP

Dostosuj lokalny plik `includes/config.php`. Przykładowa konfiguracja **wyłącznie dla lokalnego XAMPP z domyślnym użytkownikiem bazy**:

```php
<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'grand_access_hotel');
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_URL', '');
```

Wpisz własne wartości połączenia. Nie kopiuj powyższych ustawień jako konfiguracji publicznego hostingu.

`BASE_URL` oznacza ścieżkę projektu w adresie witryny, **bez końcowego ukośnika i bez nazwy domeny**:

| Uruchomienie | `BASE_URL` |
|---|---|
| Serwer PHP uruchomiony z głównego katalogu repo | `''` |
| XAMPP: `http://localhost/System-rezerwacji-us-ug/public/index.php` | `'/System-rezerwacji-us-ug'` |
| Projekt w głównym katalogu domeny | `''` |

### 4. Migracje i kontrola instalacji

Po imporcie bazowego SQL uruchom z głównego katalogu projektu:

```sh
php scripts/migrate_etap3.php
php scripts/sprawdz_instalacje.php
```

**Nie pomijaj migracji także przy nowej instalacji.** Bazowy SQL nie zawiera pełnego schematu nowych modułów administracyjnych. Migrator wykonuje kolejno 003 i 004, a poprawnie zakończone migracje zapisuje w tabeli `Migracje`.

### 5. Serwer i adres strony

```sh
php -S localhost:8000 router.php
```

Otwórz:

```text
http://localhost:8000/public/index.php
```

Logowanie: `http://localhost:8000/public/logowanie.php`.

Na XAMPP umieść projekt w `htdocs`, uruchom Apache i MySQL oraz ustaw odpowiedni `BASE_URL`. Skrypty CLI możesz uruchamiać przez `C:\xampp\php\php.exe`. Używaj jawnego adresu `public/index.php`, aby nie otworzyć starszej makiety HTML.

## Aktualizacja istniejącej bazy

1. Wykonaj pełny backup bazy i plików.
2. Wstrzymaj zapisy na czas aktualizacji.
3. Zachowaj lokalny config, wgraj aktualny kod i uruchom `php scripts/migrate_etap3.php`.
4. Wykonaj `php scripts/sprawdz_instalacje.php` oraz sprawdzenie funkcji w przeglądarce.

Migrator pomija migracje oznaczone jako zakończone. Jeżeli działa już schemat po 003 i 004, nie należy ręcznie wykonywać ich ponownie.

> Migracje DDL nie są atomowe. Przy częściowym błędzie nie dopisuj markerów i nie ponawiaj losowych poleceń. Ustal rzeczywisty stan schematu lub przywróć backup sprzed migracji. **Nie importuj bazowego `database.sql` jako sposobu aktualizacji.**

## InfinityFree

Na docelowym hostingu:

- Umieść pliki w odpowiednim `htdocs` przypisanym do domeny, zachowując strukturę i pliki `.htaccess`.
- Ustaw `DB_HOST`, pełną nazwę bazy, użytkownika i hasło zgodnie z panelem hostingu. Nie zakładaj `localhost` ani użytkownika `root`.
- Dla projektu umieszczonego bezpośrednio w katalogu domeny ustaw `BASE_URL=''`.
- Nie zakładaj dostępności SSH ani zewnętrznego połączenia z MySQL. Bazę i migracje można obsłużyć w phpMyAdmin.
- Dla nowej instalacji utwórz bazę w panelu, wybierz ją w phpMyAdmin i dopiero zaimportuj bazowy SQL.
- Jako adres wejścia do obecnej wersji wykorzystuj `/public/index.php`; repozytorium nie ma głównego pliku `index.php`.

### Migracje przez phpMyAdmin bez CLI

Po backupie i wybraniu właściwej bazy sprawdź, czy istnieje tabela `Migracje`, i odczytaj jej wpisy. Jeżeli nie istnieje, utwórz rejestr:

```sql
CREATE TABLE IF NOT EXISTS Migracje (
    Nazwa VARCHAR(100) PRIMARY KEY,
    Czas DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

Następnie wykonuj **tylko brakujące** migracje, w kolejności:

1. Gdy brakuje wpisu `003_panel` i nie ma tabeli `KategorieUslug`, importuj [`003_panel.sql`](database/migrations/003_panel.sql). Dopiero po wykonaniu całego pliku bez błędów dodaj:

   ```sql
   INSERT INTO Migracje(Nazwa) VALUES ('003_panel');
   ```

2. Gdy 003 jest zakończona, brakuje wpisu `004_uzupelnienia` i nie ma tabeli `WizytyUslug`, importuj [`004_uzupelnienia.sql`](database/migrations/004_uzupelnienia.sql). Dopiero po pełnym sukcesie dodaj:

   ```sql
   INSERT INTO Migracje(Nazwa) VALUES ('004_uzupelnienia');
   ```

Przy sprzeczności markerów ze schematem, np. istniejącej tabeli docelowej bez wpisu migracji, **przerwij i sprawdź stan bazy**. Nie oznaczaj migracji jako zakończonej na podstawie samego istnienia jednej tabeli.

Przed publicznym udostępnieniem włącz HTTPS, zmień hasła demonstracyjne i sprawdź odmowę odczytu plików w `includes/`, `database/` i `scripts/`. Nie wysyłaj na stronę `.git`, backupów i ZIP-ów. GitHub przechowuje kod, ale sam nie uruchamia tej aplikacji PHP/MySQL.

## Konta demonstracyjne

Konta z bazowego SQL:

| Rola | E-mail | Hasło demonstracyjne |
|---|---|---|
| Administrator | `admin@hotel.pl` | `Admin123!` |
| Pracownik | `john.doe@email.com` | `Pracownik123!` |
| Pracownik | `will.dafie@email.com` | `Pracownik123!` |
| Klient | `anna.kowal@dk.pl` | `Klient123!` |

Nowy klient rejestruje się pod `/public/rejestracja.php`, z uwzględnieniem ewentualnego prefiksu `BASE_URL`.

**Publicznie znane hasła demo nie nadają się do produkcji.** Korzystaj z nich wyłącznie na fikcyjnych danych; przed upublicznieniem instalacji zmień hasła albo ogranicz dostęp.

## Kontrola instalacji i sprawdzanie działania

W repozytorium dostępny jest skrypt:

```sh
php scripts/sprawdz_instalacje.php
```

Sprawdza wersję PHP, rozszerzenia, obecność configu, połączenie z bazą, markery migracji, wymagane tabele, trzy FK wizyty oraz usunięcie dawnej tekstowej kopii kategorii. Nie jest testem całego interfejsu ani dowodem poprawnego wdrożenia.

Składnię pojedynczego pliku można sprawdzić poleceniem:

```sh
php -l public/admin/uslugi.php
```

Po instalacji sprawdź ręcznie:

- rejestrację, poprawne i błędne logowanie, profil, zmianę hasła i wylogowanie;
- odmowę wejścia klienta do paneli administratora i pracownika;
- wybór pokoju, podsumowanie i zapis pobytu oraz odmowę kolidującego terminu;
- dodawanie i edycję kategorii, usług i pracowników;
- kompetencje N:M, zakres grafiku i urlop;
- filtry rezerwacji, szczegóły, status i opiekuna pobytu;
- blokadę usunięcia powiązanych danych oraz blokowanie kont;
- wygląd formularzy na telefonie i komputerze.

Nie przypisujemy obecnemu repo wyników testów wykonanych dla innych paczek lub wersji kodu. Brakujące moduły wymieniono w sekcji [Status i ograniczenia](#status-i-ograniczenia).

## Bezpieczeństwo

W kodzie zastosowano:

- przygotowane zapytania PDO dla danych wejściowych;
- hashowanie haseł i weryfikację przez funkcje PHP;
- serwerową kontrolę sesji, ról, aktywności i właściciela danych;
- tokeny CSRF i POST dla operacji zmieniających dane;
- kodowanie tekstu przy wyświetlaniu w HTML;
- limit pięciu nieudanych logowań w oknie 15 minut dla adresu e-mail;
- regenerację identyfikatora sesji i limit 30 minut bezczynności;
- ciasteczka `HttpOnly`, `SameSite=Lax` i `Secure` przy wykrytym HTTPS;
- transakcje, klucze obce, UNIQUE, indeksy i ograniczenia CHECK;
- reguły odmowy dostępu HTTP w prywatnych katalogach.

### Prywatna konfiguracja

`.gitignore` zawiera `includes/config.php`, ale w opisanym commicie ten plik **nadal jest śledzony**. Aby zachować go lokalnie i usunąć z indeksu Git:

```sh
git rm --cached includes/config.php
```

Następnie zatwierdź zmianę. Nie publikuj rzeczywistych haseł w README, commitach, logach ani zrzutach ekranu. Jeżeli prawdziwe dane dostępowe trafiły do historii repozytorium, należy je unieważnić — sam `.gitignore` nie usuwa ich z historii.
