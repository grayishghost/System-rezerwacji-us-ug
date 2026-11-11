# Wdrożenie na gah.gt.tc — bez kasowania istniejących danych

## 1. Co jest obecnie nie tak

Sprawdzenie przez publiczne konto demo było tylko do odczytu. Zastany pulpit to stara wersja. Moduły nowego panelu odpowiadały HTTP 503 z komunikatem wymagającym migracji. Strony pokoi i dostępności zwracały 404. Nie wykonano zdalnych zmian ani migracji. Ta paczka sama nie zmienia hostingu.

## 2. Backup i wgranie plików

1. Z panelu InfinityFree pobierz kopię całego aktualnego `htdocs` i wyeksportuj bazę w phpMyAdmin. Sprawdź, że kopie się pobrały. Nie umieszczaj ich w publicznym katalogu ani repozytorium.
2. Zachowaj istniejący `includes/config.php`. Paczka nie zawiera tego pliku. Nie zastępuj go konfiguracją lokalną.
3. Na czas aktualizacji ogranicz dostęp do strony w panelu hostingu; nie przyjmuj równoległych rezerwacji podczas migracji. Samo wycofanie pokoju nie zastępuje okna serwisowego.
4. Wgraj ZAWARTOŚĆ katalogu `projekt` do `htdocs` domeny gah.gt.tc, nie cały katalog `AKTUALNE` i nie archiwum wcześniejszych materiałów. Nadpisz cały aktualny zestaw plików, zwłaszcza `public/admin/index.php`, `includes/`, CSS, JS i grafiki. Wgraj też ukryte `.htaccess`. Nie kasuj hostingu w ciemno.
5. W prywatnym config.php sprawdź DB_HOST, pełną nazwę bazy, DB_USER i DB_PASS z panelu MySQL InfinityFree. Nie zakładaj `localhost`. Dla domeny bez podkatalogu BASE_URL to pusty ciąg. Nie wpisuj pełnego adresu HTTPS w BASE_URL. Opcjonalne SESSION_SECURE=true dopiero gdy strona działa wyłącznie po HTTPS.
6. Usuń stare zapasowe PHP/SQL/ZIP z publicznego katalogu po zabezpieczeniu ich kopii. Manifest nie wykrywa wszystkich dodatkowych plików. Nie wysyłaj `ARCHIWUM`, `robocze`, raportów testów ani prywatnych danych.

## 3. Ustal stan bazy — zanim cokolwiek zaimportujesz

Zaloguj się na `/public/logowanie.php` i otwórz `/public/admin/instalacja.php`. Ta strona nie wymaga kompletnego schematu nowego panelu, ale wymaga działającej konfiguracji, bazowych tabel kont i konta admina. Jest tylko odczytem. Pokazuje tabele, kolumny, markery i ograniczenia. Jeśli logowanie lub PDO nie działa, sprawdź bazę i log PHP w panelu hostingu.

W phpMyAdmin wybierz właściwą bazę i uruchom odczyt:

```sql
SHOW TABLES;
SELECT TABLE_NAME,COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME,ORDINAL_POSITION;
```

Jeżeli istnieje Migracje:

```sql
SELECT Nazwa FROM Migracje ORDER BY Nazwa;
```

| Migracja | Stan przed migracją | Stan po migracji |
|---|---|---|
| 003_panel | Uslugi_dodatkowe.Kategoria, brak KategorieUslug i LogAdministratora | KategorieUslug, LogAdministratora, Pracownicy.Aktywny/Opis, ID_Kategorii i CzasTrwania usługi |
| 004_uzupelnienia | po 003, brak WizytyUslug | WizytyUslug z 3 FK, dodatkowe CHECK, usunięta tekstowa Kategoria |
| 005_pokoje | po 004, brak BlokadyPokoi i nowych kolumn pokoi/pobytów | BlokadyPokoi, Pokoje.Aktywny/Wersja, Rezerwacje.LiczbaOsob/Wersja |
| 006_integralnosc | po 005, brak CHECK o prefiksie chk_i006_ | 23 dodatkowe CHECK, indeks pobytów i dwa FK z RESTRICT |

Jeśli tabela/kolumna migracji JUŻ jest, lecz markeru brak albo część pozostałych elementów nie istnieje: STOP. To może być częściowa migracja. Nie uruchamiaj ponownie ALTER i nie dopisuj markeru dla pozoru. Porównaj pełny SQL migracji z rzeczywistym schematem, odtwórz spójny backup albo napraw indywidualnie z osobą znającą bazę. Przy nieznanym stanie nie istnieje bezpieczny uniwersalny import. Markery same w sobie nie dowodzą kompletności schematu.

## 4. Aktualizacja istniejącej bazy przez phpMyAdmin

Nie importuj `database.sql` ani `nowa_baza_INFINITYFREE.sql` na istniejącej bazie. Oba kasują tabele.

