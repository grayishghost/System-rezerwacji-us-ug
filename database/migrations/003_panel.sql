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
