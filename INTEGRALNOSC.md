# Integralność danych i reguły rezerwacji

Wydanie GAH-FULL-006-r1 zawiera ograniczenia bazy oraz reguły transakcyjne w PHP. Nie istnieje jeden uniwersalny zestaw zasad dla wszystkich hoteli. To podstawy odpowiednie dla tego projektu rezerwacji, nie deklaracja kompletnego komercyjnego systemu PMS, księgowości lub obsługi prawnej hotelu.

## Warstwa bazy danych

| Reguła | Realizacja |
|---|---|
| Jednoznaczna identyfikacja rekordów | Klucze główne we wszystkich 17 tabelach |
| Istniejący klient, pokój, pracownik, usługa itd. | 19 kluczy obcych |
| Brak duplikatów identyfikatorów biznesowych | UNIQUE adresu e-mail, numeru pokoju, profilu pracownika dla konta, nazwy kategorii, wpisu grafiku pracownik/dzień; złożony PK kompetencji |
| Brak wymaganych wartości NULL | NOT NULL odpowiednich kolumn; historyczne wyjątki opisane niżej |
| Nieujemne kwoty i dodatnie ilości | CHECK cen, kwoty pobytu, płatności, zamówień, pojemności i liczby osób |
| Koniec po początku | CHECK dat pobytu, wizyty i blokady oraz godzin grafiku |
| Typ dnia zgodny z godzinami | Praca wymaga obu godzin; urlop, wolne i zwolnienie mają obie godziny NULL |
| Dodatni czas usługi | CHECK 1–1440 minut |
| Flagi tylko 0/1 | CHECK aktywności konta, pracownika, pokoju oraz dostępności usługi |
| Poprawny numer i wersja | Dodatni numer pokoju, wersje pokoju/pobytu/wizyty co najmniej 1 |
| Niepuste podstawowe nazwy | CHECK po TRIM dla imienia, nazwiska, e-maila, stanowiska, typu pokoju, usługi, kategorii i powodu blokady |
| Dozwolone role, statusy i typy dni | ENUM oraz dodatkowe CHECK, także przeciw pustej wartości ENUM przy wyłączonym trybie strict |
| Nieujemna płaca | CHECK Placa_brutto >= 0 |
| Godziny grafiku w obrębie doby | CHECK początku od 00:00 i końca przed 24:00 |
| Zachowanie powiązanych rozliczeń | RESTRICT zamiast CASCADE na powiązaniach płatności i zamówienia usługi z pobytem |

Łącznie w pełnym schemacie są 37 ograniczeń CHECK, w tym 23 nowe w migracji 006. Migracja 006 nie usuwa rekordów i nie podmienia historycznych kwot. Rozpoznaje istniejące nazwy dwóch kluczy obcych przez information_schema, zamiast zakładać ich nazwę na hostingu.

Płatności i zamówienia nie są dziennikiem księgowym: ich własne rekordy nadal można zmienić/usunąć kontem SQL z odpowiednimi prawami. RESTRICT zapobiega konkretnie ich niezamierzonemu usunięciu razem z rezerwacją. Historia panelu również nie jest niezmiennym audytem kryptograficznym.

## Reguły między rekordami — PHP i transakcje

Te reguły nie są samodzielnymi CHECK w MySQL, ponieważ porównują wiele wierszy lub tabel:

- brak nakładania aktywnych pobytów tego samego pokoju;
- brak nakładania pobytu i blokady oraz dwóch blokad;
- liczba nowych gości nieprzekraczająca pojemności pokoju;
- brak nowych rezerwacji wycofanego pokoju lub pokoju w serwisie;
- brak nakładania wizyt pracownika;
- aktywne konto, prawidłowa rola, kompetencja i godziny pracy pracownika;
- termin wizyty mieszczący się w grafiku, poza urlopem;
- dozwolone przejścia statusów i brak przywracania statusów końcowych;
- kontrola wersji formularza i zakaz zmiany opłaconego pobytu przez edytor;
- kontrola zmiany pojemności/typu/serwisu pokoju z aktywnymi pobytami.

