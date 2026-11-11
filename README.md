# Grand Access Hotel — komplet funkcji z uwag nauczyciela

Autorzy projektu: Oliwia Drygiel, Tobiasz Mućko, Dawid Domagała.

To kompletne wydanie projektu, a nie nakładka samych grafik. Łączy wcześniejszą pełną wersję panelu, poprawki zasobów oraz nowe zarządzanie pokojami, blokady terminów i edycję pobytów. Nie jest kopią obecnej gałęzi głównej GitHuba ani potwierdzeniem wdrożenia na gah.gt.tc.

## Funkcje

- Pulpit z licznikami, ostatnimi operacjami i wspólną nawigacją.
- Rezerwacje pobytów: dodawanie, szczegóły, filtry, eksport CSV, status, opiekun, zmiana pokoju, terminu i liczby osób z przeliczeniem ceny.
- Zakończonych rezerwacji nie wskrzeszamy. Pobyt można ukończyć po wymeldowaniu. Zmiana terminu jest możliwa przed rozpoczęciem, bez zapisanych płatności. Zamiast usuwać historię — anulowanie.
- Pokoje: dodawanie, edycja, usuwanie bez historii, publikowanie i wycofywanie oferty, wybór istniejącej ilustracji.
- Dostępność: kolizje pobytów i blokad, wycofana oferta, serwis. Przedziały są prawostronnie otwarte: dzień wymeldowania nie blokuje kolejnego pobytu.
- Dostępność pracowników: grafik, urlopy, aktywność profilu i konta, wolne przedziały pomniejszone o wizyty. Kompetencje są sprawdzane przy tworzeniu wizyty. Opiekun pobytu nie oznacza całodobowego zajęcia pracownika.
- Konta i pracownicy: dodawanie, edycja, dezaktywacja, usuwanie profilu bez historii, kompetencje, grafik i urlopy.
- Kategorie i usługi: CRUD, czas trwania, przypisania pracowników.
- Wizyty usług: bezpośrednie powiązania z klientem, pracownikiem i usługą, godziny pracy, brak kolizji, statusy, CSV.
- Kalendarz pobytów, blokad pokoi i wizyt usług.
- Historia operacji administratora z filtrowaniem; zapis poprzedniego i nowego stanu.
- Kontrola instalacji dostępna dla administratora także przed migracją panelu: schemat, markery migracji i sumy kontrolne plików.
- Role, CSRF, sesje, prepared statements, transakcje i blokady rekordów, kontrola nieaktualnych formularzy, zabezpieczenie CSV.
- Lokalne grafiki i fonty, wersjonowanie adresów zasobów zawartością pliku, widoki mobilne.

## Integralność

Schemat ma 17 tabel, 19 FK i 37 CHECK. Migracja 006 dodaje 23 CHECK i chroni powiązania płatności oraz zamówień przed usunięciem razem z pobytem. Reguły kolizji i pojemności są egzekwowane w transakcjach aplikacji, nie przez dowolny bezpośredni SQL. Szczegóły, granice zabezpieczeń oraz polityka dat są w `INTEGRALNOSC.md`.

## Uruchomienie

Wymagania: PHP 8.1+ z pdo_mysql i mbstring, MySQL 8.0.16+ albo współczesna MariaDB obsługująca CHECK; Apache 2.4 z .htaccess albo lokalny serwer PHP. Testy wykonano na PHP 8.4 i MariaDB 11.8. Ostateczną zgodność z konkretną bazą InfinityFree trzeba potwierdzić po migracji.

1. Przeczytaj `WDROZENIE_INFINITYFREE.md`. Istniejąca baza i nowa baza wymagają różnych czynności.
2. Utwórz prywatny `includes/config.php` z `includes/config.example.php`.
3. Dla PUSTEJ bazy importuj `database/nowa_baza_INFINITYFREE.sql`. Ten plik kasuje i odtwarza tabele — NIE używaj do aktualizacji istniejących danych.
4. Dla istniejącej bazy wykonaj tylko brakujące migracje 003, 004, 005, 006. Nie zgaduj stanu częściowej migracji.
5. Otwórz `/public/logowanie.php`, następnie `/public/admin/instalacja.php`.
6. Lokalnie można użyć `php -S 0.0.0.0:8000 router.php` przy `BASE_URL` równym pustemu ciągowi.

`database/database.sql` jest pełnym odtworzeniem lokalnej bazy grand_access_hotel; również kasuje dane. Nie jest migracją.

## Konta demonstracyjne

| Rola | Login | Hasło |
|---|---|---|
| Administrator | admin@hotel.pl | Admin123! |
| Pracownik | john.doe@email.com | Pracownik123! |
| Klient | anna.kowal@dk.pl | Klient123! |

Dotyczą fikcyjnej bazy demonstracyjnej. Po testach usuń publiczny dostęp administracyjny lub zmień hasło. Nigdy nie publikuj config.php ani kopii bazy z prawdziwymi danymi.

## Struktura

`public/` — strony ról i panel; `includes/` — wspólna logika; `database/` — schemat i migracje; `css/`, `js/`, `assets/` — lokalne zasoby; `scripts/` — narzędzia CLI. Katalogów prywatnych i SQL nie otwieramy przez WWW. Licencja fontów: `assets/fonts/OFL.txt`.

Kod aplikacji jest bez komentarzy. Objaśnienia są w dokumentacji, poza kodem.

## Zgodność repozytorium i hostingu

Wgraj na hosting i do repozytorium ten sam zestaw z tego wydania, bez prywatnego config.php. Sprawdź panel instalacji oraz `php scripts/sprawdz_instalacje.php`. Identyfikator i odcisk wydania muszą odpowiadać plikowi START z paczki. Manifest sprawdza wymienione pliki, nie wykrywa dowolnych dodatkowych plików — usuń niepotrzebne stare kopie ręcznie po backupie.

Brak automatycznej synchronizacji GitHub → InfinityFree. Sam commit nie uruchamia migracji. Oceniać należy działające strony na hostingu. Uzupełnienie funkcji nie gwarantuje konkretnej oceny.
