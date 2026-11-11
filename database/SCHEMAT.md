# Schemat aktualnego wydania

17 tabel, 19 kluczy obcych. Wygenerowano z rzeczywiście odtworzonego schematu 003–006, nie ze starego rysunku.

`WizytyUslug` jest bezpośrednią rezerwacją usługi: klient, pracownik i usługa to trzy oddzielne FK. `Pracownicy_Uslugi` realizuje N:M kompetencji. `Rezerwacje` opisuje pobyty dobowe, a `BlokadyPokoi` okresy wyłączone z oferty. Kategorie usług mają osobną tabelę; tekstowa kopia Kategoria została usunięta. Kwota pobytu i cena wizyty są historycznymi cenami transakcji, nie bieżącym cennikiem. LiczbaOsob pozostaje NULL w starych pobytach, gdy tej informacji wcześniej nie zbierano.

Diagram: `ERD_AKTUALNE.svg`.

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

## Stanowiska

| Kolumna | Typ | NULL | Klucz |
|---|---|---|---|
| ID_Stanowiska | int(11) | NO | PRI |
| Nazwa_stanowiska | varchar(50) | NO | — |
| Placa_brutto | decimal(10,2) | NO | — |

## TypyPokoi

| Kolumna | Typ | NULL | Klucz |
|---|---|---|---|
| ID_Typu | int(11) | NO | PRI |
| NazwaTypu | varchar(50) | NO | — |
| Opis | text | YES | — |

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

## Integralność wydania 006

37 CHECK; 19 FK. Powiązania płatności i zamówień z pobytem mają DELETE RESTRICT. Szczegółowy podział reguł SQL/PHP i ograniczenia modelu opisuje `../INTEGRALNOSC.md`.
