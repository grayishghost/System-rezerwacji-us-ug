# Wdrożenie GAH-FULL-007-r1

## Aktualizacja działającego wydania 006

Ta ścieżka jest przeznaczona dla bazy, w której 003, 004, 005 i 006 wykonano w całości. Jeżeli markeru brakuje mimo wcześniejszego importu, najpierw potwierdź kompletny schemat — nie ponawiaj ALTER ani nie wpisuj markerów dla pozoru.

1. Pobierz prywatny backup całego htdocs, w tym includes/config.php i istniejących assets/pokoje. Wyeksportuj właściwą bazę przez phpMyAdmin. Zweryfikuj pobranie plików. Backupów nie publikuj pod domeną ani w repozytorium.
2. Zaplanuj okno serwisowe bez nowych rezerwacji i edycji. Stare i nowe pliki nie powinny jednocześnie obsługiwać zapisów.
3. Wgraj zawartość projekt z paczki 007 do htdocs przypisanego do gah.gt.tc, z nadpisaniem odpowiednich plików. Nie twórz dodatkowego katalogu projekt. Można użyć nakładki 007, jeżeli na serwerze jest pełne wydanie 006. Wgraj również pliki zaczynające się kropką.
4. ZACHOWAJ prywatny includes/config.php, prawdziwe dane bazy, BASE_URL i istniejące ilustracje użytkowników. W paczce nie ma config.php. Jeśli zawiera komentarze, usuń tylko komentarze, nie wartości. Dla domeny bez podkatalogu BASE_URL jest pustym ciągiem.
5. Otwórz /public/admin/instalacja.php. Logowanie oraz diagnostyka pozostają dostępne przed 007; pełny nowy panel wymaga 007.
6. W phpMyAdmin wybierz właściwą istniejącą bazę. Uruchom database/PRZED_007_ODCZYT.sql. Są dwa wyniki; oba Niepoprawne muszą wynosić zero. Wynik dodatni oznacza duplikaty nazw stanowisk albo typów. Wyjaśnij je bez usuwania przypisań/historii. Nie scalamy ich automatycznie.
7. Dopiero przy dwóch zerach importuj database/migrations/007_panel.sql. Po jakimkolwiek błędzie STOP: wcześniejsze DDL mogło już się wykonać. Nie ponawiaj w ciemno. Przywróć spójny backup albo zbadaj i napraw konkretny częściowy stan.
8. Dopiero po pełnym sukcesie 007 zapisz marker:

```sql
INSERT INTO Migracje(Nazwa) VALUES ('007_panel');
```

9. Nie wykonuj ponownie 003, 004, 005 ani 006. Nie importuj database.sql ani nowa_baza_INFINITYFREE.sql na istniejących danych — kasują i odtwarzają tabele.
10. Odśwież kontrolę instalacji. Wszystkie kontrole schematu muszą być OK, a wydanie GAH-FULL-007-r1 i manifest zgodne z plikiem START paczki. Wykonaj odczyty weryfikacji i checklistę odbioru.

## Warunki przesyłania ilustracji

Wgraj assets/pokoje/.htaccess i zachowaj ten katalog. PHP musi móc w nim zapisywać; sprawdź właściciela/uprawnienia w panelu hostingu. Nie stosuj 777. Wymagane GD oraz fileinfo; ich stan i limity upload_max_filesize/post_max_size pokazuje kontrola instalacji. Bez GD nadal można używać istniejących ilustracji, ale upload zgłosi komunikat.

Aplikacja przyjmuje poprawne JPG/PNG/WEBP do 5 MB, 6000 px na bok i 8 mln pikseli. Limit serwera może być niższy. Plik jest dekodowany i zapisywany jako nowy JPEG z losową nazwą. Nie przesyłamy SVG ani plików wykonywalnych. Po błędzie zapisu bazy nowy plik jest usuwany.

Nowe ilustracje nie należą do stałego manifestu. Zachowaj je w backupie i podczas kolejnych uploadów. .gitignore pomija je, ale zachowuje ochronny .htaccess. Nie wgrywaj ich automatycznie do publicznego repozytorium.

