# Grand Access Hotel

System zarządzania pięciogwiazdkowym hotelem Grand Access Hotel: rezerwacje, klienci, pracownicy, pokoje i usługi dodatkowe.
Projekt semestralny (przedmiot: Tworzenie stron i aplikacji internetowych).

## Autorzy

* Oliwia Drygiel
* Tobiasz Mućko
* Dawid Domagała

## Opis systemu

Aplikacja internetowa w PHP z bazą MySQL. Użytkownicy mają jedną z trzech ról:

|Rola|Co może|
|-|-|
|**klient**|zakłada konto, loguje się, edytuje profil, (w kolejnych etapach) rezerwuje i anuluje własne rezerwacje|
|**pracownik**|loguje się do swojego panelu, (w kolejnych etapach) obsługuje swoje wizyty|
|**admin**|zarządza użytkownikami (blokowanie kont), (w kolejnych etapach) całym systemem|

## Technologie

* PHP 8 (PDO, sesje), HTML5, CSS3, JavaScript
* MySQL / MariaDB
* Git i GitHub

## Instrukcja uruchomienia (XAMPP, Windows)

1. Zainstaluj XAMPP i uruchom w panelu **Apache** oraz **MySQL**.
2. Skopiuj folder projektu do `C:\\\\xampp\\\\htdocs\\\\` (np. `C:\\\\xampp\\\\htdocs\\\\System-rezerwacji-us-ug`).
3. Otwórz `http://localhost/phpmyadmin`, wybierz **Import** i wskaż plik `database/database.sql`
(plik sam tworzy bazę `grand\\\_access\\\_hotel` i wszystkie tabele z danymi testowymi).
4. W folderze `includes` zmodyfikuj `config.php`. Sprawdź dane bazy
i ustaw `BASE\\\_URL` na nazwę folderu projektu, np. `/System-rezerwacji-us-ug`.
5. Wejdź na `http://localhost/System-rezerwacji-us-ug/public/index.php`.

Szybkie uruchomienie bez XAMPP-a (gdy PHP jest zainstalowany): w folderze projektu
`php -S localhost:8000`, w `config.php` ustaw `BASE\\\_URL` na pusty tekst `''`, wejdź na `http://localhost:8000/public/index.php`.

## Konta testowe

|Rola|E-mail|Hasło|
|-|-|-|
|administrator|`admin@hotel.pl`|`Admin123!`|
|pracownik|`john.doe@email.com`|`Pracownik123!`|
|pracownik|`will.dafie@email.com`|`Pracownik123!`|
|klient|`anna.kowal@dk.pl`|`Klient123!`|

Nowych klientów można zarejestrować na stronie `public/rejestracja.php`.

## Struktura repozytorium

```
README.md
database/database.sql     baza danych (struktura + dane testowe)
database/diagram\\\_bazy.png diagram ERD
includes/                 wspólny kod: połączenie z bazą, sesje, role, nagłówek i stopka
public/                   strony aplikacji (index, logowanie, rejestracja, profil, panele)
  admin/ klient/ pracownik/
css/  js/  assets/
```

## Baza danych

Tabele: `Uzytkownicy` (logowanie i role), `Pracownicy`, `Stanowiska`, `Pokoje`, `TypyPokoi`,
`Rezerwacje`, `Platnosci`, `Uslugi\\\_dodatkowe`, `ZamowieniaUslugi`, `Pracownicy\\\_Uslugi` (relacja N:M),
`GrafikPracownikow` (grafik, urlopy), `Proby\\\_logowania` (limit błędnych logowań).
Klucze obce, UNIQUE (np. e-mail), indeksy i ograniczenie `CHECK (DataDo > DataOd)` są opisane w `database.sql`.

## Funkcje etapu 2 (użytkownicy i logowanie)

* rejestracja z walidacją po stronie serwera (pola, e-mail, unikalność e-maila, zgodność haseł),
* hasła zapisywane przez `password\\\_hash()`, sprawdzane przez `password\\\_verify()`,
* logowanie jednym formularzem dla trzech ról, sesje, wylogowanie, przekierowanie do właściwego panelu,
* kontrola ról po stronie PHP na każdej chronionej stronie (wpisanie adresu URL ręcznie nie pomoże),
* profil użytkownika z edycją danych,
* zapytania przygotowane (PDO) i zabezpieczenie wyświetlanych danych przed XSS (`htmlspecialchars`),
* komunikaty błędów bez ujawniania informacji technicznych.

### Funkcje dodatkowe

* tokeny CSRF we wszystkich formularzach POST,
* limit błędnych prób logowania (5 prób na 15 minut dla jednego e-maila),
* zmiana hasła po podaniu starego,
* blokowanie i odblokowywanie kont przez administratora (działa od razu, także dla zalogowanych),
* bezpieczne zarządzanie sesją: regeneracja ID po logowaniu, ciasteczko `httponly` + `samesite`, wylogowanie po 30 minutach bezczynności.

## Status projektu

Etap 2 (użytkownicy, logowanie, role, profil) wykonany. Kolejne etapy: panel administracyjny, rezerwacje, prezentacja.

