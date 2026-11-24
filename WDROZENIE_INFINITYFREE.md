# Aktualizacja 007 → etap 4 / 008

## Założenia i backup

To instrukcja dla kompletnego, poprawnego wydania 007. Przed zmianą sprawdź kontrolę instalacji i rzeczywisty stan bazy. Brak markera mimo wcześniej wykonanych ALTER nie oznacza, że wolno wykonać je ponownie.

1. Pobierz prywatny backup wszystkich plików strony, includes/config.php i dynamicznych ilustracji assets/pokoje. Wyeksportuj całą właściwą bazę w phpMyAdmin. Sprawdź pobranie i możliwość odtworzenia.
2. Ustal okno serwisowe bez zapisów klientów i administratorów. Nie uruchamiaj jednocześnie mieszanego starego i nowego kodu. Nowe pliki wymagają kompletnego 008; stary kod nie zapisuje nowej historii.
3. Nie wysyłaj prywatnych backupów, produkcyjnych eksportów ani config.php do GitHuba lub publicznego htdocs.

## Stan wyjściowy

W phpMyAdmin wybierz bazę strony i odczytaj:

```sql
SELECT Nazwa FROM Migracje ORDER BY Nazwa;
```

Oczekiwane: 003_panel, 004_uzupelnienia, 005_pokoje, 006_integralnosc, 007_panel. Jeżeli 008_terminarz już jest, nie importuj jej ponownie. Jeśli brakuje wcześniejszej migracji albo jej markeru, najpierw potwierdź schemat; nie dopisuj markerów dla pozoru. Nowy runner CLI zawiera przywróconą obsługę 007, której brakowało w sprawdzonym commicie GitHuba.

## Upload plików

Wgraj zawartość projekt/ z pełnego wydania 008 albo komplet nakładki 007 → 008 bezpośrednio do htdocs przypisanego do gah.gt.tc. Nie twórz htdocs/projekt i nie pozostawiaj ZIP w miejscu publicznym. Zachowaj katalogi i pliki zaczynające się kropką.

Zachowaj prywatny includes/config.php i istniejące ilustracje użytkowników. Nie zastępuj konfiguracji przykładem. Nie kasuj całych katalogów podczas nadpisywania plików. Dla domeny bez podkatalogu BASE_URL jest pustym ciągiem. Nakładka nie uzupełnia brakujących niezmienionych plików starego wydania; niezgodne zasoby napraw wskazanymi plikami pełnego kompletu, nie modyfikacją manifestu.

Komplet trzech paczek osobowych jest jedną zmianą. Nie wdrażaj na hosting pojedynczej paczki osoby ani części dnia. Najpierw scal wszystkie zmiany, sprawdź zgodność z kompletem, potem wykonaj jeden spójny upload w oknie serwisowym. README i dokumentacja wewnątrz projekt należą do manifestu i też są częścią uploadu.

## Wykonaj tylko brakującą 008

1. W phpMyAdmin tej samej bazy uruchom database/PRZED_008_ODCZYT.sql. Dwa liczniki Niepoprawne muszą wynosić zero. Wynik dodatni oznacza STOP i analizę, nie kasowanie historii.
2. Upewnij się, że tabela HistoriaWizyt jeszcze nie istnieje. Jeżeli istnieje bez markeru, stan może być częściowy — nie importuj od początku.
3. Importuj wyłącznie database/migrations/008_terminarz.sql. Tworzy nową tabelę i oznaczony stan początkowy istniejących wizyt. Nie zmienia ich dat, statusów, cen, przypisań, grafików ani kont.
4. Po jakimkolwiek błędzie STOP: DDL może być częściowo wykonane. Nie ponawiaj w ciemno, nie dodawaj markera. Zachowaj komunikat i przywróć spójny backup albo napraw konkretny stan po analizie.
5. Dopiero po pełnym sukcesie wykonaj:

