# Etap 5 — trzy osoby, trzy dni, rzeczywista praca

Plan przyjęty dla 24–26.11.2026; termin PDF: 27.11.2026. To harmonogram zespołu, nie deklaracja już wykonanych commitów. Trzy osoby są zaakceptowane. Zakres nie obejmuje prezentacji ani obrony.

Paczki są materiałem do przeglądu, uruchomienia, testów i integracji. Sam upload nie oznacza autorstwa całego dostarczonego kodu. Każdy używa własnego konta i opisuje rzeczywiście wykonane czynności. Nie cofamy dat commitów i nie tworzymy fikcyjnej historii. Jeśli pracujecie w inne dni, zmieńcie harmonogram na prawdziwe daty.

## Podział obowiązków

| Dzień | Oliwia Drygiel — daty i logika | Tobiasz Mućko — bezpieczeństwo i wydanie | Dawid Domagała — kalendarz i integracja |
|---|---|---|---|
| 1 / 24.11 | Wspólna ścisła data, podłączenie auth i walidacji admina; uzupełnienie brakujących historycznych dokumentów wymagań/testów. Test poprawnych dat, 29.02 i NUL. Przegląd ICS Dawida | Nagłówki, CSP, zewnętrzny JS pobytu, test odczytu HTTP; przywrócenie spójnych dokumentów 008. Test stron i konsoli bez łamania CSP. Przegląd dat Oliwii | Generator ICS i jego testy bez bazy; uzgodnienie jednostrefowego czasu, pól prywatnych i braku synchronizacji. Naprawa świeżego SQL i brakującego PRZED_008 jako plików repo, bez importu na hosting. Przegląd nagłówków Tobiasza |
| 2 / 25.11 | Podłączenie reguł do pobytów i terminarza, wspólne kolizje, CLI test_regul. Sprawdzenie 7 wymaganych wariantów przedziałów, dat i konfliktu zapisu | Wzmocnienie manifestu i testy jego uszkodzeń; CLI odczytu instalacji. Sprawdzenie braku pliku, zmiany hasha i zgodności schematu bez zapisów | Endpoint ICS, link w szczegółach klienta/pracownika i instrukcja. Test własnej/cudzej wizyty, gościa, pobrania załącznika i braku danych kontaktowych |
| 3 / 26.11 | Aktualna mapa kryteriów i raport; oznaczenie historycznych raportów. Odbiór pełnej rezerwacji, kolizji, anulowania, przełożenia, telefonu i wersji bez JS | Aktualna instrukcja wdrożenia i integralności, końcowy manifest; odbiór diagnostyki na hostingu, prywatności i backupu. Przegląd raportu Oliwii | README, plan/odbiór i integracja całości; próba świeżej fikcyjnej bazy lokalnie, ERD i SQL, zgodność main i hostingu. Przegląd instrukcji Tobiasza |

Obciążenie jest wyrównane zakresem kodu, testów i odpowiedzialności, nie sztuczną identyczną liczbą plików. Zarezerwujcie po około 60–90 minut dziennie na osobę; jeśli test wykaże błąd, poprawa ma pierwszeństwo przed liczbą commitów.

## Kolejność pracy i scalania

1. Backup aktualnego repo i hostingu. Porównana baza Git: 84cdee975a4f34007250fd1e6135e40ac2ebe9b0. Jeśli main jest nowszy, przejrzyjcie różnice, nie nadpisujcie ich w ciemno.
2. Dzień 1: każdy zakłada gałąź z aktualnego main, bierze własny folder projekt z paczki DZIEN_1. Po testach otwiera PR. Scalenie Oliwia → Dawid → Tobiasz; Tobiasz scala manifest dnia jako ostatni. Zależności auth/reguly oraz db/bezpieczenstwo muszą znaleźć się razem na hostingu.
3. Dzień 2: najpierw pobierzcie scalony dzień 1. Nowe gałęzie i własne paczki DZIEN_2. Znowu Oliwia → Dawid → Tobiasz. Manifest dnia jako ostatni.
4. Dzień 3: analogicznie po dniu 2. Uzupełnijcie realne wyniki odbioru, nie sam plan. Ostateczny manifest i wdrożenie po wszystkich częściach.
5. Hosting aktualizuje jedna osoba spójną paczką dzienną dopiero po scaleniu całego dnia. Nie przesyłajcie oddzielnie pojedynczych paczek autorów na działającą stronę.

W każdej paczce osobistej jest lista ścieżek i sugerowany opis commita. Wgrajcie zawartość projekt do głównego katalogu gałęzi, nie ZIP i nie nowy folder projekt. Nigdy prywatny includes/config.php, eksport hostingu, lokalne logi ani własne hasła.

## Sugerowane opisy — tylko po wykonaniu czynności

| Dzień | Oliwia | Tobiasz | Dawid |
|---|---|---|---|
| 1 | Etap5: przegląd i test ścisłych dat oraz uzupełnienie mapy 008 | Etap5: integracja CSP i test zasobów strony | Etap5: generator ICS, testy UTF-8 i spójny SQL 008 |
| 2 | Etap5: wspólne reguły pobytów i terminarza, test kolizji | Etap5: walidacja manifestu i odczyt instalacji CLI | Etap5: prywatny eksport wizyty, test własności i pobierania |
| 3 | Etap5: odbiór rezerwacji i aktualny raport wymagań | Etap5: sprawdzenie wdrożenia, prywatności i wydania | Etap5: README, świeża baza testowa i integracja końcowa |

## Odbiór hostingu — do rzeczywistego wypełnienia

- [ ] Backup zrobiony i zachowany poza publicznym htdocs.
- [ ] Zachowane includes/config.php i własne assets/pokoje; bez ponownego importu 008.
- [ ] Diagnostyka administratora: schemat gotowy, oczekiwane wydanie/odcisk i liczba plików, zero niezgodności.
- [ ] Wszystkie 29 odczytanych reguł danych pokazują 0. Jeśli nie, STOP i analiza, nie kasowanie danych.
- [ ] Rejestracja testowego klienta, logowanie i prawidłowe uprawnienia.
- [ ] Oferta usług → pracownik → dzień → godzina → podsumowanie → zapis; zapis widoczny po odświeżeniu.
- [ ] Konflikt, urlop i brak kompetencji odrzucone; druga próba nie dubluje zapisu.
- [ ] Własna przyszła wizyta: przełożenie, anulowanie i historia; pracownik widzi wyłącznie własne wizyty.
- [ ] Pobranie ICS ma właściwy termin; nie zawiera telefonu, e-maila i uwag; cudze ID nie udostępnia wizyty.
- [ ] Klient/admin/pracownik na telefonie, bez brakujących zdjęć/fontów/skryptów.
- [ ] Ustalony aktualny grafik i kompetencje w bazie hostingu. Brak wolnych godzin nie zawsze oznacza błąd programu.
- [ ] Repo ma rzeczywiste zmiany i przeglądy trzech osób; końcowe pliki odpowiadają hostingowi.

Nie zaznaczono tych punktów za użytkownika. Lokalny raport nie jest dowodem wdrożenia na InfinityFree.
