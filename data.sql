SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS `GrafikPracownikow`;
DROP TABLE IF EXISTS `Pracownicy_Uslugi`;
DROP TABLE IF EXISTS `Transakcje`;
DROP TABLE IF EXISTS `ZamowieniaUslugi`;
DROP TABLE IF EXISTS `Platnosci`;
DROP TABLE IF EXISTS `Sprzatanie`;
DROP TABLE IF EXISTS `PrzegladWyposarzenia`;
DROP TABLE IF EXISTS `HistoriaPokoi`;
DROP TABLE IF EXISTS `Rezerwacje`;
DROP TABLE IF EXISTS `Uslugi_dodatkowe`;
DROP TABLE IF EXISTS `Pokoje`;
DROP TABLE IF EXISTS `TypyPokoi`;
DROP TABLE IF EXISTS `Pracownicy`;
DROP TABLE IF EXISTS `Stanowiska`;
DROP TABLE IF EXISTS `Klienci`;
SET FOREIGN_KEY_CHECKS=1;

-- -----------------------------------------------------
-- Table `Klienci`
-- -----------------------------------------------------
CREATE TABLE `Klienci` (
  `ID_Klienta` INT AUTO_INCREMENT PRIMARY KEY,
  `Imie` VARCHAR(50) NOT NULL,
  `Nazwisko` VARCHAR(50) NOT NULL,
  `Telefon` VARCHAR(15),
  `Email` VARCHAR(100) NOT NULL UNIQUE,
  `Pesel` VARCHAR(11) UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `Klienci` VALUES 
(1,'Anna','Kowalska','123456789','anna.kowal@dk.pl','12345456789'),
(2,'Jacek','Sowa','987-654-321','jacek.kowal@dk.pl','14565876545'),
(3,'Adam','Nowak','456-321-789','adam.nowak@dk.pl','13573554389');

-- -----------------------------------------------------
-- Table `Stanowiska`
-- -----------------------------------------------------
CREATE TABLE `Stanowiska` (
  `ID_stanowiska` INT AUTO_INCREMENT PRIMARY KEY,
  `Nazwa_stanowiska` VARCHAR(50) NOT NULL,
  `Placa_brutto` DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `Stanowiska` VALUES 
(1,'Recepcjonista',6000.00),
(2,'Menadżer usług',5000.00),
(3,'Pokojówka',4666.00);

-- -----------------------------------------------------
-- Table `Pracownicy`
-- -----------------------------------------------------
CREATE TABLE `Pracownicy` (
  `ID_Pracownika` INT AUTO_INCREMENT PRIMARY KEY,
  `Imie` VARCHAR(50) NOT NULL,
  `Nazwisko` VARCHAR(50) NOT NULL,
  `ID_stanowiska` INT,
  `Telefon` VARCHAR(15),
  `Email` VARCHAR(100) NOT NULL UNIQUE,
  `Pesel` VARCHAR(11) UNIQUE,
  FOREIGN KEY (`ID_stanowiska`) REFERENCES `Stanowiska`(`ID_stanowiska`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `Pracownicy` VALUES 
(1,'John','Doe',1,'212-132-435','john.doe@email.com','92438281093'),
(2,'William','Dafie',2,'932-748-578','will.dafie@email.com','98543768754');

-- -----------------------------------------------------
-- Table `TypyPokoi`
-- -----------------------------------------------------
CREATE TABLE `TypyPokoi` (
  `ID_Typu` INT AUTO_INCREMENT PRIMARY KEY,
  `NazwaTypu` VARCHAR(50) NOT NULL,
  `Opis` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `TypyPokoi` VALUES 
(1,'Standard 1 osoba','Standardowy typ pokoju dla 1 osoby.'),
(2,'Apartament deluxe','Luksusowe wnętrze z hydromasażem.');

-- -----------------------------------------------------
-- Table `Pokoje`
-- -----------------------------------------------------
CREATE TABLE `Pokoje` (
  `ID_Pokoju` INT AUTO_INCREMENT PRIMARY KEY,
  `NrPokoju` INT NOT NULL UNIQUE,
  `ID_Typu` INT NOT NULL,
  `CenaDobowa` DECIMAL(10,2) NOT NULL,
  `Status` ENUM('Wolny', 'Zajęty', 'Serwis') DEFAULT 'Wolny',
  `IlośćOsóbWPokoju` INT DEFAULT 1,
  `Pietro` INT DEFAULT 1,
  `Zdjecie_Pokoju` VARCHAR(255),
  FOREIGN KEY (`ID_Typu`) REFERENCES `TypyPokoi`(`ID_Typu`),
  INDEX `idx_pokoje_status` (`Status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `Pokoje` VALUES 
(1,101,1,200.00,'Zajęty',1,1,'download.jpg'),
(2,102,2,500.00,'Wolny',2,1,'');

-- -----------------------------------------------------
-- Table `Uslugi_dodatkowe`
-- -----------------------------------------------------
CREATE TABLE `Uslugi_dodatkowe` (
  `ID_uslugi` INT AUTO_INCREMENT PRIMARY KEY,
  `Nazwa_Uslugi` VARCHAR(100) NOT NULL,
  `Opis` TEXT,
  `Cena` DECIMAL(10,2) NOT NULL,
  `Kategoria` VARCHAR(50),
  `Czydostepna` BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `Uslugi_dodatkowe` VALUES 
(1,'Drinki do pokoju','Drinki dostarczane do pokoju',200.00,'Napoje',TRUE),
(2,'Śniadanie do łóżka','Śniadanie dostarczane do łóżka',500.00,'Jedzenie',TRUE);

-- -----------------------------------------------------
-- Table `Pracownicy_Uslugi` (Relacja N:M - wymóg na 4)
-- -----------------------------------------------------
CREATE TABLE `Pracownicy_Uslugi` (
  `ID_Pracownika` INT NOT NULL,
  `ID_Uslugi` INT NOT NULL,
  PRIMARY KEY (`ID_Pracownika`, `ID_Uslugi`),
  FOREIGN KEY (`ID_Pracownika`) REFERENCES `Pracownicy`(`ID_Pracownika`) ON DELETE CASCADE,
  FOREIGN KEY (`ID_Uslugi`) REFERENCES `Uslugi_dodatkowe`(`ID_uslugi`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table `Rezerwacje`
-- -----------------------------------------------------
CREATE TABLE `Rezerwacje` (
  `ID_Rezerwacji` INT AUTO_INCREMENT PRIMARY KEY,
  `ID_Pokoju` INT NOT NULL,
  `ID_Klienta` INT NOT NULL,
  `ID_Pracownika` INT,
  `DataOd` DATETIME NOT NULL,
  `DataDo` DATETIME NOT NULL,
  `Kwota` DECIMAL(10,2) NOT NULL,
  `Status` ENUM('Oczekująca', 'Potwierdzona', 'Ukończone', 'Anulowana') DEFAULT 'Oczekująca',
  `Zrodlo_rezerwacji` VARCHAR(50),
  `Uwagi` TEXT,
  FOREIGN KEY (`ID_Pokoju`) REFERENCES `Pokoje`(`ID_Pokoju`),
  FOREIGN KEY (`ID_Klienta`) REFERENCES `Klienci`(`ID_Klienta`),
  FOREIGN KEY (`ID_Pracownika`) REFERENCES `Pracownicy`(`ID_Pracownika`),
  INDEX `idx_rezerwacje_daty` (`DataOd`, `DataDo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `Rezerwacje` VALUES 
(1,1,1,1,'2026-01-27 00:00:00','2026-01-30 00:00:00',800.00,'Ukończone','Bezpośrednio','brak');

-- -----------------------------------------------------
-- Table `Platnosci`
-- -----------------------------------------------------
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

-- -----------------------------------------------------
-- Table `ZamowieniaUslugi`
-- -----------------------------------------------------
CREATE TABLE `ZamowieniaUslugi` (
  `ID_zamowienia` INT AUTO_INCREMENT PRIMARY KEY,
  `ID_rezerwacji` INT NOT NULL,
  `ID_uslugi` INT NOT NULL,
  `DataZamowienia` DATETIME NOT NULL,
  `Ilosc` INT DEFAULT 1,
  `Cena_jednostkowa` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`ID_rezerwacji`) REFERENCES `Rezerwacje`(`ID_Rezerwacji`) ON DELETE CASCADE,
  FOREIGN KEY (`ID_uslugi`) REFERENCES `Uslugi_dodatkowe`(`ID_uslugi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Rozszerzenie na Ocenę 6: Grafik / Dni wolne pracowników
-- -----------------------------------------------------
CREATE TABLE `GrafikPracownikow` (
  `ID_Wpisu` INT AUTO_INCREMENT PRIMARY KEY,
  `ID_Pracownika` INT NOT NULL,
  `Data` DATE NOT NULL,
  `TypDnia` ENUM('Praca', 'Urlop', 'Zwolnienie', 'Wolne') DEFAULT 'Praca',
  `GodzinaOd` TIME,
  `GodzinaDo` TIME,
  FOREIGN KEY (`ID_Pracownika`) REFERENCES `Pracownicy`(`ID_Pracownika`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