Zapisy panelu i klienta blokują odpowiedni rekord pokoju albo pracownika w transakcji. Równoczesne próby zapisu są w ten sposób serializowane. Testy obejmują rezerwacja–rezerwacja, rezerwacja–blokada, blokada–blokada i konkurujące wizyty.

**Bezpośredni INSERT/UPDATE w phpMyAdmin może ominąć powyższe reguły PHP. Nie dodano triggerów ani bazowej blokady przedziałów, która chroniłaby przed dowolnym obcym programem zapisującym SQL.** Konta dostępu do bazy muszą być prywatne; nie wolno ręcznie dodawać rezerwacji z pominięciem aplikacji. Gdy bazę będzie zasilać więcej systemów, potrzebna jest dodatkowa wspólna warstwa zapisów albo odpowiednio zaprojektowane zabezpieczenie bazodanowe. Nie należy zastępować braku nakładania zwykłym UNIQUE na pokoju i dacie początku — to nie rozwiązuje kolizji przedziałów.

## Przyjęte zasady projektu, a nie uniwersalne prawo hotelowe

- Pobyt jest dobowy i korzysta z przedziału [DataOd, DataDo). Dzień wymeldowania pozwala na następny pobyt. Godzin check-in/check-out i bufora sprzątania nie modelujemy.
- Nowe pobyty można tworzyć od bieżącego dnia na maksymalnie 366 nocy; reguła jest wspólna dla klienta i administratora. To limit aplikacji, nie branżowy standard.
- Wizyta mieści się w jednym dniu pracy. System nie obsługuje zmian nocnych przechodzących przez północ.
- Dostępność pracownika dotyczy wizyt usług. Opiekun pobytu nie jest zajęty przez wszystkie doby rezerwacji.
- Kwoty są przechowywane jako DECIMAL w PLN. Historyczna kwota pobytu/cena wizyty nie musi odpowiadać dzisiejszemu cennikowi. Ujemna płatność nie jest tu modelem zwrotu.
- Starsze pobyty mogą mieć LiczbaOsob=NULL, ponieważ poprzednia wersja nie zbierała tej informacji. Nie wymyślamy liczby gości podczas migracji. Każda nowa rezerwacja przez aplikację wymaga dodatniej liczby osób i sprawdzenia pojemności. Baza nadal dopuszcza NULL dla tej kolumny z powodu historii.
- Nie wymuszamy, aby historyczny klient lub pracownik pozostawał aktywny na zawsze. Dezaktywacja nie usuwa historii.
- Nie dodano obowiązkowej przedpłaty, limitu wieku, dokumentów gości ani automatycznej opłaty za anulowanie. To wymaga uzgodnionej polityki konkretnego hotelu i odpowiednich podstaw prawnych, a nie arbitralnego CHECK.

## Wdrożenie na istniejących danych

1. Backup plików i eksport bazy, okno serwisowe bez nowych zapisów.
2. Ustal rzeczywisty stan migracji. Wykonaj wyłącznie brakujące 003, 004 i 005.
3. Przed 006 wykonaj wyłącznie odczyt `database/PRZED_006_ODCZYT.sql`. Wszystkie 27 liczników powinno wynosić zero. Wynik większy od zera wymaga analizy i poprawienia konkretnych danych przez właściciela; nie kasuj historii i nie dopisuj markera dla pozoru.
4. Dopiero potem wykonaj `database/migrations/006_integralnosc.sql`. Po pełnym sukcesie zapisz marker 006 według instrukcji wdrożenia.
5. Kontrola instalacji ma pokazać wymagany schemat, zgodne pliki i wyniki odczytu danych.

Migrator CLI wykonuje ten sam odczyt przed rozpoczęciem 006 i zatrzymuje się, jeśli znajdzie naruszenie. DDL nie jest atomowe; po błędzie samego ALTER trzeba zbadać częściowy stan i przywrócić spójny backup lub naprawić konkretny problem.

Nie wyłączaj CHECK ani FOREIGN_KEY_CHECKS w celu przepchnięcia błędnej migracji. Ograniczenia nie są zastępstwem dla backupu, kontroli dostępu i testu na rzeczywistym hostingu.