1. Jeżeli brak rejestru migracji, wykonaj `database/00_rejestr_migracji.sql`. Tworzy tylko rejestr, nie wykonuje pozostałych migracji.
2. Przed 006, po pełnym 005, uruchom `database/PRZED_006_ODCZYT.sql`. Wszystkie 27 liczników musi wynosić zero; inaczej przerwij i wyjaśnij dane. To tylko odczyt, nie automatyczna naprawa.
3. W kolejności 003 → 004 → 005 → 006 importuj WYŁĄCZNIE brakujące, w całości niewykonane pliki z `database/migrations/`.
4. Po KAŻDYM imporcie upewnij się, że phpMyAdmin nie zgłosił żadnego błędu. Wykonaj odczyty z pliku `database/weryfikacja_po_migracji.sql`, porównaj elementy tabeli powyżej i cały SQL tej migracji.
5. Dopiero po pełnym sukcesie odpowiedniej migracji zapisz jej marker, osobno:

```sql
INSERT INTO Migracje(Nazwa) VALUES ('003_panel');
```

Po sukcesie 004 analogicznie:

```sql
INSERT INTO Migracje(Nazwa) VALUES ('004_uzupelnienia');
```

Po sukcesie 005 analogicznie:

```sql
INSERT INTO Migracje(Nazwa) VALUES ('005_pokoje');
```

Po sukcesie 006 analogicznie:

```sql
INSERT INTO Migracje(Nazwa) VALUES ('006_integralnosc');
```

Nie wykonuj ponownie INSERT, jeżeli marker już istnieje. Nie dopisuj czterech markerów bez czterech kompletnych migracji. Brakujące markery dla kompletnego, wcześniej wykonanego schematu wymagają najpierw sprawdzenia całej migracji.

DDL w MySQL nie jest atomowe: błąd w połowie nie cofa wcześniejszych ALTER. W takim przypadku przerwij, zachowaj komunikat i przywróć backup/napraw konkretny stan — nie ponawiaj całego pliku w ciemno.

InfinityFree zwykle nie udostępnia SSH. Nie uruchamiaj migracji przez dopisany publiczny endpoint PHP. Jeżeli masz środowisko z CLI i prawidłowym config.php, alternatywą dla phpMyAdmin jest `php scripts/migrate_etap3.php`. Skrypt pomija oznaczone migracje i odrzuca wykryty częściowy schemat. To nadal wymaga backupu.

## 5. Nowa, PUSTA baza

Tylko gdy celowo zaczynasz od fikcyjnej bazy demonstracyjnej: wybierz pustą bazę w phpMyAdmin i importuj `database/nowa_baza_INFINITYFREE.sql`. Zawiera już 003–006 i ich markery. Nie importuj ich drugi raz. `database.sql` przeznaczono do lokalnego odtworzenia grand_access_hotel i zawiera CREATE DATABASE/USE.

## 6. Sprawdzenie po wdrożeniu

- `/public/admin/instalacja.php`: wszystkie kontrole bazy OK; brak zmienionych plików; identyczny identyfikator i odcisk jak w START paczki.
- `/public/admin/index.php`: nowy pulpit z nawigacją do pokoi, dostępności, pobytów, kalendarza i historii.
- Zrób ręcznie checklistę `ODBIOR_I_PODZIAL.md` na fikcyjnych danych. Dopiero po uzgodnieniu z właścicielem możesz tworzyć lub usuwać dane hostingu.
- Sprawdź CSS, obrazy, nawigację, role, eksport i telefon. Wyczyść cache przeglądarki po aktualizacji; pliki zasobów mają adresy zależne od zawartości.
- Sprawdź, że `/includes/config.php`, `/database/database.sql` i `/scripts/migrate_etap3.php` nie są dostępne jako publiczne treści. Dostęp do tych katalogów jest blokowany przez .htaccess.
- Przy 503: kontrola instalacji. Przy 404: położenie plików. Przy 500: log PHP, wersja PHP i dane bazy; nie włączaj publicznego wyświetlania haseł/błędów PDO.
- Jeśli hosting odrzuca dyrektywę .htaccess, odczytaj konkretny błąd w logu i dostosuj do jego Apache; nie wyłączaj ochrony prywatnych katalogów bez zamiennika.

## 7. GitHub i hosting to to samo wydanie

Przenieś do repozytorium ten sam katalog `projekt`, włącznie z `includes/wydanie.json`, ale bez `includes/config.php`. Nie nadpisuj repozytorium katalogiem backupu. Przed commitem sprawdź listę zmian i `git status`. Jeśli config był już śledzony, usuń go z indeksu i zmień ujawnione dane bazy — .gitignore nie usuwa sekretów z historii.

Zrób commit po testach, wyślij go i zapisz jego SHA w protokole odbioru. Wykonaj ręcznie upload dokładnie tego wydania na InfinityFree oraz migracje. Potwierdź zgodność manifestu na hostingu. Nie twierdzimy, że obecne repozytorium lub hosting zostały zmienione przez przygotowanie tej paczki.
