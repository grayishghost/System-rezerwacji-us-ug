SET NAMES utf8mb4;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS BlokadyPokoi;
DROP TABLE IF EXISTS `WizytyUslug`;
DROP TABLE IF EXISTS `LogAdministratora`;
DROP TABLE IF EXISTS `KategorieUslug`;
DROP TABLE IF EXISTS `Migracje`;
DROP TABLE IF EXISTS `Proby_logowania`;
DROP TABLE IF EXISTS `GrafikPracownikow`;
DROP TABLE IF EXISTS `Pracownicy_Uslugi`;
DROP TABLE IF EXISTS `ZamowieniaUslugi`;
DROP TABLE IF EXISTS `Platnosci`;
DROP TABLE IF EXISTS `Rezerwacje`;
DROP TABLE IF EXISTS `Uslugi_dodatkowe`;
DROP TABLE IF EXISTS `Pokoje`;
DROP TABLE IF EXISTS `TypyPokoi`;
DROP TABLE IF EXISTS `Pracownicy`;
DROP TABLE IF EXISTS `Stanowiska`;
DROP TABLE IF EXISTS `Uzytkownicy`;
DROP TABLE IF EXISTS `Klienci`;
SET FOREIGN_KEY_CHECKS = 1;


CREATE TABLE `Uzytkownicy` (
  `ID_Uzytkownika` INT AUTO_INCREMENT PRIMARY KEY,
  `Imie` VARCHAR(50) NOT NULL,
  `Nazwisko` VARCHAR(50) NOT NULL,
  `Email` VARCHAR(100) NOT NULL UNIQUE,
  `Telefon` VARCHAR(20),
  `Haslo` VARCHAR(255) NOT NULL,
  `Rola` ENUM('klient', 'pracownik', 'admin') NOT NULL DEFAULT 'klient',
  `Aktywny` TINYINT(1) NOT NULL DEFAULT 1,
  `DataUtworzenia` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO `Uzytkownicy` (`ID_Uzytkownika`, `Imie`, `Nazwisko`, `Email`, `Telefon`, `Haslo`, `Rola`, `Aktywny`) VALUES
(1, 'Jan',     'Administrator', 'admin@hotel.pl',      '111222333', '$2y$10$qO.tSnS2CRGWJdkgYCf5W.0cwhQmvRZxxXxz0fdm5gAjYKgYjukIO', 'admin',     1),
(2, 'John',    'Doe',           'john.doe@email.com',  '212132435', '$2y$10$HDzGqwQW1I2XSpyByyl8k.iG33AyFf0k1cGmfw9RDVM8SEsiBUG9a', 'pracownik', 1),
(3, 'William', 'Dafie',         'will.dafie@email.com','932748578', '$2y$10$HDzGqwQW1I2XSpyByyl8k.iG33AyFf0k1cGmfw9RDVM8SEsiBUG9a', 'pracownik', 1),
(4, 'Anna',    'Kowalska',      'anna.kowal@dk.pl',    '123456789', '$2y$10$whLGCAHW.ja4vx.4xn7y1uQHtysNy0AgbZ6thJYfD3CDziLk8dBGC', 'klient',    1),
(5, 'Jacek',   'Sowa',          'jacek.kowal@dk.pl',   '987654321', '$2y$10$whLGCAHW.ja4vx.4xn7y1uQHtysNy0AgbZ6thJYfD3CDziLk8dBGC', 'klient',    1),
(6, 'Adam',    'Nowak',         'adam.nowak@dk.pl',    '456321789', '$2y$10$whLGCAHW.ja4vx.4xn7y1uQHtysNy0AgbZ6thJYfD3CDziLk8dBGC', 'klient',    1);


CREATE TABLE `Stanowiska` (
  `ID_Stanowiska` INT AUTO_INCREMENT PRIMARY KEY,
  `Nazwa_stanowiska` VARCHAR(50) NOT NULL,
  `Placa_brutto` DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `Stanowiska` (`ID_Stanowiska`, `Nazwa_stanowiska`, `Placa_brutto`) VALUES
(1, 'Recepcjonista', 6000.00),
(2, 'Menadżer usług', 5000.00),
(3, 'Pokojówka', 4666.00);


CREATE TABLE `Pracownicy` (
  `ID_Pracownika` INT AUTO_INCREMENT PRIMARY KEY,
  `ID_Uzytkownika` INT NOT NULL UNIQUE,
  `ID_Stanowiska` INT,
  FOREIGN KEY (`ID_Uzytkownika`) REFERENCES `Uzytkownicy`(`ID_Uzytkownika`) ON DELETE CASCADE,
  FOREIGN KEY (`ID_Stanowiska`) REFERENCES `Stanowiska`(`ID_Stanowiska`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `Pracownicy` (`ID_Pracownika`, `ID_Uzytkownika`, `ID_Stanowiska`) VALUES
(1, 2, 1),
(2, 3, 2);


CREATE TABLE `TypyPokoi` (
  `ID_Typu` INT AUTO_INCREMENT PRIMARY KEY,
  `NazwaTypu` VARCHAR(50) NOT NULL,
  `Opis` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `TypyPokoi` (`ID_Typu`, `NazwaTypu`, `Opis`) VALUES
(1, 'Standard 1 osoba', 'Standardowy typ pokoju dla 1 osoby.'),
(2, 'Apartament deluxe', 'Luksusowe wnętrze z hydromasażem.');

CREATE TABLE `Pokoje` (
  `ID_Pokoju` INT AUTO_INCREMENT PRIMARY KEY,
  `NrPokoju` INT NOT NULL UNIQUE,
  `ID_Typu` INT NOT NULL,
  `CenaDobowa` DECIMAL(10,2) NOT NULL,
  `Status` ENUM('Wolny', 'Zajęty', 'Serwis') NOT NULL DEFAULT 'Wolny',
  `IloscOsob` INT NOT NULL DEFAULT 1,
  `Pietro` INT NOT NULL DEFAULT 1,
  `Zdjecie_Pokoju` VARCHAR(255),
  FOREIGN KEY (`ID_Typu`) REFERENCES `TypyPokoi`(`ID_Typu`),
  INDEX `idx_pokoje_status` (`Status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `Pokoje` (`ID_Pokoju`, `NrPokoju`, `ID_Typu`, `CenaDobowa`, `Status`, `IloscOsob`, `Pietro`, `Zdjecie_Pokoju`) VALUES
(1, 101, 1, 200.00, 'Zajęty', 1, 1, NULL),
(2, 102, 2, 500.00, 'Wolny',  2, 1, NULL);


CREATE TABLE `Uslugi_dodatkowe` (
  `ID_Uslugi` INT AUTO_INCREMENT PRIMARY KEY,
  `Nazwa_Uslugi` VARCHAR(100) NOT NULL,
  `Opis` TEXT,
  `Cena` DECIMAL(10,2) NOT NULL,
  `Kategoria` VARCHAR(50),
  `Czydostepna` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `Uslugi_dodatkowe` (`ID_Uslugi`, `Nazwa_Uslugi`, `Opis`, `Cena`, `Kategoria`, `Czydostepna`) VALUES
(1, 'Drinki do pokoju', 'Drinki dostarczane do pokoju', 200.00, 'Napoje', 1),
(2, 'Śniadanie do łóżka', 'Śniadanie dostarczane do łóżka', 500.00, 'Jedzenie', 1);


CREATE TABLE `Pracownicy_Uslugi` (
  `ID_Pracownika` INT NOT NULL,
  `ID_Uslugi` INT NOT NULL,
  PRIMARY KEY (`ID_Pracownika`, `ID_Uslugi`),
  FOREIGN KEY (`ID_Pracownika`) REFERENCES `Pracownicy`(`ID_Pracownika`) ON DELETE CASCADE,
  FOREIGN KEY (`ID_Uslugi`) REFERENCES `Uslugi_dodatkowe`(`ID_Uslugi`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `Pracownicy_Uslugi` (`ID_Pracownika`, `ID_Uslugi`) VALUES
(1, 1), (1, 2), (2, 2);


CREATE TABLE `Rezerwacje` (
  `ID_Rezerwacji` INT AUTO_INCREMENT PRIMARY KEY,
  `ID_Pokoju` INT NOT NULL,
  `ID_Klienta` INT NOT NULL,
  `ID_Pracownika` INT,
  `DataOd` DATETIME NOT NULL,
  `DataDo` DATETIME NOT NULL,
  `Kwota` DECIMAL(10,2) NOT NULL,
  `Status` ENUM('Oczekująca', 'Potwierdzona', 'Ukończone', 'Anulowana') NOT NULL DEFAULT 'Oczekująca',
  `Zrodlo_rezerwacji` VARCHAR(50),
  `Uwagi` TEXT,
  FOREIGN KEY (`ID_Pokoju`) REFERENCES `Pokoje`(`ID_Pokoju`),
  FOREIGN KEY (`ID_Klienta`) REFERENCES `Uzytkownicy`(`ID_Uzytkownika`),
  FOREIGN KEY (`ID_Pracownika`) REFERENCES `Pracownicy`(`ID_Pracownika`),
  CONSTRAINT `chk_rezerwacje_daty` CHECK (`DataDo` > `DataOd`),
  INDEX `idx_rezerwacje_daty` (`DataOd`, `DataDo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `Rezerwacje` (`ID_Rezerwacji`, `ID_Pokoju`, `ID_Klienta`, `ID_Pracownika`, `DataOd`, `DataDo`, `Kwota`, `Status`, `Zrodlo_rezerwacji`, `Uwagi`) VALUES
(1, 1, 4, 1, '2026-01-27 00:00:00', '2026-01-30 00:00:00', 800.00, 'Ukończone', 'Bezpośrednio', 'brak');


CREATE TABLE `Platnosci` (
  `ID_Platnosci` INT AUTO_INCREMENT PRIMARY KEY,
  `ID_Rezerwacji` INT NOT NULL,
  `DataPlatnosci` DATETIME NOT NULL,
  `Kwota` DECIMAL(10,2) NOT NULL,
  `Metoda` VARCHAR(50),
  `Status` VARCHAR(50),
  `Typ_platnosci` VARCHAR(50),
  FOREIGN KEY (`ID_Rezerwacji`) REFERENCES `Rezerwacje`(`ID_Rezerwacji`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `Platnosci` (`ID_Platnosci`, `ID_Rezerwacji`, `DataPlatnosci`, `Kwota`, `Metoda`, `Status`, `Typ_platnosci`) VALUES
(1, 1, '2026-01-27 12:00:00', 800.00, 'Karta', 'Opłacona', 'Pobyt');

CREATE TABLE `ZamowieniaUslugi` (
  `ID_Zamowienia` INT AUTO_INCREMENT PRIMARY KEY,
  `ID_Rezerwacji` INT NOT NULL,
  `ID_Uslugi` INT NOT NULL,
  `DataZamowienia` DATETIME NOT NULL,
  `Ilosc` INT NOT NULL DEFAULT 1,
  `Cena_jednostkowa` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`ID_Rezerwacji`) REFERENCES `Rezerwacje`(`ID_Rezerwacji`) ON DELETE CASCADE,
  FOREIGN KEY (`ID_Uslugi`) REFERENCES `Uslugi_dodatkowe`(`ID_Uslugi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `GrafikPracownikow` (
  `ID_Wpisu` INT AUTO_INCREMENT PRIMARY KEY,
  `ID_Pracownika` INT NOT NULL,
  `Data` DATE NOT NULL,
  `TypDnia` ENUM('Praca', 'Urlop', 'Zwolnienie', 'Wolne') NOT NULL DEFAULT 'Praca',
  `GodzinaOd` TIME,
  `GodzinaDo` TIME,
  FOREIGN KEY (`ID_Pracownika`) REFERENCES `Pracownicy`(`ID_Pracownika`) ON DELETE CASCADE,
  UNIQUE KEY `uniq_grafik_dzien` (`ID_Pracownika`, `Data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `GrafikPracownikow` (`ID_Pracownika`, `Data`, `TypDnia`, `GodzinaOd`, `GodzinaDo`) VALUES
(1, '2026-11-02', 'Praca', '08:00:00', '16:00:00'),
(1, '2026-11-03', 'Praca', '08:00:00', '16:00:00'),
(2, '2026-11-02', 'Urlop', NULL, NULL);


CREATE TABLE `Proby_logowania` (
  `ID_Proby` INT AUTO_INCREMENT PRIMARY KEY,
  `Email` VARCHAR(100) NOT NULL,
  `IP` VARCHAR(45) NOT NULL,
  `Czas` DATETIME NOT NULL,
  INDEX `idx_proby_email_czas` (`Email`, `Czas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE KategorieUslug (
 ID_Kategorii INT AUTO_INCREMENT PRIMARY KEY,
 Nazwa VARCHAR(50) NOT NULL UNIQUE,
 Opis TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO KategorieUslug (Nazwa)
 SELECT DISTINCT COALESCE(NULLIF(TRIM(Kategoria), ''), 'Pozostałe') FROM Uslugi_dodatkowe;
ALTER TABLE Uslugi_dodatkowe ADD ID_Kategorii INT NULL, ADD CzasTrwania INT NOT NULL DEFAULT 30;
UPDATE Uslugi_dodatkowe u JOIN KategorieUslug k ON k.Nazwa = COALESCE(NULLIF(TRIM(u.Kategoria), ''), 'Pozostałe') SET u.ID_Kategorii = k.ID_Kategorii;
ALTER TABLE Uslugi_dodatkowe MODIFY ID_Kategorii INT NOT NULL,
 ADD CONSTRAINT fk_usluga_kategoria FOREIGN KEY (ID_Kategorii) REFERENCES KategorieUslug(ID_Kategorii),
 ADD CONSTRAINT chk_usluga_cena CHECK (Cena >= 0),
 ADD CONSTRAINT chk_usluga_czas CHECK (CzasTrwania BETWEEN 1 AND 1440);
ALTER TABLE Pracownicy ADD Aktywny TINYINT(1) NOT NULL DEFAULT 1, ADD Opis TEXT NULL;
CREATE TABLE LogAdministratora (
 ID_Logu BIGINT AUTO_INCREMENT PRIMARY KEY,
 ID_Admina INT NULL,
 Akcja VARCHAR(80) NOT NULL,
 Obiekt VARCHAR(50) NOT NULL,
 ID_Obiektu INT NULL,
 Przed TEXT NULL,
 Po TEXT NULL,
 Czas DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (ID_Admina) REFERENCES Uzytkownicy(ID_Uzytkownika) ON DELETE SET NULL,
 INDEX idx_log_czas(Czas), INDEX idx_log_obiekt(Obiekt, ID_Obiektu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE Migracje (Nazwa VARCHAR(100) PRIMARY KEY, Czas DATETIME DEFAULT CURRENT_TIMESTAMP);
INSERT INTO Migracje(Nazwa) VALUES ('003_panel');

CREATE TABLE WizytyUslug (
 ID_Wizyty INT AUTO_INCREMENT PRIMARY KEY,
 ID_Klienta INT NOT NULL,
 ID_Pracownika INT NOT NULL,
 ID_Uslugi INT NOT NULL,
 DataOd DATETIME NOT NULL,
 DataDo DATETIME NOT NULL,
 Cena DECIMAL(10,2) NOT NULL,
 Status ENUM('Oczekująca','Potwierdzona','Ukończone','Anulowana') NOT NULL DEFAULT 'Oczekująca',
 Uwagi VARCHAR(1000) NULL,
 Wersja INT NOT NULL DEFAULT 1,
 DataUtworzenia DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (ID_Klienta) REFERENCES Uzytkownicy(ID_Uzytkownika),
 FOREIGN KEY (ID_Pracownika) REFERENCES Pracownicy(ID_Pracownika),
 FOREIGN KEY (ID_Uslugi) REFERENCES Uslugi_dodatkowe(ID_Uslugi),
 CONSTRAINT chk_wizyta_przedzial CHECK(DataDo > DataOd),
 CONSTRAINT chk_wizyta_cena CHECK(Cena >= 0),
 INDEX idx_wizyta_pracownik_daty (ID_Pracownika,DataOd,DataDo),
 INDEX idx_wizyta_status_data (Status,DataOd),
 INDEX idx_wizyta_klient_data (ID_Klienta,DataOd)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE Pokoje ADD CONSTRAINT chk_pokoj_cena CHECK(CenaDobowa >= 0), ADD CONSTRAINT chk_pokoj_osoby CHECK(IloscOsob > 0);
ALTER TABLE Rezerwacje ADD CONSTRAINT chk_pobyt_kwota CHECK(Kwota >= 0);
ALTER TABLE Platnosci ADD CONSTRAINT chk_platnosc_kwota CHECK(Kwota >= 0);
ALTER TABLE ZamowieniaUslugi ADD CONSTRAINT chk_zamowienie_ilosc CHECK(Ilosc > 0), ADD CONSTRAINT chk_zamowienie_cena CHECK(Cena_jednostkowa >= 0);
ALTER TABLE GrafikPracownikow ADD CONSTRAINT chk_grafik_godziny CHECK((TypDnia='Praca' AND GodzinaOd IS NOT NULL AND GodzinaDo IS NOT NULL AND GodzinaDo>GodzinaOd) OR (TypDnia<>'Praca' AND GodzinaOd IS NULL AND GodzinaDo IS NULL));
ALTER TABLE Uslugi_dodatkowe DROP COLUMN Kategoria;

INSERT INTO Migracje(Nazwa) VALUES ('004_uzupelnienia');


INSERT INTO WizytyUslug(ID_Klienta,ID_Pracownika,ID_Uslugi,DataOd,DataDo,Cena,Status,Uwagi) VALUES
(4,1,1,'2026-11-03 10:00:00','2026-11-03 10:30:00',200.00,'Oczekująca','Fikcyjne dane startowe'),
(5,1,2,'2026-11-03 11:00:00','2026-11-03 11:30:00',500.00,'Potwierdzona','Fikcyjne dane startowe');

ALTER TABLE Pokoje ADD Aktywny TINYINT(1) NOT NULL DEFAULT 1, ADD Wersja INT NOT NULL DEFAULT 1;
ALTER TABLE Rezerwacje ADD LiczbaOsob INT NULL, ADD Wersja INT NOT NULL DEFAULT 1, ADD CONSTRAINT chk_pobyt_osoby CHECK(LiczbaOsob IS NULL OR LiczbaOsob > 0);
CREATE TABLE BlokadyPokoi (
 ID_Blokady INT AUTO_INCREMENT PRIMARY KEY,
 ID_Pokoju INT NOT NULL,
 DataOd DATE NOT NULL,
 DataDo DATE NOT NULL,
 Powod VARCHAR(250) NOT NULL,
 ID_Admina INT NULL,
 DataUtworzenia DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (ID_Pokoju) REFERENCES Pokoje(ID_Pokoju) ON DELETE CASCADE,
 FOREIGN KEY (ID_Admina) REFERENCES Uzytkownicy(ID_Uzytkownika) ON DELETE SET NULL,
 CONSTRAINT chk_blokada_przedzial CHECK(DataDo > DataOd),
 INDEX idx_blokada_pokoj_daty(ID_Pokoju,DataOd,DataDo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO Migracje(Nazwa) VALUES ('005_pokoje');
ALTER TABLE Uzytkownicy ADD CONSTRAINT chk_i006_konto_flaga CHECK (Aktywny IN (0,1));
ALTER TABLE Uzytkownicy ADD CONSTRAINT chk_i006_konto_imie CHECK (CHAR_LENGTH(TRIM(Imie))>0);
ALTER TABLE Uzytkownicy ADD CONSTRAINT chk_i006_konto_nazwisko CHECK (CHAR_LENGTH(TRIM(Nazwisko))>0);
ALTER TABLE Uzytkownicy ADD CONSTRAINT chk_i006_konto_email CHECK (CHAR_LENGTH(TRIM(Email))>0);
ALTER TABLE Uzytkownicy ADD CONSTRAINT chk_i006_konto_rola CHECK (Rola IN ('klient','pracownik','admin'));
ALTER TABLE Pracownicy ADD CONSTRAINT chk_i006_pracownik_flaga CHECK (Aktywny IN (0,1));
ALTER TABLE Stanowiska ADD CONSTRAINT chk_i006_placa CHECK (Placa_brutto>=0);
ALTER TABLE Stanowiska ADD CONSTRAINT chk_i006_stanowisko_nazwa CHECK (CHAR_LENGTH(TRIM(Nazwa_stanowiska))>0);
ALTER TABLE TypyPokoi ADD CONSTRAINT chk_i006_typ_nazwa CHECK (CHAR_LENGTH(TRIM(NazwaTypu))>0);
ALTER TABLE Pokoje ADD CONSTRAINT chk_i006_pokoj_numer CHECK (NrPokoju>0);
ALTER TABLE Pokoje ADD CONSTRAINT chk_i006_pokoj_flaga CHECK (Aktywny IN (0,1));
ALTER TABLE Pokoje ADD CONSTRAINT chk_i006_pokoj_wersja CHECK (Wersja>=1);
ALTER TABLE Pokoje ADD CONSTRAINT chk_i006_pokoj_status CHECK (Status IN ('Wolny','Zajęty','Serwis'));
ALTER TABLE Rezerwacje ADD CONSTRAINT chk_i006_pobyt_wersja CHECK (Wersja>=1);
ALTER TABLE Rezerwacje ADD CONSTRAINT chk_i006_pobyt_status CHECK (Status IN ('Oczekująca','Potwierdzona','Ukończone','Anulowana'));
ALTER TABLE WizytyUslug ADD CONSTRAINT chk_i006_wizyta_wersja CHECK (Wersja>=1);
ALTER TABLE WizytyUslug ADD CONSTRAINT chk_i006_wizyta_status CHECK (Status IN ('Oczekująca','Potwierdzona','Ukończone','Anulowana'));
ALTER TABLE Uslugi_dodatkowe ADD CONSTRAINT chk_i006_usluga_flaga CHECK (Czydostepna IN (0,1));
ALTER TABLE Uslugi_dodatkowe ADD CONSTRAINT chk_i006_usluga_nazwa CHECK (CHAR_LENGTH(TRIM(Nazwa_Uslugi))>0);
ALTER TABLE KategorieUslug ADD CONSTRAINT chk_i006_kategoria_nazwa CHECK (CHAR_LENGTH(TRIM(Nazwa))>0);
ALTER TABLE BlokadyPokoi ADD CONSTRAINT chk_i006_blokada_powod CHECK (CHAR_LENGTH(TRIM(Powod))>0);
ALTER TABLE GrafikPracownikow ADD CONSTRAINT chk_i006_grafik_typ CHECK (TypDnia IN ('Praca','Urlop','Zwolnienie','Wolne'));
ALTER TABLE GrafikPracownikow ADD CONSTRAINT chk_i006_grafik_doba CHECK (TypDnia<>'Praca' OR (GodzinaOd>='00:00:00' AND GodzinaDo<'24:00:00'));
CREATE INDEX idx_pobyt_pokoj_status_daty ON Rezerwacje(ID_Pokoju,Status,DataOd,DataDo);
SET @gah_fk=(SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Platnosci' AND COLUMN_NAME='ID_Rezerwacji' AND REFERENCED_TABLE_NAME='Rezerwacje');
SET @gah_ddl=CONCAT('ALTER TABLE Platnosci DROP FOREIGN KEY `',REPLACE(@gah_fk,'`','``'),'`, ADD CONSTRAINT fk_i006_platnosc_pobyt FOREIGN KEY (ID_Rezerwacji) REFERENCES Rezerwacje(ID_Rezerwacji) ON DELETE RESTRICT ON UPDATE RESTRICT');
PREPARE gah_stmt FROM @gah_ddl;
EXECUTE gah_stmt;
DEALLOCATE PREPARE gah_stmt;
SET @gah_fk=(SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='ZamowieniaUslugi' AND COLUMN_NAME='ID_Rezerwacji' AND REFERENCED_TABLE_NAME='Rezerwacje');
SET @gah_ddl=CONCAT('ALTER TABLE ZamowieniaUslugi DROP FOREIGN KEY `',REPLACE(@gah_fk,'`','``'),'`, ADD CONSTRAINT fk_i006_zamowienie_pobyt FOREIGN KEY (ID_Rezerwacji) REFERENCES Rezerwacje(ID_Rezerwacji) ON DELETE RESTRICT ON UPDATE RESTRICT');
PREPARE gah_stmt FROM @gah_ddl;
EXECUTE gah_stmt;
DEALLOCATE PREPARE gah_stmt;

INSERT INTO Migracje(Nazwa) VALUES ('006_integralnosc');