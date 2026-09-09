-- MySQL dump 10.13  Distrib 8.4.4, for Linux (x86_64)
--
-- Host: localhost    Database: db
-- ------------------------------------------------------
-- Server version	8.4.4

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `HistoriaPokoi`
--

DROP TABLE IF EXISTS `HistoriaPokoi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `HistoriaPokoi` (
  `ID_Wpisu` tinyint DEFAULT NULL,
  `ID_Pokoju` tinyint DEFAULT NULL,
  `ID_Klienta` tinyint DEFAULT NULL,
  `DataZameldowania` varchar(19) DEFAULT NULL,
  `DataWymeldowania` varchar(19) DEFAULT NULL,
  `Uwagi` varchar(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `HistoriaPokoi`
--

LOCK TABLES `HistoriaPokoi` WRITE;
/*!40000 ALTER TABLE `HistoriaPokoi` DISABLE KEYS */;
INSERT INTO `HistoriaPokoi` VALUES (1,1,1,'2025-12-27 00:00:00','2025-12-28 00:00:00','brak'),(3,2,2,'2025-12-27 00:00:00','2025-12-28 00:00:00','brak'),(4,2,3,'2025-12-16 00:00:00','2025-12-26 00:00:00','brak'),(5,3,4,'2025-12-05 00:00:00','2025-12-26 00:00:00','brak'),(6,4,5,'2025-12-30 00:00:00','2025-12-31 00:00:00','brak'),(7,5,6,'2025-12-27 00:00:00','2025-12-30 00:00:00','brak'),(8,6,8,'2025-12-22 00:00:00','2025-12-28 00:00:00','brak'),(9,7,9,'2025-12-29 00:00:00','2025-12-30 00:00:00','brak'),(10,8,10,'2025-12-29 00:00:00','2025-12-31 00:00:00','brak'),(11,9,11,'2025-12-22 00:00:00','2025-12-24 00:00:00','brak');
/*!40000 ALTER TABLE `HistoriaPokoi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Klienci`
--

DROP TABLE IF EXISTS `Klienci`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Klienci` (
  `ID_Klienta` tinyint DEFAULT NULL,
  `Imie` varchar(6) DEFAULT NULL,
  `Nazwisko` varchar(11) DEFAULT NULL,
  `Telefon` varchar(11) DEFAULT NULL,
  `Email` varchar(24) DEFAULT NULL,
  `Pesel` bigint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Klienci`
--

LOCK TABLES `Klienci` WRITE;
/*!40000 ALTER TABLE `Klienci` DISABLE KEYS */;
INSERT INTO `Klienci` VALUES (1,'Anna','Kowalska','123456789','anna.kowal@dk.pl',12345456789),(2,'Jacek','Sowa','987-654-321','jacek.kowal@dk.pl',14565876545),(3,'Adam','Nowak','456-321-789','adam.nowak@dk.pl',13573554389),(4,'Ivan','Groźny','321-567-098','ivan.grozny@dk.pl',66688899900),(5,'Su','Wu','666-555-444','su.wu@dk.pl',12354553321),(6,'Angy','Red','123-422-322','angy.red@dk.pl',11111111111),(8,'Syren','Jotan','577-362-274','syren.jotan@dk.pl',84743637282),(9,'Navita','Enchantress','756-276-572','navita.enchantress@dk.pl',38747384738),(10,'Marco','Polo','387-482-787','marco.polo@dk.pl',35483768729),(11,'Chin','Ciag','298-732-787','chin.ciag@dk.pl',98294372890),(12,'Tye','Sulgester','327-468-372','tye.sylgester@dk.pl',32748764328),(13,'Tyrone','Black','884-768-463','tyrone.black@dk.pl',76532873816),(14,'Hose','Juan','443-552-117','hose.juan@dk.pl',43682798764),(15,'Dingi','Gupta','654-329-890','dingi.gupta@dk.pl',77646524718),(16,'Arthur','Morgan','573-757-298','arthur.morgan@dk.pl',97248763829),(17,'Octan','Diesel','783-782-350','octan.diesel@dk.pl',82846377283),(18,'Lucian','Morningstar','927-846-372','lucian.morningstar@dk.pl',77000898646),(19,'Blak','Bixz','798-578-378','blak.bixz@dk.pl',42432998398),(20,'Lalita','Dead','883-929-075','lalita.dead@dk.pl',62561778467);
/*!40000 ALTER TABLE `Klienci` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Platnosci`
--

DROP TABLE IF EXISTS `Platnosci`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Platnosci` (
  `ID_Płatności` tinyint DEFAULT NULL,
  `ID_Rezerwacji` tinyint DEFAULT NULL,
  `DataPłatności` varchar(19) DEFAULT NULL,
  `Kwota` smallint DEFAULT NULL,
  `Metoda` varchar(15) DEFAULT NULL,
  `Status` varchar(9) DEFAULT NULL,
  `Typ płatności` varchar(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Platnosci`
--

LOCK TABLES `Platnosci` WRITE;
/*!40000 ALTER TABLE `Platnosci` DISABLE KEYS */;
INSERT INTO `Platnosci` VALUES (6,9,'2026-01-27 00:00:00',800,'Karta','Ukończone','Natychmiastowa'),(1,8,'2026-01-15 00:00:00',600,'Gotówka','Ukończone','Natychmiastowa'),(2,3,'2026-01-19 00:00:00',450,'Przelew bankowy','Ukończone','Przedpłata'),(3,4,'2026-01-26 00:00:00',1200,'Karta','Ukończone','Natychmiastowa'),(4,7,'2026-01-29 00:00:00',300,'Gotówka','Ukończone','Natychmiastowa'),(5,8,'2026-01-28 00:00:00',800,'Gotówka','Ukończone','Natychmiastowa');
/*!40000 ALTER TABLE `Platnosci` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Pokoje`
--

DROP TABLE IF EXISTS `Pokoje`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Pokoje` (
  `ID_Pokoju` tinyint DEFAULT NULL,
  `NrPokoju` tinyint DEFAULT NULL,
  `TypPokoju` tinyint DEFAULT NULL,
  `CenaDobowa` smallint DEFAULT NULL,
  `Status` varchar(6) DEFAULT NULL,
  `IlośćOsóbWPokoju` tinyint DEFAULT NULL,
  `Pietro` tinyint DEFAULT NULL,
  `Zdjecie_Pokoju` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Pokoje`
--

LOCK TABLES `Pokoje` WRITE;
/*!40000 ALTER TABLE `Pokoje` DISABLE KEYS */;
INSERT INTO `Pokoje` VALUES (1,1,1,200,'Zajęty',1,1,'download.jpg'),(2,2,2,200,'Zajęty',2,2,''),(3,3,3,200,'Zajęty',3,3,''),(4,4,4,200,'Wolny',0,4,''),(5,5,5,500,'Wolny',0,1,''),(6,6,6,150,'Zajęty',1,2,''),(7,7,7,700,'Wolny',0,3,''),(8,8,8,350,'Wolny',0,4,''),(9,9,9,100,'Zajęty',4,1,''),(10,10,10,275,'Wolny',0,2,''),(11,11,11,5000,'Wolny',0,3,''),(13,13,13,180,'Wolny',0,2,''),(14,14,14,1500,'Wolny',0,3,''),(15,15,15,400,'Zajęty',4,4,''),(16,16,16,355,'Wolny',0,4,''),(17,17,17,450,'Wolny',0,3,''),(18,18,18,600,'Wolny',0,2,''),(19,19,19,180,'Wolny',0,1,''),(20,20,20,2000,'Wolny',0,4,''),(23,21,1,200,'Wolny',0,1,''),(24,22,2,200,'Wolny',0,2,''),(25,23,3,200,'Wolny',0,3,''),(26,24,4,200,'Wolny',0,4,'');
/*!40000 ALTER TABLE `Pokoje` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Pracownicy`
--

DROP TABLE IF EXISTS `Pracownicy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Pracownicy` (
  `ID_Pracownika` tinyint DEFAULT NULL,
  `Imie` varchar(7) DEFAULT NULL,
  `Nazwisko` varchar(7) DEFAULT NULL,
  `Stanowisko` tinyint DEFAULT NULL,
  `Telefon` varchar(11) DEFAULT NULL,
  `Email` varchar(25) DEFAULT NULL,
  `Pesel` bigint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Pracownicy`
--

LOCK TABLES `Pracownicy` WRITE;
/*!40000 ALTER TABLE `Pracownicy` DISABLE KEYS */;
INSERT INTO `Pracownicy` VALUES (5,'John','Doe',1,'212-132-435','john.doe@email.com',92438281093),(6,'William','Dafie',2,'932-748-578','will.dafie@email.com',98543768754),(7,'Baz','Nider',3,'009-091-839','baz.nider@email.com',72536527817),(8,'Gill','Mchoy',4,'186-274-578','gill.mchoy@email.com',26435176724),(9,'Dan','Tomsky',5,'261-824-765','dan.tomsky@email.com',92714983672),(10,'Louis','Defines',6,'732-658-329','louis.defines@email.com',87648618732),(11,'Jack','Merd',7,'274-365-782','jack.merd@email.com',17264879018),(12,'Tom','Junak',8,'298-784-637','tom.junak@email.com',83982435931),(14,'Wu','Zung',10,'658-439-876','wu.zung@email.com',72638756327),(15,'Bu','Wung',11,'986-528-383','bu.wung@email.com',32321023000),(17,'Michael','DeSanta',13,'382-987-611','michael.desanta@email.com',98765434156),(18,'Nina','Flyer',14,'939-709-871','nina.flyer@email.com',98329863978),(19,'Tyrone','Black',15,'356-278-365','tyrone.black@email.com',27371683751),(20,'Weya','Sung',16,'765-483-974','weya.sung@email.com',76261837512),(21,'Touruk','Makto',17,'424-562-135','touruk.makto@email.com',29478279345),(22,'Paweł','Sitarz',1,'286-737-629','pawel.sitarz@email.com',19748376293);
/*!40000 ALTER TABLE `Pracownicy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PrzegladWyposarzenia`
--

DROP TABLE IF EXISTS `PrzegladWyposarzenia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `PrzegladWyposarzenia` (
  `ID_Wyposazenia` tinyint DEFAULT NULL,
  `ID_pokoju` tinyint DEFAULT NULL,
  `NazwaElementu` varchar(16) DEFAULT NULL,
  `Stan` varchar(7) DEFAULT NULL,
  `DataOstatniegoPrzegladu` varchar(19) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PrzegladWyposarzenia`
--

LOCK TABLES `PrzegladWyposarzenia` WRITE;
/*!40000 ALTER TABLE `PrzegladWyposarzenia` DISABLE KEYS */;
INSERT INTO `PrzegladWyposarzenia` VALUES (1,4,'Czajnik','Sprawne','2025-12-28 00:00:00'),(2,4,'Lampka','Sprawne','2025-12-28 00:00:00'),(3,1,'Łóżko','Sprawne','2025-12-28 00:00:00'),(4,1,'Stolik','Sprawne','2025-12-27 00:00:00'),(5,2,'Łóżko dwuosobowe','Sprawne','2025-12-28 00:00:00'),(6,2,'TV','Sprawne','2025-12-27 00:00:00'),(7,3,'Sofa','Sprawne','2025-12-28 00:00:00'),(8,17,'Sauna','Sprawne','2026-01-01 00:00:00');
/*!40000 ALTER TABLE `PrzegladWyposarzenia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Rezerwacje`
--

DROP TABLE IF EXISTS `Rezerwacje`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Rezerwacje` (
  `ID_Rezerwacji` tinyint DEFAULT NULL,
  `ID_Pokoju` tinyint DEFAULT NULL,
  `ID_Klienta` tinyint DEFAULT NULL,
  `DataOd` varchar(19) DEFAULT NULL,
  `DataDo` varchar(19) DEFAULT NULL,
  `Kwota` smallint DEFAULT NULL,
  `Status` varchar(9) DEFAULT NULL,
  `ID_Pracownika` tinyint DEFAULT NULL,
  `Zrodlo rezerwacji` varchar(12) DEFAULT NULL,
  `Uwagi` varchar(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Rezerwacje`
--

LOCK TABLES `Rezerwacje` WRITE;
/*!40000 ALTER TABLE `Rezerwacje` DISABLE KEYS */;
INSERT INTO `Rezerwacje` VALUES (9,3,18,'2026-01-27 00:00:00','2026-01-30 00:00:00',800,'Ukończone',22,'Bezpośrednio','brak'),(2,2,5,'2026-01-15 00:00:00','2026-01-18 00:00:00',600,'Ukończone',5,'Bezpośrednio','brak'),(3,6,2,'2026-01-21 00:00:00','2026-01-23 00:00:00',450,'Ukończone',22,'Telefon','brak'),(4,15,3,'2026-01-26 00:00:00','2026-01-29 00:00:00',1200,'Ukończone',5,'Strona','brak'),(7,9,5,'2026-01-29 00:00:00','2026-01-31 00:00:00',300,'Ukończone',22,'Bezpośrednio','brak'),(8,1,4,'2026-01-28 00:00:00','2026-01-31 00:00:00',800,'Ukończone',5,'Bezpośrednio','brak');
/*!40000 ALTER TABLE `Rezerwacje` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Sprzatanie`
--

DROP TABLE IF EXISTS `Sprzatanie`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Sprzatanie` (
  `ID_Sprzatania` tinyint DEFAULT NULL,
  `ID_Pokoju` tinyint DEFAULT NULL,
  `DataSprzatania` varchar(19) DEFAULT NULL,
  `StatusSprzatania` varchar(9) DEFAULT NULL,
  `Uwagi` varchar(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Sprzatanie`
--

LOCK TABLES `Sprzatanie` WRITE;
/*!40000 ALTER TABLE `Sprzatanie` DISABLE KEYS */;
INSERT INTO `Sprzatanie` VALUES (4,4,'2025-12-28 00:00:00','Ukończone','brak'),(6,2,'2025-12-26 00:00:00','Ukończone','brak'),(7,3,'2025-12-24 00:00:00','Ukończone','brak'),(8,4,'2025-12-24 00:00:00','Ukończone','brak'),(9,5,'2025-12-10 00:00:00','Ukończone','brak'),(10,16,'2026-01-08 00:00:00','Ukończone','brak');
/*!40000 ALTER TABLE `Sprzatanie` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Stanowiska`
--

DROP TABLE IF EXISTS `Stanowiska`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Stanowiska` (
  `ID_stanowiska` tinyint DEFAULT NULL,
  `Nazwa_stanowiska` varchar(21) DEFAULT NULL,
  `Placa_brutto` mediumint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Stanowiska`
--

LOCK TABLES `Stanowiska` WRITE;
/*!40000 ALTER TABLE `Stanowiska` DISABLE KEYS */;
INSERT INTO `Stanowiska` VALUES (1,'Recepcjonista',6000),(2,'Menadżer usług',5000),(3,'Portier',4666),(4,'Pokojówka',4666),(5,'Inspektor pięter',4666),(6,'Konserwator',4900),(7,'Dyrektor',13000),(8,'Menadżer hotelu',9000),(10,'Parkingowy',4666),(11,'Animator',4666),(13,'Room service',4700),(14,'Ksiegowy',7200),(15,'Ogrodnik',4666),(16,'Ochroniarz',4666),(17,'Pracownik sprzątający',4666),(18,'Masażysta',4666);
/*!40000 ALTER TABLE `Stanowiska` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Transakcje`
--

DROP TABLE IF EXISTS `Transakcje`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Transakcje` (
  `ID_Transakcji` tinyint DEFAULT NULL,
  `ID_Klienta` tinyint DEFAULT NULL,
  `ID_Pracownika` tinyint DEFAULT NULL,
  `ID_platnosci` tinyint DEFAULT NULL,
  `ID_zamowienia` tinyint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Transakcje`
--

LOCK TABLES `Transakcje` WRITE;
/*!40000 ALTER TABLE `Transakcje` DISABLE KEYS */;
INSERT INTO `Transakcje` VALUES (3,1,14,1,1),(4,2,17,2,2),(5,3,15,3,3),(6,5,8,4,4),(7,4,5,5,5),(8,18,17,6,6);
/*!40000 ALTER TABLE `Transakcje` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `TypyPokoi`
--

DROP TABLE IF EXISTS `TypyPokoi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `TypyPokoi` (
  `ID_Typu` tinyint DEFAULT NULL,
  `NazwaTypu` varchar(27) DEFAULT NULL,
  `Opis` varchar(72) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `TypyPokoi`
--

LOCK TABLES `TypyPokoi` WRITE;
/*!40000 ALTER TABLE `TypyPokoi` DISABLE KEYS */;
INSERT INTO `TypyPokoi` VALUES (1,'Standard 1 osoba','Standardowy typ pokoju dla 1 osoby.'),(2,'Standard 2 osoby','Standardowy typ pokoju dla 2 osób'),(3,'Standard 3 osoby','Standardowy typ pokoju dla 3 osób'),(4,'Standard 4 osoby','Standardowy typ pokoju dla 4 osób'),(5,'Apartament junior','Sypialnia połączona z niewielkim salonem i ekspresem do kawy.'),(6,'Apartament deluxe','Luksusowe wnętrze, oddzielna sypialnia i wanna z hydromasażem.'),(7,'Apartament Prezydencki','Najwyższy standard: dwie sypialnie, salon, aneks i taras widokowy.'),(8,'Pokój Biznesowy','Wyposażony w szybkie Wi-Fi, drukarkę i ergonomiczne krzesło.'),(9,'Pokój Ekonomiczny','Mniejszy pokój w atrakcyjnej cenie, podstawowe wyposażenie.'),(10,'Pokój dla niepełnosprawnych','Brak barier architektonicznych, szerokie przejścia i uchwyty w łazience.'),(11,'Pokój Tematyczny \"Morski\"','Wystrój inspirowany żeglarstwem i błękitem oceanu.'),(13,'Pokój tematyczny \"Ogrodowy\"','Wystrój inspirowany ogrodnictwem zawierający mnóstwo roślin doniczkowy.'),(14,'Pokój jednoosobowy Plus','Łóżko o szerokości 120cm, klimatyzacja i minibar w cenie.'),(15,'Dwuosobowy Superior','Większy metraż, zestaw wypoczynkowy oraz widok na ogród.'),(16,'Pokój Rodzinny 2+2','Przestronny pokój z łóżkiem małżeńskim i rozkładaną sofą dla dzieci.'),(17,'Pokój typu Twin','Pokój z dwoma oddzielnymi łóżkami, idealny dla wyjazdów służbowych.'),(18,'Pokój z widokiem na morze','Gwarantowany widok na linię brzegową z balkonu.'),(19,'Studio z aneksem','Pokój z w pełni wyposażoną kuchnią, idealny na dłuższe pobyty.'),(20,'Suite SPA','Pokój z prywatną sauną i masażerami wewnątrz łazienki.'),(21,'Penthouse','\r\nEkskluzywny lokal na ostatnim piętrze z prywatną windą.');
/*!40000 ALTER TABLE `TypyPokoi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Uslugi_dodatkowe`
--

DROP TABLE IF EXISTS `Uslugi_dodatkowe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Uslugi_dodatkowe` (
  `ID_uslugi` tinyint DEFAULT NULL,
  `Nazwa_Uslugi` varchar(22) DEFAULT NULL,
  `Opis` varchar(31) DEFAULT NULL,
  `Cena` smallint DEFAULT NULL,
  `Kategoria` varchar(10) DEFAULT NULL,
  `Czydostepna` varchar(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Uslugi_dodatkowe`
--

LOCK TABLES `Uslugi_dodatkowe` WRITE;
/*!40000 ALTER TABLE `Uslugi_dodatkowe` DISABLE KEYS */;
INSERT INTO `Uslugi_dodatkowe` VALUES (1,'Drinki do pokoju','Drinki dostarczane do pokoju',200,'Napoje','Tak'),(2,'Śniadanie do łóżka','Śniadanie dostarczane do łóżka',500,'Jedzenie','Tak'),(3,'Animator','Zabawy z dziećmi na godzine',100,'Dla Dzieci','Tak'),(4,'Dodatkowe sprzątanie','Na zamówienie klienta',100,'Sprzątanie','Tak'),(5,'Informacja turystyczna','Podanie informacji turystycznej',20,'Turystyka','Tak'),(6,'Budzenie na życznie','Budzenie gościa na jego życznie',120,'Goście','Tak');
/*!40000 ALTER TABLE `Uslugi_dodatkowe` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ZamowieniaUslugi`
--

DROP TABLE IF EXISTS `ZamowieniaUslugi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ZamowieniaUslugi` (
  `ID_zamowienia` tinyint DEFAULT NULL,
  `id_rezerwacji` tinyint DEFAULT NULL,
  `id_uslugi` tinyint DEFAULT NULL,
  `dataZamowienia` varchar(19) DEFAULT NULL,
  `ilosc` tinyint DEFAULT NULL,
  `cena_jednostkowa` smallint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ZamowieniaUslugi`
--

LOCK TABLES `ZamowieniaUslugi` WRITE;
/*!40000 ALTER TABLE `ZamowieniaUslugi` DISABLE KEYS */;
INSERT INTO `ZamowieniaUslugi` VALUES (1,2,2,'2026-01-16 00:00:00',1,200),(2,3,1,'2026-01-22 00:00:00',2,200),(3,4,3,'2026-01-27 00:00:00',1,100),(4,7,4,'2026-01-30 00:00:00',1,100),(5,8,5,'2026-01-30 00:00:00',1,20),(6,9,6,'2026-01-28 00:00:00',1,120);
/*!40000 ALTER TABLE `ZamowieniaUslugi` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-11 13:30:03
