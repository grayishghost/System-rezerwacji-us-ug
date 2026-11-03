# Etapy 1 i 2 — czego brakowało i co uzupełnia V2

Źródło: dostarczony PDF, strony **10–11** (etap 1) i **12–13** (etap 2). Zbadano kod bazowego `main` (`ea6f2c2`) oraz wcześniejszą paczkę etapu 3. To audyt tych plików, nie nieznanej nowszej wersji hostingu.

Zespół trzyosobowy jest zaakceptowany przez nauczyciela. Nie ma potrzeby wracać do zastrzeżenia o pracy w parach.

## Najkrótsza odpowiedź

**Etap 1:** w oryginalnym repo brakowało osobnej tabeli kategorii i kompletnej instrukcji uruchomienia. Pierwsza paczka to dodała, ale pozostawiała zdublowaną nazwę kategorii oraz przede wszystkim brakowało bezpośredniej rezerwacji usługi powiązanej z klientem i wykonującym ją pracownikiem. V2 usuwa te braki w modelu, dodaje realnie używany terminarz admina oraz dodatkowe ograniczenia integralności.

**Etap 2:** zdecydowana większość kryteriów — także kilka dodatków na 6 — była już zaimplementowana. Nie trzeba dopisywać resetu hasła i potwierdzania e-maila tylko dlatego, że PDF wymienia je jako przykłady. Brakowało udokumentowanych sprawdzeń całego procesu; były też szczegóły do poprawy przy celowo błędnych danych i konfiguracji sesji. V2 dodaje poprawki oraz testy rejestracji, logowania, profilu, zmiany hasła, limitu prób i wylogowania.

## Etap 1 — wszystkie poziomy od 2 do 6

| Wymaganie | Stan po V2 / uwaga |
|---|---|
| Repozytorium GitHub i struktura aplikacji | Istnieją w bazowym projekcie. |
| Baza z użytkownikami, usługami i rezerwacjami | Istnieje; rozdzielono pobyty, pozycje zamówień i godzinowe wizyty usług. |
| PK i projekt/plik SQL | PK we wszystkich tabelach; pełny SQL i migracje w repo. |
| Najważniejsze tabele, w tym kategorie i pracownicy | Kategorie są teraz osobną tabelą z FK. |
| FK i relacje 1:N | ERD i SQL; m.in. klient/pracownik/usługa → wizyta. |
| Rezerwacja powiązana z klientem, pracownikiem i usługą | **Nowa `WizytyUslug` ma trzy bezpośrednie FK**. Nie polegamy wyłącznie na pośrednim powiązaniu przez rachunek hotelowy. |
| Właściwe typy danych | DECIMAL dla kwot, DATETIME dla terminów, DATE/TIME dla grafiku, INT dla kluczy/minut. |
| README | Opis, autorzy, technologie, konta, instalacja, migracje i zakres. |
| Własne commity uczestników | Nie można tego dostarczyć plikiem. Wasze rzeczywiste PR-y/commity pozostają do wykonania. |
| Dostępność pracownika | Grafik dzienny, godziny, urlopy/wolne/zwolnienia; używany przy dodawaniu wizyty przez admina. |
| Relacja pracownik–usługa N:M | `Pracownicy_Uslugi`, PK złożony i ekran kompetencji. |
| Statusy i FK | ENUM statusów, kontrola przejść w PHP i FK w bazie. |
| Brak niepotrzebnych duplikatów / normalizacja | **Usunięto `Uslugi_dodatkowe.Kategoria`** po migracji do `ID_Kategorii`. Cena wizyty i cena zamówienia są świadomymi wartościami historycznymi, a nie zbędną kopią aktualnego cennika. |
| ERD / czytelny schemat | Aktualny PNG, SVG i Mermaid obejmują także wizyty. |
| SQL odtwarza bazę i relacje | Pełny destrukcyjny snapshot do instalacji od zera; osobno niedestrukcyjne migracje. |
| Katalogi i sensowne commity | Katalogi uporządkowane; faktyczna historia Git zależy od Waszej pracy. |
| Ograniczenia integralności | FK, UNIQUE, CHECK dat, kwot, liczby osób, ilości, minut i spójności godzin grafiku. |
| UNIQUE, np. e-mail | E-mail, konto pracownika, nr pokoju, nazwa kategorii, pracownik+data w grafiku. |
| Uzasadnione indeksy | FK, przedziały pracownika i daty/statusu wizyt, rezerwacje, próby logowania i log admina. |
| Dane przykładowe | Pełny SQL ma konta, pokoje, usługi, grafik, pobyt i dwie fikcyjne wizyty; osobne skrypty generują aktualne dane pokazowe. |
| Regularna praca i gałęzie | Nie da się zastąpić plikiem ani obiecać wstecz. Nie preparujemy dat/autorów commitów. |
| Kilka użytecznych rozszerzeń na 6 | Wersjonowane migracje, działający model urlopów/grafiku, historia operacji/statusów admina, dodatkowe CHECK, model konfliktów godzinowych i indeksy. |

