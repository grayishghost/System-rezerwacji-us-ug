# Schemat wydania 007

17 tabel, 19 kluczy obcych i 42 CHECK. Dokument odzwierciedla rzeczywiście odtworzony schemat 003–007.

WizytyUslug wiąże bezpośrednio klienta, pracownika i usługę. Pracownicy_Uslugi to kompetencje N:M. Archiwum w Rezerwacje jest flagą widoczności administracyjnej, nie statusem zajętości. Wersja kontroluje nieaktualne edycje; SesjaWersja konta unieważnia sesje po zmianie hasła. Starsze pobyty zachowują nieznaną LiczbaOsob=NULL. Dwie unikalne nazwy słowników dodaje 007. Szczegóły reguł SQL i PHP opisuje ../INTEGRALNOSC.md.

Diagram: ERD_AKTUALNE.svg.

## BlokadyPokoi

| Kolumna | Typ | NULL | Klucz |
|---|---|---|---|
| ID_Blokady | int(11) | NO | PRI |
| ID_Pokoju | int(11) | NO | MUL |
| DataOd | date | NO | — |
| DataDo | date | NO | — |
| Powod | varchar(250) | NO | — |
| ID_Admina | int(11) | YES | MUL |
| DataUtworzenia | datetime | NO | — |

## GrafikPracownikow

| Kolumna | Typ | NULL | Klucz |
|---|---|---|---|
| ID_Wpisu | int(11) | NO | PRI |
| ID_Pracownika | int(11) | NO | MUL |
| Data | date | NO | — |
| TypDnia | enum('Praca','Urlop','Zwolnienie','Wolne') | NO | — |
| GodzinaOd | time | YES | — |
| GodzinaDo | time | YES | — |

## KategorieUslug

| Kolumna | Typ | NULL | Klucz |
|---|---|---|---|
| ID_Kategorii | int(11) | NO | PRI |
| Nazwa | varchar(50) | NO | UNI |
| Opis | text | YES | — |

## LogAdministratora

| Kolumna | Typ | NULL | Klucz |
|---|---|---|---|
| ID_Logu | bigint(20) | NO | PRI |
| ID_Admina | int(11) | YES | MUL |
| Akcja | varchar(80) | NO | — |
| Obiekt | varchar(50) | NO | MUL |
| ID_Obiektu | int(11) | YES | — |
| Przed | text | YES | — |
| Po | text | YES | — |
| Czas | datetime | NO | MUL |

## Migracje

| Kolumna | Typ | NULL | Klucz |
|---|---|---|---|
| Nazwa | varchar(100) | NO | PRI |
| Czas | datetime | YES | — |

## Platnosci

| Kolumna | Typ | NULL | Klucz |
|---|---|---|---|
| ID_Platnosci | int(11) | NO | PRI |
| ID_Rezerwacji | int(11) | NO | MUL |
| DataPlatnosci | datetime | NO | — |
| Kwota | decimal(10,2) | NO | — |
| Metoda | varchar(50) | YES | — |
| Status | varchar(50) | YES | — |
| Typ_platnosci | varchar(50) | YES | — |

## Pokoje

| Kolumna | Typ | NULL | Klucz |
|---|---|---|---|
| ID_Pokoju | int(11) | NO | PRI |
| NrPokoju | int(11) | NO | UNI |
| ID_Typu | int(11) | NO | MUL |
| CenaDobowa | decimal(10,2) | NO | — |
| Status | enum('Wolny','Zajęty','Serwis') | NO | MUL |
| IloscOsob | int(11) | NO | — |
| Pietro | int(11) | NO | — |
| Zdjecie_Pokoju | varchar(255) | YES | — |
| Aktywny | tinyint(1) | NO | — |
| Wersja | int(11) | NO | — |

## Pracownicy

| Kolumna | Typ | NULL | Klucz |
|---|---|---|---|
| ID_Pracownika | int(11) | NO | PRI |
| ID_Uzytkownika | int(11) | NO | UNI |
| ID_Stanowiska | int(11) | YES | MUL |
| Aktywny | tinyint(1) | NO | — |
| Opis | text | YES | — |

## Pracownicy_Uslugi

| Kolumna | Typ | NULL | Klucz |
|---|---|---|---|
| ID_Pracownika | int(11) | NO | PRI |
| ID_Uslugi | int(11) | NO | PRI |

