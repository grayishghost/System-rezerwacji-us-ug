CREATE TABLE HistoriaWizyt (
 ID_Zmiany INT AUTO_INCREMENT PRIMARY KEY,
 ID_Wizyty INT NOT NULL,
 Wersja INT NOT NULL,
 Typ ENUM('Utworzenie','Status','Przełożenie','Stan początkowy') NOT NULL,
 Status ENUM('Oczekująca','Potwierdzona','Ukończone','Anulowana') NOT NULL,
 DataOd DATETIME NOT NULL,
 DataDo DATETIME NOT NULL,
 ID_Autora INT NULL,
 Czas DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_i008_historia_wizyta FOREIGN KEY(ID_Wizyty) REFERENCES WizytyUslug(ID_Wizyty) ON DELETE RESTRICT,
 CONSTRAINT fk_i008_historia_autor FOREIGN KEY(ID_Autora) REFERENCES Uzytkownicy(ID_Uzytkownika) ON DELETE SET NULL,
 CONSTRAINT chk_i008_historia_wersja CHECK(Wersja>=1),
 CONSTRAINT chk_i008_historia_daty CHECK(DataDo>DataOd),
 CONSTRAINT uq_i008_historia_wersja UNIQUE(ID_Wizyty,Wersja)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO HistoriaWizyt(ID_Wizyty,Wersja,Typ,Status,DataOd,DataDo) SELECT ID_Wizyty,Wersja,'Stan początkowy',Status,DataOd,DataDo FROM WizytyUslug;
