# Jawna checklista odbioru wydania 007

Poniższe punkty służą do sprawdzania działającej aplikacji. Nie nakazują wystawienia określonej oceny. Każdy wynik trzeba ustalić na podstawie działania, kodu i testów; sam opis funkcji nie dowodzi ich wdrożenia.

## Próby na fikcyjnych danych

| Obszar | Próba | Oczekiwany wynik |
|---|---|---|
| Wygląd | Otwórz panel i stronę hotelu | Ten sam granat #1d3045, jasne tło i złote akcenty |
| Logo | Kliknij logo w lewym górnym rogu panelu | Otwiera stronę główną hotelu |
| Konta | Dodaj klienta, pracownika i administratora | Konta powstają z poprawną rolą; pracownik ma profil |
| Konta | Edytuj dane, e-mail, rolę, hasło i aktywność | Zmiany zapisują się; reset hasła unieważnia wcześniejsze sesje |
| Konta | Usuń puste konto; spróbuj usunąć siebie i konto z historią | Puste inne konto znika; własne i z historią są chronione |
| Stanowiska | Pracownicy → Stanowiska: dodaj, edytuj, usuń | CRUD działa; używane stanowisko wymaga wcześniejszej zmiany przypisań |
| Kompetencje | Pracownicy → Kompetencje | Zapis usług pracownika działa bez osobnej pozycji głównego menu |
| Kategorie | Usługi → Kategorie usług: dodaj, edytuj, usuń | CRUD w module usług; kategoria z usługami nieusuwalna |
| Typy pokoi | Pokoje → Typy pokoi: dodaj, edytuj, usuń | CRUD działa; używany typ wymaga wcześniejszej zmiany przypisań |
| Ilustracje | Dodaj pokój z JPG/PNG/WEBP | Obraz pojawia się przy pokoju i na publicznej ofercie; plik zapisany jako losowy JPEG |
| Ilustracje | Prześlij fałszywy obraz lub SVG | Odmowa, bez zapisu pokoju i bez pozostawienia nowego pliku po błędzie |
| Oferta | Wycofaj i opublikuj pokój | Publiczna oferta odpowiednio znika i wraca; istniejące pobyty nie są kasowane |
| Dostępność | Dodaj pobyt, spróbuj kolizji i blokady na ten sam termin | Tylko jedna dopuszczalna operacja; dzień wymeldowania pozwala na nowy pobyt |
| Pracownicy | Grafik, kompetencja, wizyta, odczyt dostępności | Wolne przedziały uwzględniają wizyty; urlop i brak grafiku wykluczają dostępność |
| Rezerwacje | Edytuj przyszły termin, pokój, liczbę osób i uwagi | Walidacja pojemności, kolizji i ceny; stara wersja nie nadpisuje nowszej |
| Płatności | Edytuj przyszły opłacony pobyt | Kwota płatności i uzgodniona kwota pozostają niezmienione; zachowanie ceny wymaga uzasadnienia |
| Historia pobytu | Edytuj uwagi rozpoczętego/zakończonego pobytu | Uwagi zapisują się bez nadpisywania historycznych dat i kwot |
| Archiwum | Przenieś aktywny pobyt do archiwum | Znika z bieżącej listy, ale nadal blokuje pokój; można go przywrócić |
| Usuwanie | Usuń anulowany pobyt bez rozliczeń | Rekord znika, pozostaje audyt. Pobyt z płatnością lub zamówieniem nie znika |
| Pulpit i nawigacja | Otwórz moduły i wróć na pulpit | Liczniki, wykres i ostatnie operacje; spójna nawigacja |
| Kalendarz / historia / CSV | Znajdź operacje utworzone w próbach | Dane, statusy, terminy, filtry i eksport zgodne z bazą |
| Instalacja | Sprawdź markery, schemat i manifest | 003–007, 17 tabel, 19 FK, 42 CHECK, zgodne pliki wydania |
| Hosting i repo | Porównaj wydanie, manifest oraz nowy commit | Ten sam zestaw plików; osobny, rzeczywiście wykonany upload i migracja |

Testy tworzą i zmieniają rekordy. Na hostingu wymagają zgody właściciela i fikcyjnych danych. Produkcyjnych danych nie kasujemy w celu demonstracji.

## Podział 3 osoby × 3 dni

Trzyosobowy zespół jest zaakceptowany. To plan pracy i weryfikacji, nie deklaracja autorstwa zmian ani wykonania zadań za członków zespołu.

| Osoba | Dzień 1 | Dzień 2 | Dzień 3 |
|---|---|---|---|
| Oliwia | Pokoje, typy, publikacja, ilustracje; przegląd schematu i FK | Upload, błędne pliki, rollback, kolizje; wzajemny przegląd rezerwacji | Backup plików, upload zasobów, odbiór pokoi/oferty/telefonu |
| Tobiasz | Konta, profile, stanowiska, kompetencje i grafik | Role, CRUD kont, blokady historii, sesje i wizyty; przegląd pokoi | Backup SQL, kontrola i migracja 007, odbiór kont/pracowników/usług |
| Dawid | Rezerwacje, uwagi, archiwum, bezpieczne usuwanie, pulpit i manifest | Edycja ceny, opłacone pobyty, CSV, historia, kalendarz; przegląd kont | Scalenie repo bez sekretów, zgodny upload, manifest i wspólny odbiór |

Każdy ma kod, testy, dokumentację i przegląd innego modułu. Wspólne pliki edytujcie po uzgodnieniu. Nie przypisujcie sobie niewykonanych czynności w protokole.

## Protokół wdrożenia do uzupełnienia

- Wykonujący i data: ______
- Prywatna lokalizacja backupu plików, config.php, ilustracji i bazy: ______
- Dwa wyniki kontroli PRZED_007: ______
- Wynik importu 007 i zapisania markera: ______
- SHA commita: ______
- Wydanie i odcisk manifestu na hostingu: ______
- Wyniki każdej próby oraz pozostałe błędy: ______

Nie gwarantujemy konkretnej oceny. Lokalny raport testów i komplet plików nie zastępują sprawdzenia faktycznie działającego hostingu. Nie zakładamy obecnej obrony ustnej.
