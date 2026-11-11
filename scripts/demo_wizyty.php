<?php


if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
if (!in_array('--potwierdz-demo', $argv, true)) {
    exit("Uruchom na lokalnej bazie demo: php scripts/demo_wizyty.php --potwierdz-demo\n");
}
require __DIR__.'/../includes/db.php';
date_default_timezone_set('Europe/Warsaw');
try {
    $pdo->beginTransaction();
    $row = $pdo->query("SELECT p.ID_Pracownika,s.ID_Uslugi,s.CzasTrwania,s.Cena FROM Pracownicy p JOIN Uzytkownicy u ON u.ID_Uzytkownika=p.ID_Uzytkownika JOIN Pracownicy_Uslugi ps ON ps.ID_Pracownika=p.ID_Pracownika JOIN Uslugi_dodatkowe s ON s.ID_Uslugi=ps.ID_Uslugi WHERE p.Aktywny=1 AND u.Aktywny=1 AND s.Czydostepna=1 AND s.CzasTrwania<=60 ORDER BY p.ID_Pracownika,s.ID_Uslugi LIMIT 1 FOR UPDATE")->fetch();
    if (!$row) {
        throw new RuntimeException('Brak aktywnego pracownika z przypisaną aktywną usługą o czasie do 60 minut.');
    }
    if ($pdo->query("SELECT COUNT(*) FROM WizytyUslug WHERE Uwagi='ETAP3_DEMO_USLUGI'")->fetchColumn()) {
        $pdo->rollBack();
        exit("Wizyty demo już istnieją.\n");
    }
    $client = $pdo->query("SELECT ID_Uzytkownika FROM Uzytkownicy WHERE Rola='klient' AND Aktywny=1 ORDER BY ID_Uzytkownika LIMIT 1")->fetchColumn();
    if (!$client) {
        throw new RuntimeException('Brak aktywnego klienta.');
    }
    $done = false;
    for ($i = 1;$i <= 14;$i++) {
        $day = (new DateTimeImmutable('today'))->modify("+$i days")->format('Y-m-d');
        $q = $pdo->prepare('SELECT * FROM GrafikPracownikow WHERE ID_Pracownika=? AND Data=? FOR UPDATE');
        $q->execute([$row['ID_Pracownika'],$day]);
        $g = $q->fetch();
        if ($g && ($g['TypDnia'] !== 'Praca' || $g['GodzinaOd'] > '09:00:00' || $g['GodzinaDo'] < '12:00:00')) {
            continue;
        }
        $q = $pdo->prepare("SELECT COUNT(*) FROM WizytyUslug WHERE ID_Pracownika=? AND Status IN ('Oczekująca','Potwierdzona') AND DataOd<? AND DataDo>?");
        $q->execute([$row['ID_Pracownika'],$day.' 12:00:00',$day.' 09:00:00']);
        if ($q->fetchColumn()) {
            continue;
        }
        if (!$g) {
            $q = $pdo->prepare("INSERT INTO GrafikPracownikow(ID_Pracownika,Data,TypDnia,GodzinaOd,GodzinaDo) VALUES(?,?,'Praca','08:00','16:00')");
            $q->execute([$row['ID_Pracownika'],$day]);
        }
        foreach (['09:00','11:00'] as $hour) {
            $start = new DateTimeImmutable($day.' '.$hour);
            $end = $start->modify('+'.(int)$row['CzasTrwania'].' minutes');
            $q = $pdo->prepare("INSERT INTO WizytyUslug(ID_Klienta,ID_Pracownika,ID_Uslugi,DataOd,DataDo,Cena,Status,Uwagi) VALUES(?,?,?,?,?,?,'Oczekująca','ETAP3_DEMO_USLUGI')");
            $q->execute([$client,$row['ID_Pracownika'],$row['ID_Uslugi'],$start->format('Y-m-d H:i:s'),$end->format('Y-m-d H:i:s'),$row['Cena']]);
        }$done = true;
        break;
    }
    if (!$done) {
        throw new RuntimeException('Brak bezkolizyjnego dnia w kolejnych 14 dniach. Nic nie zapisano.');
    }
    $pdo->commit();
    echo "Dodano 2 fikcyjne wizyty usług i ewentualny brakujący dzień pracy.\n";
} catch (Throwable $ex) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }fwrite(STDERR, $ex->getMessage()."\n");
    exit(1);
}
