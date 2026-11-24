# Etap 4 — odbiór i równy podział 3 osoby × 3 dni

Termin z PDF: 20.11.2026. Plan przyjmuje wskazane przez zespół krótsze okno 16–18.11. Trzyosobowy skład został zatwierdzony. Podział dotyczy odpowiedzialności za przegląd, testy, poprawki i integrację — nie jest deklaracją, że każda osoba samodzielnie napisała wygenerowane pliki.

PDF wyraźnie mówi, że samo przesłanie cudzych plików nie oznacza wkładu. Każda osoba powinna rzeczywiście przejrzeć swój moduł, wykonać przypisane próby, rozumieć zmiany i zapisać prawdziwy opis commita. Nie fabrykuj dat, autorów, historii pracy ani wyników.

## Dokładnie 12 plików na osobę

Pliki są rozłączne. Razem 36 nowych/zmienionych plików wobec kompletnego 007. Nie dzielimy jednego pliku między dwa równoczesne uploady. Współdzielone zależności scalcie przed wdrożeniem. Manifest należy do ostatniej integracji Dawida, a nie do każdej części z osobna.

### Oliwia — klient, wybór i interfejs

```text
public/uslugi.php
public/usluga.php
public/klient/rezerwuj.php
public/klient/terminy.php
public/klient/index.php
public/klient/wizyta.php
public/klient/kalendarz.php
js/terminarz.js
css/panel.css
includes/header.php
ETAP4_WYMAGANIA.md
database/PO_008_ODCZYT.sql
```

Odpowiedzialność: przejść cały proces na komputerze i telefonie, sprawdzić ofertę, AJAX i brak JS, podsumowanie, własność danych, kalendarz oraz powiązanie każdego punktu PDF z ekranem. Przejrzeć z Tobiaszem serwerową kontrolę żądań. Zweryfikować PO_008 na fikcyjnej bazie i zapisać wyniki.

### Tobiasz — wspólna logika i pracownik

```text
includes/terminarz.php
includes/terminarz_widok.php
includes/auth.php
database/migrations/008_terminarz.sql
includes/admin_wizyty.php
public/admin/wizyty.php
public/pracownik/index.php
public/pracownik/wizyta.php
includes/instalacja.php
public/admin/instalacja.php
database/DEMO_TERMINARZ.sql
INTEGRALNOSC.md
```

Odpowiedzialność: kontrola grafików i ról, wszystkie konflikty z PDF i obie granice, dwa równoległe klienty, statusy, transakcje, rollback, historia, nieaktywny profil. Z Oliwią sprawdzić przełożenie bez zmiany uzgodnionej ceny/czasu; z Dawidem przejrzeć migrację i ograniczenia SQL.

### Dawid — baza, migracje i integracja wydania

```text
database/database.sql
database/nowa_baza_INFINITYFREE.sql
scripts/migrate_etap3.php
database/PRZED_008_ODCZYT.sql
database/ERD_AKTUALNE.dot
database/ERD_AKTUALNE.svg
database/SCHEMAT.md
README.md
WDROZENIE_INFINITYFREE.md
ODBIOR_I_PODZIAL.md
ETAP4_TESTY.md
includes/wydanie.json
```

Odpowiedzialność: odtworzyć fikcyjną bazę, porównać schemat z ERD, sprawdzić zachowanie danych po 007 → 008, przerwanie częściowej migracji i brak fałszywych markerów, poprawiony runner 007/008. Scalić wszystkie części, sprawdzić manifest i zgodność paczki. Razem wykonać backup oraz odbiór hostingu.

## Trzy dni — po 4 pliki dziennie na osobę

Kolejność poniżej odpowiada kolejności list w każdej paczce. To plan commitów do przeglądu i scalenia, nie pozwolenie na wdrażanie częściowo zmienionego hostingu.