```sql
INSERT INTO Migracje(Nazwa) VALUES ('008_terminarz');
```

6. Uruchom database/PO_008_ODCZYT.sql. Dwa liczniki muszą wynosić zero.
7. Odśwież /public/admin/instalacja.php: GAH-ETAP4-008-r1, sześć markerów 003–008, wszystkie kontrole schematu oraz zgodne pliki. Oczekiwane 18 tabel, 21 FK i 44 CHECK. Odcisk manifestu i liczbę plików znajdziesz w START paczki.

Nie ponawiaj 003–007. Nie importuj database.sql ani nowa_baza_INFINITYFREE.sql do istniejącej bazy — kasują i odtwarzają tabele. Nie wyłączaj FK/CHECK w celu wymuszenia importu.

Jeżeli masz bezpieczny dostęp CLI, alternatywą jest php scripts/migrate_etap3.php z poprawną prywatną konfiguracją i backupem. Skrypt pomija oznaczone migracje, wykonuje odczyty przed 006/007/008 i zatrzymuje częściowy schemat. Nie dodawaj publicznego endpointu uruchamiającego migracje.

## Grafik i właściwe dane

Migracja nie wymyśla przyszłych terminów na hostingu. W panelu Pracownicy → Kompetencje przypisz odpowiednie usługi aktywnym pracownikom, a w Grafiku ustaw prawdziwe dni i godziny pracy. Urlop albo Wolne oznaczają brak dostępności. Brak dnia pracy daje brak godzin — to nie błąd zapisu. Używaj wyłącznie danych, które wolno przetwarzać i publikować.

DEMO_TERMINARZ.sql i pełny SQL są do fikcyjnego środowiska demonstracyjnego. Pełne odtworzenie tworzy grafik kolejnych 14 dni. Nie importuj demonstracyjnego grafiku na rzeczywisty hosting bez świadomej decyzji właściciela. Nie wpisuj sztucznych wizyt jako rzekomo wykonanych rezerwacji klientów.

## Kontrola działania na hostingu

- Ctrl+F5 po uploadzie CSS/JS.
- Oferta /public/uslugi.php → szczegóły → klient: usługa, odpowiedni pracownik, data, godzina, podsumowanie, potwierdzenie.
- Konto klienta widzi tylko własne wizyty, ich szczegóły, anulowanie i przełożenie przyszłej aktywnej wizyty, kalendarz oraz historię.
- Konto pracownika widzi własne wizyty na dziś/przyszłe/historię i zmienia status własnej wizyty, nigdy cudzej.
- Próby konfliktów, stykanie granic oraz dwa równoległe zapisy według ETAP4_TESTY.md.
- Różne godziny, urlop i dzień wolny, AJAX oraz rezerwacja z wyłączonym JavaScript.
- Ochrona includes/config.php, SQL i skryptów CLI. GD/fileinfo i zapisywalny assets/pokoje dla ilustracji; bez uprawnień 777.

Testy zapisujące dane wykonuj wyłącznie na uzgodnionych fikcyjnych rekordach za zgodą właściciela. Publiczne hasło demonstracyjnego administratora zmień po próbach. Nie wysyłaj do czatu haseł, konfiguracji ani danych prawdziwych klientów.

## Awaria i wycofanie

503 nowego panelu oznacza m.in. brak wymaganej migracji. Diagnostyka administratora powinna pozostać dostępna; 500 wymaga prywatnego logu serwera. Nie pokazuj szczegółów połączenia publicznie. Przy częściowym DDL lub wycofaniu przywróć zgodną parę backup plików + backup bazy. Samo cofnięcie plików po nowych zapisach może zatrzymać historię — nie mieszaj wersji w normalnym ruchu.

Wypełnij protokół ODBIOR_I_PODZIAL.md rzeczywistymi wynikami hostingu. Przygotowanie paczek i lokalnych testów nie oznacza zdalnego wdrożenia ani gwarancji oceny.