## Starsza istniejąca baza

1. Backup i ustalenie stanu rzeczywistych tabel/kolumn/ograniczeń oraz tabeli Migracje. Sam brak markeru nie oznacza braku wykonanych ALTER.
2. Jeżeli nie ma rejestru Migracje, wykonaj database/00_rejestr_migracji.sql.
3. Wykonuj tylko brakujące i w całości niewykonane migracje 003_panel, 004_uzupelnienia, 005_pokoje. Po każdej sprawdź pełny sukces i dodaj wyłącznie jej marker.
4. Przed 006 uruchom PRZED_006_ODCZYT.sql: wszystkie 27 wyników zero. Wykonaj 006, sprawdź i zapisz marker dopiero po pełnym sukcesie.
5. Przed 007 wykonaj dwa odczyty PRZED_007_ODCZYT.sql, następnie procedurę 007 powyżej.
6. Markery odpowiadają dokładnym nazwom plików bez .sql. Nie dodawaj wszystkich naraz bez kompletnych migracji.

Jeżeli część elementów migracji już istnieje, a innych brakuje, nie uruchamiaj jej od początku. Wymagany jest backup lub indywidualna naprawa schematu. Nie wyłączaj FOREIGN_KEY_CHECKS ani CHECK w celu przepchnięcia błędnych danych.

InfinityFree zwykle nie udostępnia SSH. Jeśli środowisko ma CLI, alternatywą jest php scripts/migrate_etap3.php z poprawną prywatną konfiguracją i backupem. Skrypt pomija oznaczone migracje, wykrywa częściowy schemat i kontroluje dane przed 006/007. Nie dodawaj publicznego endpointu uruchamiającego migracje.

## Nowa pusta baza

Wyłącznie do świadomego uruchomienia fikcyjnego demo: importuj database/nowa_baza_INFINITYFREE.sql do pustej bazy. Zawiera 003–007 i markery. Nie wykonuj migracji drugi raz. database/database.sql wybiera lokalną grand_access_hotel; również jest destrukcyjnym odtworzeniem, nie aktualizacją.

## Po wdrożeniu

- Logo panelu ma otwierać stronę hotelu; granat i złote akcenty mają odpowiadać stronie.
- Konta: utwórz testowego klienta, pracownika i admina; edytuj i usuń konta bez historii. Sprawdź blokady własnego konta i rekordów z historią.
- Pracownicy: sekcje kompetencji i stanowisk. Usługi: sekcja kategorii. Pokoje: typy i upload ilustracji.
- Pobyty: edycja dat/pokoju przed rozpoczęciem, uwagi do historii, zachowanie kwoty opłaconego pobytu, archiwizacja/przywrócenie, trwałe usunięcie tylko anulowanego bez rozliczeń.
- Archiwizacja nie anuluje pobytu i nie zwalnia pokoju. Sprawdź odrzucenie kolizji także dla aktywnego pobytu w archiwum.
- Sprawdź na telefonie, konto klienta/pracownika, kalendarz, historię i CSV.
- /includes/config.php, SQL oraz skrypty CLI nie mogą być publicznie dostępne. Sprawdź ochronę katalogu ilustracji.
- 503 panelu oznacza brak wymaganej migracji. 500 wymaga odczytu logu serwera. Nie publikuj błędów z hasłami ani konfiguracji. Przy odrzuceniu dyrektywy .htaccess sprawdź konkretny błąd; nie wyłączaj ochrony bez zamiennika.

## GitHub i hosting

Wgraj to samo wydanie do repozytorium bez prywatnego config.php, produkcyjnej bazy i dynamicznych ilustracji. Jeśli config.php był już śledzony, usuń go z bieżącej wersji repo i zmień ujawnione prawdziwe hasła. .gitignore nie usuwa sekretów z historii.

Zapisz SHA commita i wynik kontroli instalacji na hostingu. Nie jest to automatyczna synchronizacja. Przygotowanie paczki oraz lokalnych testów nie oznacza, że zdalne pliki, baza lub repozytorium zostały zmienione.
