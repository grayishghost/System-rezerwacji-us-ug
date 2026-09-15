# Grand Access Hotel

 System zarządzania **Grand Access Hotel** – pięciogwiazdkowym hotelem, którego celem jest stworzenie nowoczesnej aplikacji internetowej wspierającej obsługę rezerwacji, klientów, pracowników, pokoi oraz usług dodatkowych.

 Projekt realizowany jest jako **projekt semestralny** i obejmuje zarówno warstwę prezentacji, logikę aplikacji, jak i relacyjną bazę danych.

# Autorzy projektu

- Oliwia Drygiel
- Tobiasz Mućko
- Dawid Domagała

 ## Opis projektu

 **Grand Access Hotel** to system informatyczny przeznaczony do zarządzania działalnością hotelu. Aplikacja umożliwia obsługę najważniejszych procesów związanych z pobytem gości – od zarządzania pokojami i rezerwacjami, przez obsługę klientów, aż po dodatkowe usługi i płatności.

 System został zaprojektowany z myślą o dwóch głównych grupach użytkowników:

 - **Klientach hotelu** – którzy mogą przeglądać ofertę, dokonywać rezerwacji oraz korzystać z dodatkowych usług.
- **Pracownikach hotelu** – odpowiedzialnych za zarządzanie rezerwacjami, pokojami, klientami, personelem oraz usługami.

 Projekt wykorzystuje relacyjną bazę danych **MySQL**, której struktura została przedstawiona za pomocą diagramu ERD.

 ## Główne funkcjonalności

 System będzie obejmował między innymi:

 - zarządzanie klientami,
- zarządzanie pokojami hotelowymi,
- obsługę typów pokoi,
- tworzenie i zarządzanie rezerwacjami,
- zarządzanie pracownikami,
- prowadzenie grafiku pracowników,
- obsługę usług dodatkowych,
- obsługę zamówień usług przypisanych do rezerwacji,
- rejestrowanie płatności,
- przechowywanie informacji o statusach rezerwacji i płatności,
- obsługę danych użytkowników i ich bezpieczeństwa.

 ## Baza danych

 Integralną częścią projektu jest relacyjna baza danych MySQL. Została ona podzielona na kilka powiązanych ze sobą tabel:

 | Tabela | Opis |
| --- | --- |
| `Klienci` | Dane osób korzystających z usług hotelu |
| `Rezerwacje` | Informacje dotyczące rezerwacji pokoi |
| `Pokoje` | Dane dotyczące dostępnych pokoi |
| `TypyPokoi` | Rodzaje i charakterystyka pokoi |
| `Pracownicy` | Informacje o pracownikach hotelu |
| `Stanowiska` | Stanowiska zajmowane przez pracowników |
| `GrafikPracownikow` | Harmonogram pracy personelu |
| `Uslugi_dodatkowe` | Oferta dodatkowych usług hotelowych |
| `ZamowieniaUslugi` | Usługi zamówione w ramach konkretnej rezerwacji |
| `Pracownicy_Uslugi` | Powiązanie pracowników z obsługiwanymi usługami |
| `Platnosci` | Informacje dotyczące płatności za pobyt i usługi |

Struktura bazy danych została zaprojektowana zgodnie z zasadami relacyjnych baz danych, wykorzystując **klucze główne, klucze obce oraz relacje pomiędzy tabelami**.

 ## Diagram ERD

 Schemat bazy danych projektu przedstawia zależności pomiędzy klientami, rezerwacjami, pokojami, pracownikami, usługami oraz płatnościami.

 Najważniejsze zależności obejmują między innymi:

 - klient może posiadać wiele rezerwacji,
- rezerwacja dotyczy konkretnego pokoju,
- pracownik może być przypisany do wielu rezerwacji,
- pokój posiada określony typ,
- pracownik zajmuje określone stanowisko,
- rezerwacja może obejmować wiele usług dodatkowych,
- usługi mogą być obsługiwane przez wielu pracowników,
- jedna rezerwacja może mieć powiązane płatności.

 ## Technologie

 Projekt zostanie wykonany z wykorzystaniem następujących technologii.

 ### Frontend

 - **HTML5** – tworzenie struktury stron internetowych,
- **CSS3** – stylowanie oraz zapewnienie responsywności interfejsu,
- **JavaScript** – obsługa interakcji i dynamicznych elementów aplikacji.

 ### Backend

 - **PHP** – implementacja logiki aplikacji, obsługa formularzy, komunikacja z bazą danych oraz realizacja procesów biznesowych.

 ### Baza danych

 - **MySQL** – przechowywanie, organizacja oraz zarządzanie danymi systemu.

 ## Cele projektu

 Głównym celem projektu jest stworzenie funkcjonalnego systemu hotelowego, który pozwoli na praktyczne wykorzystanie wiedzy zdobytej podczas zajęć z:

 - tworzenia stron internetowych,
- programowania po stronie klienta i serwera,
- projektowania relacyjnych baz danych,
- języka SQL,
- komunikacji aplikacji z bazą danych,
- projektowania interfejsów użytkownika.

 Projekt ma również na celu odwzorowanie rzeczywistych procesów zachodzących w pięciogwiazdkowym hotelu oraz stworzenie spójnego systemu łączącego warstwę frontendową, backendową i bazodanową.

 ## Status projektu

 **Projekt w trakcie realizacji**

 Planowane jest dalsze rozwijanie funkcjonalności systemu, interfejsu użytkownika oraz warstwy backendowej i bazodanowej.

 ## Projekt semestralny

 Projekt **Grand Access Hotel** powstaje w ramach projektu semestralnego i stanowi praktyczne połączenie technologii **HTML, CSS, JavaScript, PHP oraz MySQL** w jednym systemie informatycznym.

---