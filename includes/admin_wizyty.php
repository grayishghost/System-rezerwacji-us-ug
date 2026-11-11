<?php

require_once __DIR__ . '/admin_rezerwacje.php';
function aw_select()
{
    return "SELECT w.*,CONCAT(k.Imie,' ',k.Nazwisko) AS Klient,CONCAT(u.Imie,' ',u.Nazwisko) AS Pracownik,s.Nazwa_Uslugi";
}
function aw_from()
{
    return ' FROM WizytyUslug w JOIN Uzytkownicy k ON k.ID_Uzytkownika=w.ID_Klienta JOIN Pracownicy p ON p.ID_Pracownika=w.ID_Pracownika JOIN Uzytkownicy u ON u.ID_Uzytkownika=p.ID_Uzytkownika JOIN Uslugi_dodatkowe s ON s.ID_Uslugi=w.ID_Uslugi';
}
function aw_filter()
{
    $where = ['1=1'];
    $params = [];
    $error = '';
    try {
        $od = a_get('od');
        $do = a_get('do');
        if ($od !== '') {
            a_date($od);
            $where[] = 'w.DataOd >= ?';
            $params[] = $od.' 00:00:00';
        }
        if ($do !== '') {
            a_date($do);
            $where[] = 'w.DataOd < ?';
            $params[] = (new DateTimeImmutable($do))->modify('+1 day')->format('Y-m-d').' 00:00:00';
        }
        if ($od !== '' && $do !== '' && $od > $do) {
            throw new DomainException('Błędny zakres dat.');
        }
        if (a_get('status') !== '') {
            if (!in_array(a_get('status'), ar_statuses(), true)) {
                throw new DomainException('Błędny status.');
            }$where[] = 'w.Status=?';
            $params[] = a_get('status');
        }
        foreach (['pracownik' => 'w.ID_Pracownika','usluga' => 'w.ID_Uslugi'] as $key => $col) {
            if (a_get($key) !== '') {
                $where[] = $col.'=?';
                $params[] = a_id(a_get($key));
            }
        }
    } catch (DomainException $ex) {
        $where = ['1=0'];
        $params = [];
        $error = $ex->getMessage();
    }
    return ['sql' => ' WHERE '.implode(' AND ', $where),'params' => $params,'error' => $error];
}
function aw_create()
{
    global $pdo;
    
    $eid = a_id(pole_tekstowe('pracownik'));
    $emp = a_record('Pracownicy', 'ID_Pracownika', $eid, true);
    $user = a_record('Uzytkownicy', 'ID_Uzytkownika', $emp['ID_Uzytkownika']);
    if (!$emp['Aktywny'] || !$user['Aktywny'] || $user['Rola'] !== 'pracownik') {
        throw new DomainException('Pracownik musi mieć aktywny profil i konto.');
    }
    $cid = a_id(pole_tekstowe('klient'));
    $client = a_record('Uzytkownicy', 'ID_Uzytkownika', $cid);
    if ($client['Rola'] !== 'klient' || !$client['Aktywny']) {
        throw new DomainException('Wybierz aktywnego klienta.');
    }
    $sid = a_id(pole_tekstowe('usluga'));
    $service = a_record('Uslugi_dodatkowe', 'ID_Uslugi', $sid, true);
    if (!$service['Czydostepna']) {
        throw new DomainException('Usługa jest nieaktywna.');
    }
    if (!a_query('SELECT 1 FROM Pracownicy_Uslugi WHERE ID_Pracownika=? AND ID_Uslugi=?', [$eid,$sid])->fetchColumn()) {
        throw new DomainException('Pracownik nie ma przypisanej tej usługi.');
    }
    $raw = pole_tekstowe('termin');
    $start = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $raw);
    if (!$start || $start->format('Y-m-d\TH:i') !== $raw || $start < new DateTimeImmutable()) {
        throw new DomainException('Podaj poprawny przyszły termin.');
    }
    a_date($start->format('Y-m-d'));
    $end = $start->modify('+'.(int)$service['CzasTrwania'].' minutes');
    a_date($end->format('Y-m-d'));
    if ($start->format('Y-m-d') !== $end->format('Y-m-d')) {
        throw new DomainException('Wizyta musi mieścić się w jednym dniu pracy.');
    }
    $day = $start->format('Y-m-d');
    $schedule = a_query('SELECT * FROM GrafikPracownikow WHERE ID_Pracownika=? AND Data=? FOR UPDATE', [$eid,$day])->fetch();
    if (!$schedule || $schedule['TypDnia'] !== 'Praca' || $start->format('H:i:s') < $schedule['GodzinaOd'] || $end->format('H:i:s') > $schedule['GodzinaDo']) {
        throw new DomainException('Termin jest poza grafikiem pracy lub przypada w dniu wolnym/urlopie.');
    }
    $from = $start->format('Y-m-d H:i:s');
    $to = $end->format('Y-m-d H:i:s');
    if (a_query("SELECT COUNT(*) FROM WizytyUslug WHERE ID_Pracownika=? AND Status IN ('Oczekująca','Potwierdzona') AND DataOd<? AND DataDo>?", [$eid,$to,$from])->fetchColumn()) {
        throw new DomainException('Termin nakłada się na inną wizytę pracownika.');
    }
    $notes = a_text('uwagi', 1000, false);
    a_query('INSERT INTO WizytyUslug(ID_Klienta,ID_Pracownika,ID_Uslugi,DataOd,DataDo,Cena,Uwagi) VALUES(?,?,?,?,?,?,?)', [$cid,$eid,$sid,$from,$to,$service['Cena'],$notes]);
    $id = (int)$pdo->lastInsertId();
    a_log('Utworzenie wizyty', 'wizyta', $id, null, ['ID_Pracownika' => $eid,'ID_Uslugi' => $sid,'DataOd' => $from,'DataDo' => $to,'Status' => 'Oczekująca']);
}
