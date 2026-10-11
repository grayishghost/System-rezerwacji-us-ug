CREATE DATABASE IF NOT EXISTS `grand_access_hotel`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `grand_access_hotel`;

SET FOREIGN_KEY_CHECKS = 0;
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