## Proby_logowania

| Kolumna | Typ | NULL | Klucz |
|---|---|---|---|
| ID_Proby | int(11) | NO | PRI |
| Email | varchar(100) | NO | MUL |
| IP | varchar(45) | NO | — |
| Czas | datetime | NO | — |

## Rezerwacje

| Kolumna | Typ | NULL | Klucz |
|---|---|---|---|
| ID_Rezerwacji | int(11) | NO | PRI |
| ID_Pokoju | int(11) | NO | MUL |
| ID_Klienta | int(11) | NO | MUL |
| ID_Pracownika | int(11) | YES | MUL |
| DataOd | datetime | NO | MUL |
| DataDo | datetime | NO | — |
| Kwota | decimal(10,2) | NO | — |
| Status | enum('Oczekująca','Potwierdzona','Ukończone','Anulowana') | NO | — |
| Zrodlo_rezerwacji | varchar(50) | YES | — |
| Uwagi | text | YES | — |
| LiczbaOsob | int(11) | YES | — |
| Wersja | int(11) | NO | — |
| Archiwum | tinyint(1) | NO | — |

## Stanowiska

| Kolumna | Typ | NULL | Klucz |
|---|---|---|---|
| ID_Stanowiska | int(11) | NO | PRI |
| Nazwa_stanowiska | varchar(50) | NO | UNI |
| Placa_brutto | decimal(10,2) | NO | — |
| Wersja | int(11) | NO | — |

## TypyPokoi

| Kolumna | Typ | NULL | Klucz |
|---|---|---|---|
| ID_Typu | int(11) | NO | PRI |
| NazwaTypu | varchar(50) | NO | UNI |
| Opis | text | YES | — |
| Wersja | int(11) | NO | — |

## Uslugi_dodatkowe

| Kolumna | Typ | NULL | Klucz |
|---|---|---|---|
| ID_Uslugi | int(11) | NO | PRI |
| Nazwa_Uslugi | varchar(100) | NO | — |
| Opis | text | YES | — |
| Cena | decimal(10,2) | NO | — |
| Czydostepna | tinyint(1) | NO | — |
| ID_Kategorii | int(11) | NO | MUL |
| CzasTrwania | int(11) | NO | — |

## Uzytkownicy

| Kolumna | Typ | NULL | Klucz |
|---|---|---|---|
| ID_Uzytkownika | int(11) | NO | PRI |
| Imie | varchar(50) | NO | — |
| Nazwisko | varchar(50) | NO | — |
| Email | varchar(100) | NO | UNI |
| Telefon | varchar(20) | YES | — |
| Haslo | varchar(255) | NO | — |
| Rola | enum('klient','pracownik','admin') | NO | — |
| Aktywny | tinyint(1) | NO | — |
| DataUtworzenia | datetime | NO | — |
| Wersja | int(11) | NO | — |
| SesjaWersja | int(11) | NO | — |

## WizytyUslug

| Kolumna | Typ | NULL | Klucz |
|---|---|---|---|
| ID_Wizyty | int(11) | NO | PRI |
| ID_Klienta | int(11) | NO | MUL |
| ID_Pracownika | int(11) | NO | MUL |
| ID_Uslugi | int(11) | NO | MUL |
| DataOd | datetime | NO | — |
| DataDo | datetime | NO | — |
| Cena | decimal(10,2) | NO | — |
| Status | enum('Oczekująca','Potwierdzona','Ukończone','Anulowana') | NO | MUL |
| Uwagi | varchar(1000) | YES | — |
| Wersja | int(11) | NO | — |
| DataUtworzenia | datetime | NO | — |

## ZamowieniaUslugi

| Kolumna | Typ | NULL | Klucz |
|---|---|---|---|
| ID_Zamowienia | int(11) | NO | PRI |
| ID_Rezerwacji | int(11) | NO | MUL |
| ID_Uslugi | int(11) | NO | MUL |
| DataZamowienia | datetime | NO | — |
| Ilosc | int(11) | NO | — |
| Cena_jednostkowa | decimal(10,2) | NO | — |

## Relacje

