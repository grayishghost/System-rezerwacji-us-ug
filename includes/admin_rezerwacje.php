<?php

require_once __DIR__ . '/admin.php';
function ar_statuses()
{
    return ['Oczekująca','Potwierdzona','Ukończone','Anulowana'];
}
function ar_transitions($status)
{
    $map = ['Oczekująca' => ['Oczekująca','Potwierdzona','Anulowana'],
          'Potwierdzona' => ['Potwierdzona','Ukończone','Anulowana'],
          'Ukończone' => ['Ukończone'],'Anulowana' => ['Anulowana']];
    return $map[$status] ?? [];
}
function ar_version($r)
{
    return hash('sha256', json_encode([$r['Status'],(string)$r['ID_Pracownika'],(string)$r['ID_Pokoju'],$r['DataOd'],$r['DataDo'],(string)$r['Kwota'],(string)($r['LiczbaOsob'] ?? ''),(string)$r['Wersja']]));
}
function ar_filter()
{
    $where = ['1=1'];
    $params = [];
    $error = '';
    $od = a_get('od');
    $do = a_get('do');
    $status = a_get('status');
    $employee = a_get('pracownik');
    $q = mb_substr(a_get('q'), 0, 100);
    try {
        if ($od !== '') {
            a_date($od);
            $where[] = 'r.DataDo > ?';
            $params[] = $od.' 00:00:00';
        }
        if ($do !== '') {
            a_date($do);
            $where[] = 'r.DataOd < ?';
            $params[] = (new DateTimeImmutable($do))->modify('+1 day')->format('Y-m-d').' 00:00:00';
        }
        if ($od !== '' && $do !== '' && $od > $do) {
            throw new DomainException('Początek zakresu nie może być późniejszy niż koniec.');
        }
        if ($status !== '') {
            if (!in_array($status, ar_statuses(), true)) {
                throw new DomainException('Niepoprawny status.');
            }
            $where[] = 'r.Status=?';
            $params[] = $status;
        }
        if ($employee === 'brak') {
            $where[] = 'r.ID_Pracownika IS NULL';
        } elseif ($employee !== '') {
            $where[] = 'r.ID_Pracownika=?';
            $params[] = a_id($employee);
        }
        if ($q !== '') {
            $where[] = "(u.Imie LIKE ? OR u.Nazwisko LIKE ? OR CAST(r.ID_Rezerwacji AS CHAR)=? OR CAST(p.NrPokoju AS CHAR)=?)";
            $params = array_merge($params, ['%'.$q.'%','%'.$q.'%',$q,$q]);
        }
    } catch (DomainException $ex) {
        $error = $ex->getMessage();
        $where = ['1=0'];
        $params = [];
    }
    return ['sql' => ' WHERE '.implode(' AND ', $where),'params' => $params,'error' => $error,
            'values' => compact('od', 'do', 'status', 'employee', 'q')];
}
function ar_from()
{
    return " FROM Rezerwacje r JOIN Uzytkownicy u ON u.ID_Uzytkownika=r.ID_Klienta
      JOIN Pokoje p ON p.ID_Pokoju=r.ID_Pokoju LEFT JOIN Pracownicy e ON e.ID_Pracownika=r.ID_Pracownika
      LEFT JOIN Uzytkownicy eu ON eu.ID_Uzytkownika=e.ID_Uzytkownika";
}
function ar_select()
{
    return "SELECT r.*,p.NrPokoju,CONCAT(u.Imie,' ',u.Nazwisko) AS Klient,
            CONCAT(eu.Imie,' ',eu.Nazwisko) AS Pracownik";
}

