ALTER TABLE Uzytkownicy ADD Wersja INT NOT NULL DEFAULT 1, ADD SesjaWersja INT NOT NULL DEFAULT 1, ADD CONSTRAINT chk_i007_konto_wersja CHECK(Wersja>=1), ADD CONSTRAINT chk_i007_sesja_wersja CHECK(SesjaWersja>=1);
ALTER TABLE Stanowiska ADD Wersja INT NOT NULL DEFAULT 1, ADD CONSTRAINT chk_i007_stanowisko_wersja CHECK(Wersja>=1), ADD CONSTRAINT uq_i007_stanowisko UNIQUE(Nazwa_stanowiska);
ALTER TABLE TypyPokoi ADD Wersja INT NOT NULL DEFAULT 1, ADD CONSTRAINT chk_i007_typ_wersja CHECK(Wersja>=1), ADD CONSTRAINT uq_i007_typ UNIQUE(NazwaTypu);
ALTER TABLE Rezerwacje ADD Archiwum TINYINT(1) NOT NULL DEFAULT 0, ADD CONSTRAINT chk_i007_archiwum CHECK(Archiwum IN (0,1));