| Tabela i kolumna | Wskazuje na |
|---|---|
| BlokadyPokoi.ID_Admina | Uzytkownicy.ID_Uzytkownika |
| BlokadyPokoi.ID_Pokoju | Pokoje.ID_Pokoju |
| GrafikPracownikow.ID_Pracownika | Pracownicy.ID_Pracownika |
| LogAdministratora.ID_Admina | Uzytkownicy.ID_Uzytkownika |
| Platnosci.ID_Rezerwacji | Rezerwacje.ID_Rezerwacji |
| Pokoje.ID_Typu | TypyPokoi.ID_Typu |
| Pracownicy.ID_Stanowiska | Stanowiska.ID_Stanowiska |
| Pracownicy.ID_Uzytkownika | Uzytkownicy.ID_Uzytkownika |
| Pracownicy_Uslugi.ID_Pracownika | Pracownicy.ID_Pracownika |
| Pracownicy_Uslugi.ID_Uslugi | Uslugi_dodatkowe.ID_Uslugi |
| Rezerwacje.ID_Klienta | Uzytkownicy.ID_Uzytkownika |
| Rezerwacje.ID_Pokoju | Pokoje.ID_Pokoju |
| Rezerwacje.ID_Pracownika | Pracownicy.ID_Pracownika |
| Uslugi_dodatkowe.ID_Kategorii | KategorieUslug.ID_Kategorii |
| WizytyUslug.ID_Klienta | Uzytkownicy.ID_Uzytkownika |
| WizytyUslug.ID_Pracownika | Pracownicy.ID_Pracownika |
| WizytyUslug.ID_Uslugi | Uslugi_dodatkowe.ID_Uslugi |
| ZamowieniaUslugi.ID_Rezerwacji | Rezerwacje.ID_Rezerwacji |
| ZamowieniaUslugi.ID_Uslugi | Uslugi_dodatkowe.ID_Uslugi |

## Ograniczenia CHECK

| Tabela | Nazwa |
|---|---|
| BlokadyPokoi | chk_blokada_przedzial |
| BlokadyPokoi | chk_i006_blokada_powod |
| GrafikPracownikow | chk_grafik_godziny |
| GrafikPracownikow | chk_i006_grafik_doba |
| GrafikPracownikow | chk_i006_grafik_typ |
| KategorieUslug | chk_i006_kategoria_nazwa |
| Platnosci | chk_platnosc_kwota |
| Pokoje | chk_i006_pokoj_flaga |
| Pokoje | chk_i006_pokoj_numer |
| Pokoje | chk_i006_pokoj_status |
| Pokoje | chk_i006_pokoj_wersja |
| Pokoje | chk_pokoj_cena |
| Pokoje | chk_pokoj_osoby |
| Pracownicy | chk_i006_pracownik_flaga |
| Rezerwacje | chk_i006_pobyt_status |
| Rezerwacje | chk_i006_pobyt_wersja |
| Rezerwacje | chk_i007_archiwum |
| Rezerwacje | chk_pobyt_kwota |
| Rezerwacje | chk_pobyt_osoby |
| Rezerwacje | chk_rezerwacje_daty |
| Stanowiska | chk_i006_placa |
| Stanowiska | chk_i006_stanowisko_nazwa |
| Stanowiska | chk_i007_stanowisko_wersja |
| TypyPokoi | chk_i006_typ_nazwa |
| TypyPokoi | chk_i007_typ_wersja |
| Uslugi_dodatkowe | chk_i006_usluga_flaga |
| Uslugi_dodatkowe | chk_i006_usluga_nazwa |
| Uslugi_dodatkowe | chk_usluga_cena |
| Uslugi_dodatkowe | chk_usluga_czas |
| Uzytkownicy | chk_i006_konto_email |
| Uzytkownicy | chk_i006_konto_flaga |
| Uzytkownicy | chk_i006_konto_imie |
| Uzytkownicy | chk_i006_konto_nazwisko |
| Uzytkownicy | chk_i006_konto_rola |
| Uzytkownicy | chk_i007_konto_wersja |
| Uzytkownicy | chk_i007_sesja_wersja |
| WizytyUslug | chk_i006_wizyta_status |
| WizytyUslug | chk_i006_wizyta_wersja |
| WizytyUslug | chk_wizyta_cena |
| WizytyUslug | chk_wizyta_przedzial |
| ZamowieniaUslugi | chk_zamowienie_cena |
| ZamowieniaUslugi | chk_zamowienie_ilosc |