| Osoba | Dzień 1 — 16.11 | Dzień 2 — 17.11 | Dzień 3 — 18.11 |
|---|---|---|---|
| Oliwia | Pierwsze 4 pliki listy: oferta i wybór. Przejście poziomów 2–3 na spójnym projekcie lokalnym | Kolejne 4: własne wizyty, kalendarz, AJAX. Własność, brak JS, urządzenia | Ostatnie 4: styl/nawigacja, mapa wymagań i odczyt po migracji. Pełny odbiór oraz przegląd pracownika |
| Tobiasz | Pierwsze 4: wspólna logika, sesja i 008. Przegląd reguł 2–3, transakcji i schematu | Kolejne 4: administrator/pracownik. Reguły 4–5, wszystkie przedziały i równoczesność | Ostatnie 4: diagnostyka, demo i opis integralności. Rozszerzenia 6, regresja, przegląd klienta |
| Dawid | Pierwsze 4: pełne SQL, runner i odczyt przed 008. Backup i migracja na kopii | Kolejne 4: ERD/schemat/README. Porównanie danych i ograniczeń, przegląd logiki Tobiasza | Ostatnie 4: wdrożenie, odbiór, testy, manifest. Ostateczne scalenie, upload i wspólna kontrola |

## Jak pracować w repozytorium

- Zapiszcie punkt wyjściowy i zróbcie prywatny backup. Każdy pracuje ze swojego konta i na własnej gałęzi, np. etap4/oliwia-klient, etap4/tobiasz-terminarz, etap4/dawid-integracja.
- Używajcie opisów odpowiadających rzeczywistemu zakresowi: „Etap 4: oferta i formularz klienta — przegląd i integracja”, „Etap 4: kontrola przedziałów i panel pracownika”, „Etap 4: migracja 008, ERD i kontrola wydania”. Jeśli dany commit nie zawiera wszystkich tych czynności, nazwę zawęźcie.
- Każda paczka osobowa ma folder projekt. Do odpowiedniej gałęzi trafia jego ZAWARTOŚĆ, nie ZIP ani dodatkowy folder projekt. Nie przesyłajcie prywatnego config.php, produkcyjnej bazy ani dowodów z danymi osobowymi.
- Przeglądajcie pull requesty i scalajcie rozłączne pliki. Nie usuwajcie plików spoza paczek. Poprawki wspólnych plików wymagają uzgodnienia i ponownego testu.
- Przy niezmienionych plikach z paczek po scaleniu manifest musi być zgodny z pełnym kompletem. Jeśli wprowadzicie własne poprawki, trzeba uczciwie przeliczyć manifest dla końcowego stanu, a nie ukrywać różnice.
- Na hosting wgrywacie dopiero CAŁOŚĆ po scaleniu i testach, jednym spójnym wdrożeniem. Nigdy pojedynczej paczki osoby. GitHub nie aktualizuje automatycznie plików PHP ani bazy InfinityFree.

## Bramki odbioru, dokładnie w tej kolejności

1. 2 — publiczna oferta, wybór usługi/pracownika/daty/godziny i zapis.
2. 3 — właściwi pracownicy, klient z sesji, własna lista/szczegóły, zakaz cudzych danych, zajętość.
3. 4 — grafik, przeszłość, zajęty termin, anulowanie, statusy i panel pracownika.
4. 5 — czas trwania, pięć konfliktów z PDF, obie granice, kontrola serwera, role i dwa równoległe zapisy.
5. 6 — AJAX, automatyczne godziny, różne dni/godziny/urlopy, kalendarz, przełożenie i historia.
6. Regresja wcześniejszych etapów, telefony, brak JS, brak komentarzy/sekretów, schemat, manifest i rzeczywisty hosting.

Każda bramka obejmuje poprzednie. Szczegółowy scenariusz: ETAP4_TESTY.md. Gdy próba nie przechodzi, nie zastępuj jej dodatkiem i nie oznaczaj kolejnego poziomu jako zakończonego.

## Protokół hostingu — do faktycznego uzupełnienia

- Wykonujący i data: ______
- Prywatna lokalizacja sprawdzonego backupu plików i bazy: ______
- Potwierdzony stan 003–007 przed aktualizacją: ______
- Dwa wyniki PRZED_008: ______
- Pełny sukces importu 008 i dopiero wtedy marker: ______
- Dwa wyniki PO_008: ______
- SHA końcowego commita i wynik scalenia 3 gałęzi: ______
- Wydanie, manifest i liczba zgodnych plików na hostingu: ______
- Rzeczywisty grafik i kompetencje oraz wyniki bramek 2–6: ______
- Wyniki telefonu, braku JS i regresji: ______
- Pozostałe błędy lub ograniczenia: ______

Nie deklarujemy wyniku niewykonanych prób, przyszłego wkładu ani konkretnej oceny. Nie zakładamy obecnej obrony ustnej. Gotowy kod i lokalne testy są dowodem zakresu przygotowania, a nie zastępstwem rzeczywistego działania strony po wdrożeniu.