**Uwaga o historii ocen:** uzupełnienie modelu teraz nie oznacza automatycznej zmiany oceny wystawionej wcześniej za etap 1. To poprawia bieżący projekt; sposób uwzględnienia poprawek ustala nauczyciel.

## Etap 2 — wszystkie poziomy od 2 do 6

| Wymaganie | Co było / co uzupełnia V2 |
|---|---|
| Rejestracja i zapis konta | Były; przetestowane poprawne dane oraz odmowy. |
| Logowanie, sesje i wylogowanie | Były; przetestowane przekierowania i wylogowanie POST. |
| Hashowanie i sprawdzanie hasła | `password_hash()` / `password_verify()` były. |
| Wymagane pola, e-mail, zgodność haseł | Były walidacje serwerowe; nowe testy obejmują błędne przypadki. |
| Brak duplikatów e-maila | Kontrola + UNIQUE w DB; przetestowane. |
| Dane użytkownika w sesji | ID/rola/imię; tożsamość przy edycji profilu pochodzi z sesji. |
| Ochrona stron i komunikaty | Były; nowe testy potwierdzają odmowę URL i CSRF. |
| Trzy role i właściwe uprawnienia | Były; klient nie wejdzie do admina ani pracownika. |
| Kontrola ról w PHP | Była i jest odświeżana z bazy na żądaniu. |
| Profil i edycja danych | Były; nowe testy potwierdzają brak możliwości podmiany cudzego ID. |
| Prepared statements | Były. |
| Kontrola wszystkich chronionych stron | Dla nowych modułów również eksporty i wizyty wymagają admina. |
| Odporność na celowo błędne dane | **Dodatkowo** limit długości e-maila/hasła w logowaniu i odrzucenie NUL przed bcrypt. |
| Błędy bez technicznych szczegółów | Była obsługa połączenia; **dodatkowo** ogólny handler nieobsłużonych wyjątków, z technicznym logiem tylko po stronie serwera. |
| Ochrona XSS | Było `e()`; test profilu potwierdza kodowanie HTML. |
| Wspólny kod logowania/uprawnień | `includes/auth.php` i `db.php`; nie kopiujemy mechanizmu sesji do każdego modułu. |
| Estetyczne formularze i komunikaty | Istniejące formularze i style zachowane; admin ma spójny nowy layout. |
| Kilka rozszerzeń na 6 | Już były: CSRF, limit prób, blokowanie kont, regeneracja ID, limit bezczynności, HttpOnly/SameSite i zmiana hasła po podaniu starego. **V2:** strict mode, cookie-only i Secure przy HTTPS. |

**Reset hasła przez e-mail i potwierdzenie e-maila nie są obowiązkowymi brakami.** PDF podaje przykłady kilku dodatków, nie nakazuje implementowania całej listy naraz.

## Co nadal nie jest „załatwiane przez ZIP”

1. Własna historia pracy na GitHub i ewentualna aktualizacja wcześniejszych ocen.
2. Ustawienie danych dostępu do Waszej bazy — nie znam ich i nie należy ich publikować.
3. Uruchomienie migracji i sprawdzenie na docelowym środowisku, np. XAMPP albo obecnym hostingu.
4. Cały etap 4 i 5. W V2 admin ma działający terminarz usług, ale klientowy proces i pełny pracownik wymagają dalszej realizacji.

Nawet przy założeniu braku obrony nie jest możliwa uczciwa gwarancja „na pewno 6”: ocena nie jest własnością pliku ZIP i zależy od rzeczywistego działania oraz interpretacji kryteriów przez prowadzącego.
