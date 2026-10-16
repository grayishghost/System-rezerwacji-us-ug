# Schemat relacji bazy Grand Access Hotel

```mermaid
erDiagram
    Uzytkownicy ||--o| Pracownicy : "konto pracownika"
    Stanowiska ||--o{ Pracownicy : "stanowisko"
    TypyPokoi ||--o{ Pokoje : "typ"
    Uzytkownicy ||--o{ Rezerwacje : "klient"
    Pokoje ||--o{ Rezerwacje : "pokoj"
    Pracownicy |o--o{ Rezerwacje : "obsluga"
    Rezerwacje ||--o{ Platnosci : "platnosci"
    Rezerwacje ||--o{ ZamowieniaUslugi : "dodatki"
    Uslugi_dodatkowe ||--o{ ZamowieniaUslugi : "usluga"
    Pracownicy ||--o{ Pracownicy_Uslugi : "wykonuje"
    Uslugi_dodatkowe ||--o{ Pracownicy_Uslugi : "przypisana"
    Pracownicy ||--o{ GrafikPracownikow : "grafik"
```

`Pracownicy_Uslugi` tworzy relację N:M. `Proby_logowania` przechowuje próby według adresu e-mail i nie ma klucza obcego do `Uzytkownicy`, aby rejestrować także próby dla nieznanych adresów. Definicje kolumn i ograniczeń znajdują się w `database.sql`.
