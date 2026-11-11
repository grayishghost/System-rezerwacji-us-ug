<?php


if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
if (!in_array('--potwierdz-demo', $argv, true)) {
    exit("Dodaje fikcyjne pobyty i zamówienia. Tylko lokalna baza pokazowa!\nUruchom: php scripts/demo_etap3.php --potwierdz-demo\n");
}
require __DIR__ . '/../includes/db.php';
date_default_timezone_set('Europe/Warsaw');
try {
    $pdo->beginTransaction();
    $rooms = $pdo->query('SELECT * FROM Pokoje ORDER BY ID_Pokoju LIMIT 2 FOR UPDATE')->fetchAll();
    if ($pdo->query("SELECT COUNT(*) FROM Rezerwacje WHERE Zrodlo_rezerwacji='ETAP3_DEMO'")->fetchColumn()) {
        $pdo->rollBack();
        exit("Dane demo już istnieją. Nie dodano duplikatów.\n");
    }
    $client = $pdo->query("SELECT ID_Uzytkownika FROM Uzytkownicy WHERE Rola='klient' AND Aktywny=1 ORDER BY ID_Uzytkownika LIMIT 1")->fetchColumn();
    $employee = $pdo->query("SELECT p.ID_Pracownika FROM Pracownicy p JOIN Uzytkownicy u ON u.ID_Uzytkownika=p.ID_Uzytkownika WHERE p.Aktywny=1 AND u.Aktywny=1 ORDER BY p.ID_Pracownika LIMIT 1")->fetchColumn();
    if (!$rooms || !$client || !$employee) {
        throw new RuntimeException('Potrzebujesz pokoju, aktywnego klienta i pracownika.');
    }
    $plans = [[-12,-9,'Ukończone'],[-5,-3,'Potwierdzona'],[-1,2,'Potwierdzona'],[3,5,'Oczekująca'],[7,9,'Potwierdzona'],[10,12,'Anulowana']];
    $today = new DateTimeImmutable('today');
    foreach ($plans as $i => [$from,$to,$status]) {
        $room = $rooms[$i % count($rooms)];
        $od = $today->modify("$from days")->format('Y-m-d');
        $do = $today->modify("$to days")->format('Y-m-d');
        $q = $pdo->prepare("SELECT COUNT(*) FROM Rezerwacje WHERE ID_Pokoju=? AND Status IN ('Oczekująca','Potwierdzona') AND DataOd<? AND DataDo>?");
        $q->execute([$room['ID_Pokoju'],$do,$od]);
        if ($q->fetchColumn()) {
            throw new RuntimeException('Konflikt danych demo z pobytem. Nie zapisano żadnych danych demo.');
        }
        $q = $pdo->prepare("INSERT INTO Rezerwacje(ID_Pokoju,ID_Klienta,ID_Pracownika,DataOd,DataDo,Kwota,Status,Zrodlo_rezerwacji,Uwagi) VALUES(?,?,?,?,?,?,?,'ETAP3_DEMO','Fikcyjne dane do prezentacji etapu 3')");
        $q->execute([$room['ID_Pokoju'],$client,$employee,$od,$do,($to - $from) * (float)$room['CenaDobowa'],$status]);
        $rid = $pdo->lastInsertId();
        if ($i === 2) {
            $service = $pdo->query('SELECT ID_Uslugi,Cena FROM Uslugi_dodatkowe WHERE Czydostepna=1 ORDER BY ID_Uslugi LIMIT 1')->fetch();
            if ($service) {
                $q = $pdo->prepare('INSERT INTO ZamowieniaUslugi(ID_Rezerwacji,ID_Uslugi,DataZamowienia,Ilosc,Cena_jednostkowa) VALUES(?,?,?,1,?)');
                $q->execute([$rid,$service['ID_Uslugi'],$od.' 12:00:00',$service['Cena']]);
            }
        }
    }
    $pdo->commit();
    echo "Dodano 6 fikcyjnych pobytów, w tym bieżący i przyszłe, oraz przykładowe zamówienie usługi.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, 'Błąd: '.$e->getMessage()."\n");
    exit(1);
}
