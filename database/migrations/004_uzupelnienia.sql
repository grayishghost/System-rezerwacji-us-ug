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
