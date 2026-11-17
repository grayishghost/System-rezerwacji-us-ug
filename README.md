# Grand Access Hotel — etap 4, GAH-ETAP4-008-r1

Autorzy projektu: Oliwia Drygiel, Tobiasz Mućko, Dawid Domagała. Trzyosobowy skład został zaakceptowany przez nauczyciela.

PHP, HTML5, CSS3, JavaScript, PDO oraz MySQL/MariaDB. Wspólne repozytorium: https://github.com/grayishghost/System-rezerwacji-us-ug. Bazą etapu 4 jest wydanie 007; sprawdzony commit 823036b6cf34aca21f8d3a92668f792f6427d979 miał pliki 007 z wyjątkiem starszego scripts/migrate_etap3.php. Nowe wydanie zawiera poprawiony runner obejmujący 003–008. Nie deklarujemy automatycznego wdrożenia na hosting ani push do repozytorium.

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

PHP i sesja bazy używają czasu lokalnego Europe/Warsaw; offset jest ustalany na każde żądanie bez zależności od dostępności nazwanych stref MySQL. Daty wizyt są lokalnymi DATETIME. Aplikacja nie obsługuje wielostrefowego kalendarza.

Szczegółowy zakres: INTEGRALNOSC.md. Reguły kolizji między rekordami są egzekwowane w aplikacji; dowolny bezpośredni SQL może je ominąć. Historia nie jest kryptograficznie niezmiennym rejestrem. Migracja nie odtwarza nieznanej dawnej historii — oznacza stan początkowy.

## Uruchomienie

Wymagania: PHP 8.1+ z pdo_mysql i mbstring, MySQL 8.0.16+ lub współczesna MariaDB z egzekwowanym CHECK. GD/fileinfo są potrzebne do uploadu ilustracji. Apache 2.4 z .htaccess na hostingu; lokalnie można użyć router.php, ale wbudowany serwer PHP nie zastępuje ochrony .htaccess.

1. Pusta fikcyjna baza: import database/nowa_baza_INFINITYFREE.sql. Ten plik kasuje tabele i nie służy do aktualizacji istniejących danych. database/database.sql również odtwarza całość i wybiera lokalną grand_access_hotel.
2. Istniejące 007: backup plików i bazy, odczyt PRZED_008_ODCZYT.sql, wyłącznie 008_terminarz.sql, a po pełnym sukcesie marker 008_terminarz. Nie ponawiaj 003–007. Szczegóły: WDROZENIE_INFINITYFREE.md.
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
