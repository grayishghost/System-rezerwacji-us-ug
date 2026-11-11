# Odbiór funkcji ze zdjęcia i podział pracy

## Checklista na właściwym hostingu

Testuj na fikcyjnych danych. Testy tworzą i zmieniają rekordy — wymagają zgody właściciela bazy. Dotychczasowe sprawdzenie gah.gt.tc było wyłącznie do odczytu. Pola poniżej są do odhaczenia dopiero po wdrożeniu, nie są deklaracją zdalnego sukcesu.

| Uwaga nauczyciela | Gdzie sprawdzić | Próba odbiorowa |
|---|---|---|
| Zarządzanie rezerwacjami | Pobyty | Dodaj przyszły pobyt klientowi; zmień termin i pokój; sprawdź przeliczoną cenę, status, opiekuna, szczegóły, filtry i CSV. Próba kolizji musi być odrzucona. Anuluj zamiast usuwać historię. |
| Udostępnianie pokoi | Pokoje i oferta | Dodaj testowy pokój, wycofaj i ponownie opublikuj. Publiczna lista ma odpowiednio ukryć/pokazać ofertę. Pokój z historią nie może zostać skasowany. |
| Dostępność pokoi | Dostępność | Sprawdź wolny termin, zajęty termin i blokadę. Blokada nie może naruszyć istniejącego pobytu. Dzień wymeldowania dopuszcza następny pobyt. |
| Dostępność pracowników | Grafik i urlopy, Kompetencje, Dostępność, Wizyty | Dodaj godziny pracy i kompetencję, utwórz wizytę. Dostępność pokaże dwa wolne przedziały wokół wizyty. Urlop, brak grafiku i nieaktywny profil nie dają dostępności. |
| Pulpit i spójna nawigacja | Pulpit i menu boczne | Wejdź do każdego modułu, wróć na pulpit, sprawdź liczniki, wykres i ostatnie operacje. Na telefonie tabela ma przewijać się w swoim kontenerze. |
| Historia | Historia | Po zmianie pokoju, pobytu i pracownika znajdź wpis z administratorem, datą, obiektem i stanem przed/po. Sprawdź filtry. |
| Kalendarz | Kalendarz | Przełącz miesiąc, znajdź pobyt bez dnia wymeldowania, blokadę i godzinę wizyty; otwórz powiązane moduły. |
| Dodawanie, edycja, usuwanie pracowników | Pracownicy i Konta | Utwórz konto i profil, zmień stanowisko/opis, dodaj kompetencję i grafik; usuń profil bez historii. Dla profilu z historią użyj dezaktywacji. |
| Niespójność GitHub/hosting | Kontrola instalacji + repozytorium | Wszystkie kontrole bazy OK, zero niezgodnych plików, identyczne wydanie i odcisk jak w START. Zapisz SHA nowego commita i sprawdź live strony po uploadzie. |

## Kolejność prezentacji nauczycielowi

1. Właściwy adres `https://gah.gt.tc/public/admin/index.php`, nie lokalny podgląd i nie tylko repozytorium.
2. Kontrola instalacji: pełny schemat i zgodny manifest.
3. Pulpit → pokój → publikacja → dostępność → rezerwacja → odrzucenie kolizji.
4. Pracownik → kompetencja → grafik → wizyta → zmniejszenie wolnego przedziału.
5. Kalendarz i historia tych samych operacji.
6. Zgodny commit repozytorium i zapisany wynik odbioru hostingu.

Nie obiecujemy oceny 5 ani 6. Paczka uzupełnia wskazany zakres funkcjonalny, ale odbiór zależy od działającego wdrożenia i kryteriów nauczyciela. Zespół trzyosobowy jest zaakceptowany; nie wymaga ponownego zatwierdzania. Nie zakładamy obecnej obrony ustnej.

## Podział 3 osoby × 3 dni

To plan przyszłego wdrożenia i weryfikacji, nie potwierdzenie wykonania przyszłych prac. Każda osoba ma porównywalny moduł, testy, dokumentację i wzajemny przegląd. Nie edytujcie równolegle wspólnych plików bez uzgodnienia.

| Osoba | Dzień 1 — kod i baza | Dzień 2 — testy i przegląd | Dzień 3 — wdrożenie i odbiór |
|---|---|---|---|
| Oliwia | Pokoje i oferta, migracja 005, admin_pokoje.php, pokoje.php, dostepnosc.php; przegląd schematu i relacji etapu 1 | Publikacja, wycofanie, serwis, blokady, daty; przegląd modułu rezerwacji Dawida | Backup plików, upload zasobów/pokoi; live odbiór publicznej oferty, dostępności i telefonu |
| Tobiasz | Konta, pracownicy, kompetencje, grafik, urlopy i wizyty; weryfikacja CRUD oraz N:M z etapów 1–2 | Role, aktywne konta, godziny, kolizje wizyt, usuwanie i dezaktywacja; przegląd pokoi Oliwii | Backup SQL, ustalenie stanu migracji, wykonanie wyłącznie potrzebnych kroków w phpMyAdmin; live odbiór pracowników/wizyt |
| Dawid | Rezerwacje i klient, pulpit, historia, kalendarz, eksport, manifest; przegląd wcześniejszego przepływu rezerwacji | Zmiana terminu, cena, wersje formularzy, historia i CSV; przegląd pracowników Tobiasza | Scalenie repozytorium bez sekretów, upload kompletnego wydania, sprawdzenie manifestu i nowego commita; live odbiór rezerwacji/historii/kalendarza |

Każdego dnia: krótki wspólny przegląd, wynik testów i jawny wpis w protokole. Dzień 3 kończy się wspólnym przejściem całej checklisty, a nie samym commitem.

## Protokół do wypełnienia po wdrożeniu

- Osoby wykonujące i data: ______
- Backup plików oraz eksport bazy (lokalizacja prywatna): ______
- Stan bazy przed aktualizacją i wykonane migracje: ______
- SHA commita z tym wydaniem: ______
- Identyfikator i odcisk z kontroli instalacji na gah.gt.tc: ______
- Liczba niezgodnych plików: ______
- Wynik każdego wiersza checklisty oraz adresy stron: ______
- Pozostałe problemy: ______