require_once __DIR__.'/admin_pokoje.php';
function ar_create_booking()
{
    global $pdo;
    $room=pokoj_do_rezerwacji($pdo,a_id(pole_tekstowe('pokoj')),true);
    if (!$room) { throw new DomainException('Nie znaleziono pokoju.'); }
    $client=a_record('Uzytkownicy','ID_Uzytkownika',a_id(pole_tekstowe('klient')),true);
    if ($client['Rola']!=='klient' || !$client['Aktywny']) { throw new DomainException('Wybierz aktywne konto klienta.'); }
    $od=pole_tekstowe('od'); $do=pole_tekstowe('do'); $persons=a_id(pole_tekstowe('osoby'));
    $price=ap_check_booking($room,$od,$do,$persons);
    $status=a_enum('status',['Oczekująca','Potwierdzona']);
    a_query('INSERT INTO Rezerwacje(ID_Pokoju,ID_Klienta,DataOd,DataDo,Kwota,Status,Zrodlo_rezerwacji,LiczbaOsob) VALUES(?,?,?,?,?,?,?,?)',[$room['ID_Pokoju'],$client['ID_Uzytkownika'],$od,$do,$price,$status,'Panel administratora',$persons]);
    $id=(int)$pdo->lastInsertId();
    a_log('Dodanie pobytu','rezerwacja',$id,null,['ID_Pokoju'=>$room['ID_Pokoju'],'DataOd'=>$od,'DataDo'=>$do,'Kwota'=>$price,'LiczbaOsob'=>$persons,'Status'=>$status]);
}
function ar_reschedule()
{
    global $pdo;
    $id=a_id(pole_tekstowe('id'));
    $peek=a_record('Rezerwacje','ID_Rezerwacji',$id);
    $rid=a_id(pole_tekstowe('pokoj'));
    $ids=array_unique([(int)$peek['ID_Pokoju'],$rid]); sort($ids,SORT_NUMERIC);
    foreach ($ids as $v) { a_record('Pokoje','ID_Pokoju',$v,true); }
    $old=a_record('Rezerwacje','ID_Rezerwacji',$id,true);
    if (!hash_equals(ar_version($old),pole_tekstowe('wersja'))) { throw new DomainException('Rezerwacja zmieniła się w innej karcie. Odśwież.'); }
    if (!in_array($old['Status'],['Oczekująca','Potwierdzona'],true) || substr($old['DataOd'],0,10)<date('Y-m-d')) { throw new DomainException('Można edytować tylko aktywny pobyt przed rozpoczęciem.'); }
    if (a_query('SELECT ID_Platnosci FROM Platnosci WHERE ID_Rezerwacji=? LIMIT 1 FOR UPDATE',[$id])->fetchColumn()) { throw new DomainException('Pobyt ma zapis płatności. Najpierw rozlicz zmianę poza edytorem; nie zmieniamy automatycznie opłaconego pobytu.'); }
    $room=pokoj_do_rezerwacji($pdo,$rid,true);
    $od=pole_tekstowe('od'); $do=pole_tekstowe('do'); $persons=a_id(pole_tekstowe('osoby'));
    $price=ap_check_booking($room,$od,$do,$persons,$id);
    a_query('UPDATE Rezerwacje SET ID_Pokoju=?,DataOd=?,DataDo=?,LiczbaOsob=?,Kwota=?,Wersja=Wersja+1 WHERE ID_Rezerwacji=?',[$rid,$od,$do,$persons,$price,$id]);
    a_log('Zmiana terminu / pokoju pobytu','rezerwacja',$id,['ID_Pokoju'=>$old['ID_Pokoju'],'DataOd'=>$old['DataOd'],'DataDo'=>$old['DataDo'],'Kwota'=>$old['Kwota'],'LiczbaOsob'=>$old['LiczbaOsob']],['ID_Pokoju'=>$rid,'DataOd'=>$od,'DataDo'=>$do,'Kwota'=>$price,'LiczbaOsob'=>$persons]);
}
function ar_booking_form()
{
    $edit=ctype_digit(a_get('edit')) ? a_query('SELECT * FROM Rezerwacje WHERE ID_Rezerwacji=?',[a_get('edit')])->fetch() : null;
    $rooms=a_query("SELECT ID_Pokoju,CONCAT('Pokój ',NrPokoju,' · ',CenaDobowa,' PLN / noc · ',IloscOsob,' os.',IF(Aktywny=1 AND Status<>'Serwis','',' (niedostępny)')) FROM Pokoje ORDER BY NrPokoju")->fetchAll(PDO::FETCH_KEY_PAIR);
    $clients=a_query("SELECT ID_Uzytkownika,CONCAT('#',ID_Uzytkownika,' · ',Imie,' ',Nazwisko) FROM Uzytkownicy WHERE Rola='klient' AND Aktywny=1 ORDER BY Nazwisko")->fetchAll(PDO::FETCH_KEY_PAIR);
    ?><section><h2><?= $edit ? 'Zmiana terminu / pokoju #'.(int)$edit['ID_Rezerwacji'] : 'Dodaj rezerwację pobytu' ?></h2><p class="hint">Cena jest obliczana ponownie z aktualnej stawki pokoju. Zmiana terminu jest dostępna przed rozpoczęciem, bez zapisanych płatności. Rezerwacje anulujemy zamiast usuwać historię.</p>
    <form method="post" class="form-grid" data-confirm="Zapisać pobyt z ceną wyliczoną z aktualnej stawki pokoju?"><?= csrf_pole() ?>
    <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['ID_Rezerwacji'] ?>"><input type="hidden" name="wersja" value="<?= e(ar_version($edit)) ?>"><?php else: a_select('klient','Klient',$clients); endif;
    a_select('pokoj','Pokój',$rooms,$edit['ID_Pokoju'] ?? '');
    a_input('od','Zameldowanie',substr($edit['DataOd'] ?? date('Y-m-d'),0,10),'date','required');
    a_input('do','Wymeldowanie',substr($edit['DataDo'] ?? date('Y-m-d',strtotime('+1 day')),0,10),'date','required');
    a_input('osoby','Liczba osób',$edit['LiczbaOsob'] ?? 1,'number','required min="1" max="50"');
    if (!$edit) { a_select('status','Status',['Oczekująca'=>'Oczekująca','Potwierdzona'=>'Potwierdzona'],'Potwierdzona'); }
    ?><button name="action" value="<?= $edit ? 'reschedule' : 'create' ?>">Zapisz pobyt</button><a href="rezerwacje.php">Nowa rezerwacja</a></form></section><?php
}
