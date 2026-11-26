<?php
require_once __DIR__.'/auth.php';
function t_q($sql,$args=[])
{
    global $pdo;
    $q=$pdo->prepare($sql); $q->execute($args); return $q;
}
function t_id($raw)
{
    if (!is_scalar($raw) || !preg_match('/^[1-9][0-9]{0,9}$/D',(string)$raw) || (float)$raw>2147483647) { throw new DomainException('Niepoprawny identyfikator.'); }
    return (int)$raw;
}
function t_get($key,$default='')
{
    return isset($_GET[$key]) && is_string($_GET[$key]) ? $_GET[$key] : $default;
}
function t_day($raw)
{
    if (!gah_poprawna_data($raw)) { throw new DomainException('Podaj poprawną datę.'); }
    $d=DateTimeImmutable::createFromFormat('!Y-m-d',$raw);
    if (!$d || $d->format('Y-m-d')!==$raw || $d->format('Y')>'9998') { throw new DomainException('Podaj poprawną datę.'); }
    return $d;
}
function t_tx($callback)
{
    global $pdo;
    $pdo->beginTransaction();
    try { $result=$callback(); $pdo->commit(); return $result; }
    catch (Throwable $ex) { if ($pdo->inTransaction()) { $pdo->rollBack(); } throw $ex; }
}
function t_record($id,$lock=false)
{
    $r=t_q('SELECT * FROM WizytyUslug WHERE ID_Wizyty=?'.($lock?' FOR UPDATE':''),[$id])->fetch();
    if (!$r) { throw new DomainException('Nie znaleziono dostępnej wizyty.'); }
    return $r;
}
function t_own($id,$role=null)
{
    $role=$role??($_SESSION['rola']??'');
    $r=t_record($id);
    if ($role==='klient' && (int)$r['ID_Klienta']===(int)($_SESSION['user_id']??0)) { return $r; }
    if ($role==='pracownik' && t_q('SELECT ID_Pracownika FROM Pracownicy WHERE ID_Pracownika=? AND ID_Uzytkownika=? AND Aktywny=1',[$r['ID_Pracownika'],$_SESSION['user_id']??0])->fetchColumn()) { return $r; }
    if ($role==='admin') { return $r; }
    throw new DomainException('Nie znaleziono dostępnej wizyty.');
}
function t_service($id,$lock=false)
{
    $r=t_q('SELECT s.*,k.Nazwa AS Kategoria FROM Uslugi_dodatkowe s JOIN KategorieUslug k ON k.ID_Kategorii=s.ID_Kategorii WHERE s.ID_Uslugi=?'.($lock?' FOR UPDATE':''),[$id])->fetch();
    if (!$r || !$r['Czydostepna']) { throw new DomainException('Usługa nie jest obecnie dostępna.'); }
    return $r;
}
function t_employees($sid)
{
    return t_q("SELECT p.ID_Pracownika,CONCAT(u.Imie,' ',u.Nazwisko) AS Nazwa FROM Pracownicy p JOIN Uzytkownicy u ON u.ID_Uzytkownika=p.ID_Uzytkownika JOIN Pracownicy_Uslugi x ON x.ID_Pracownika=p.ID_Pracownika WHERE x.ID_Uslugi=? AND p.Aktywny=1 AND u.Aktywny=1 AND u.Rola='pracownik' ORDER BY u.Nazwisko,u.Imie,p.ID_Pracownika",[$sid])->fetchAll();
}
function t_plan($sid,$eid,$raw,$except=null)
{
    global $pdo;
    $lock=$pdo->inTransaction()?' FOR UPDATE':'';
    $emp=t_q('SELECT * FROM Pracownicy WHERE ID_Pracownika=?'.$lock,[$eid])->fetch();
    if (!$emp) { throw new DomainException('Pracownik jest niedostępny.'); }
    $account=t_q('SELECT Rola,Aktywny FROM Uzytkownicy WHERE ID_Uzytkownika=?',[$emp['ID_Uzytkownika']])->fetch();
    if (!$emp['Aktywny'] || !$account || !$account['Aktywny'] || $account['Rola']!=='pracownik') { throw new DomainException('Pracownik jest niedostępny.'); }
    $service=t_service($sid,(bool)$lock);
    if (!t_q('SELECT 1 FROM Pracownicy_Uslugi WHERE ID_Pracownika=? AND ID_Uslugi=?',[$eid,$sid])->fetchColumn()) { throw new DomainException('Pracownik nie wykonuje tej usługi.'); }
    if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}$/D',$raw)) { throw new DomainException('Wybierz poprawny przyszły termin.'); }
    $start=DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',$raw);
    if (!$start || $start->format('Y-m-d\TH:i')!==$raw || $start<new DateTimeImmutable()) { throw new DomainException('Wybierz poprawny przyszły termin.'); }
    t_day($start->format('Y-m-d'));
    $duration=(int)$service['CzasTrwania']; $price=$service['Cena'];
    if ($except!==null) {
        $old=t_record($except,(bool)$lock);
        if ((int)$old['ID_Uslugi']!==$sid || (int)$old['ID_Pracownika']!==$eid) { throw new DomainException('Przełożenie zachowuje usługę i pracownika.'); }
        $duration=(int)round((strtotime($old['DataDo'])-strtotime($old['DataOd']))/60); $price=$old['Cena'];
    }
    if ($duration<1 || $duration>1440) { throw new DomainException('Niepoprawny czas trwania usługi.'); }
    $end=$start->modify('+'.$duration.' minutes');
    if ($end->format('Y-m-d')!==$start->format('Y-m-d')) { throw new DomainException('Wizyta musi mieścić się w jednym dniu pracy.'); }
    $schedule=t_q('SELECT * FROM GrafikPracownikow WHERE ID_Pracownika=? AND Data=?'.$lock,[$eid,$start->format('Y-m-d')])->fetch();
    if (!$schedule || $schedule['TypDnia']!=='Praca' || $start->format('H:i:s')<$schedule['GodzinaOd'] || $end->format('H:i:s')>$schedule['GodzinaDo']) { throw new DomainException('Termin jest poza godzinami pracy albo przypada w dniu wolnym lub urlopie.'); }
    $sql="SELECT ID_Wizyty FROM WizytyUslug WHERE ID_Pracownika=? AND Status IN ('Oczekująca','Potwierdzona') AND DataOd<? AND DataDo>?";
    $args=[$eid,$end->format('Y-m-d H:i:s'),$start->format('Y-m-d H:i:s')];
    if ($except!==null) { $sql.=' AND ID_Wizyty<>?'; $args[]=$except; }
    if (t_q($sql.' LIMIT 1'.$lock,$args)->fetchColumn()) { throw new DomainException('Termin został zajęty. Wybierz inną godzinę.'); }
    return ['service'=>$service,'employee'=>$emp,'from'=>$start->format('Y-m-d H:i:s'),'to'=>$end->format('Y-m-d H:i:s'),'price'=>$price,'duration'=>$duration];
}
function t_slots($sid,$eid,$day,$except=null)
{
    $d=t_day($day);
    if ($d<new DateTimeImmutable('today')) { throw new DomainException('Nie można rezerwować w przeszłości.'); }
    $service=t_service($sid);
    if (!in_array($eid,array_map('intval',array_column(t_employees($sid),'ID_Pracownika')),true)) { throw new DomainException('Pracownik nie wykonuje tej usługi lub jest niedostępny.'); }
    $schedule=t_q('SELECT * FROM GrafikPracownikow WHERE ID_Pracownika=? AND Data=?',[$eid,$day])->fetch();
    if (!$schedule || $schedule['TypDnia']!=='Praca') { return []; }
    $duration=(int)$service['CzasTrwania'];
    if ($except!==null) {
        $old=t_record($except);
        if ((int)$old['ID_Uslugi']!==$sid || (int)$old['ID_Pracownika']!==$eid) { throw new DomainException('Przełożenie zachowuje usługę i pracownika.'); }
        $duration=(int)round((strtotime($old['DataDo'])-strtotime($old['DataOd']))/60);
    }
    if ($duration<1 || $duration>1440) { throw new DomainException('Niepoprawny czas trwania usługi.'); }
    $busy=t_q("SELECT DataOd,DataDo FROM WizytyUslug WHERE ID_Pracownika=? AND Status IN ('Oczekująca','Potwierdzona') AND DataOd<? AND DataDo>? AND ID_Wizyty<>?",[$eid,$day.' 23:59:59',$day.' 00:00:00',$except??0])->fetchAll();
    $from=new DateTimeImmutable($day.' '.$schedule['GodzinaOd']);$end=new DateTimeImmutable($day.' '.$schedule['GodzinaDo']);$now=new DateTimeImmutable();$slots=[];
    for ($at=$from;$at<$end;$at=$at->modify('+15 minutes')) {
        $to=$at->modify('+'.$duration.' minutes');
        if ($at<$now || $to>$end || $to->format('Y-m-d')!==$day) { continue; }
        $collision=false;
        foreach ($busy as $b) { if (gah_nakladanie($b['DataOd'],$b['DataDo'],$at->format('Y-m-d H:i:s'),$to->format('Y-m-d H:i:s'))) { $collision=true;break; } }
        if (!$collision) { $slots[]=['value'=>$at->format('H:i'),'end'=>$to->format('H:i')]; }
    }
    return $slots;
}

function t_history($id,$type,$actor=null)
{
    $r=t_record($id);
    t_q('INSERT INTO HistoriaWizyt(ID_Wizyty,Wersja,Typ,Status,DataOd,DataDo,ID_Autora) VALUES(?,?,?,?,?,?,?)',[$id,$r['Wersja'],$type,$r['Status'],$r['DataOd'],$r['DataDo'],$actor??($_SESSION['user_id']??null)]);
}
function t_create($cid,$sid,$eid,$raw,$notes='',$expected=null)
{
    global $pdo;
    $plan=t_plan($sid,$eid,$raw);
    $client=t_q('SELECT Rola,Aktywny FROM Uzytkownicy WHERE ID_Uzytkownika=?',[$cid])->fetch();
    if (!$client || !$client['Aktywny'] || $client['Rola']!=='klient') { throw new DomainException('Konto klienta jest niedostępne.'); }
    if (mb_strlen($notes)>1000) { throw new DomainException('Uwagi mogą mieć najwyżej 1000 znaków.'); }
    if ($expected && ((string)$expected['price']!==(string)$plan['price'] || (int)$expected['duration']!==$plan['duration'])) { throw new DomainException('Oferta zmieniła się. Ponownie sprawdź podsumowanie i potwierdź.'); }
    t_q('INSERT INTO WizytyUslug(ID_Klienta,ID_Pracownika,ID_Uslugi,DataOd,DataDo,Cena,Uwagi) VALUES(?,?,?,?,?,?,?)',[$cid,$eid,$sid,$plan['from'],$plan['to'],$plan['price'],$notes]);
    $id=(int)$pdo->lastInsertId(); t_history($id,'Utworzenie'); return $id;
}
function t_change($id,$version,$action,$status=null,$raw=null)
{
    $peek=t_own($id);
    t_q('SELECT ID_Pracownika FROM Pracownicy WHERE ID_Pracownika=? FOR UPDATE',[$peek['ID_Pracownika']])->fetch();
    $old=t_record($id,true);
    if ((string)$old['Wersja']!==(string)$version) { throw new DomainException('Wizyta zmieniła się w innej karcie. Odśwież stronę.'); }
    t_own($id);
    $role=$_SESSION['rola']; $future=new DateTimeImmutable($old['DataOd'])>new DateTimeImmutable();
    if ($role==='klient') {
        if (!$future || !in_array($old['Status'],['Oczekująca','Potwierdzona'],true)) { throw new DomainException('Możesz zmienić wyłącznie własną przyszłą aktywną wizytę.'); }
        if (!in_array($action,['cancel','move'],true)) { throw new DomainException('Niedozwolona operacja.'); }
        $status='Anulowana';
    }
    if ($action==='move') {
        if ($role!=='klient') { throw new DomainException('Przełożenie wykonuje właściciel wizyty.'); }
        $p=t_plan((int)$old['ID_Uslugi'],(int)$old['ID_Pracownika'],$raw,$id);
        t_q('UPDATE WizytyUslug SET DataOd=?,DataDo=?,Wersja=Wersja+1 WHERE ID_Wizyty=?',[$p['from'],$p['to'],$id]);
        t_history($id,'Przełożenie'); return $id;
    }
    $map=['Oczekująca'=>['Potwierdzona','Anulowana'],'Potwierdzona'=>['Ukończone','Anulowana'],'Ukończone'=>[],'Anulowana'=>[]];
    if ($status===$old['Status']) { return $id; }
    if (!in_array($status,$map[$old['Status']]??[],true)) { throw new DomainException('Niedozwolone przejście statusu.'); }
    if ($status==='Ukończone' && new DateTimeImmutable($old['DataDo'])>new DateTimeImmutable()) { throw new DomainException('Nie można ukończyć przyszłej wizyty.'); }
    t_q('UPDATE WizytyUslug SET Status=?,Wersja=Wersja+1 WHERE ID_Wizyty=?',[$status,$id]);t_history($id,'Status');return $id;
}
function t_post($callback,$back)
{
    if ($_SERVER['REQUEST_METHOD']!=='POST') { return; }
    csrf_sprawdz();
    try { t_tx($callback); ustaw_komunikat('success','Operacja została zapisana.'); }
    catch (DomainException $ex) { ustaw_komunikat('error',$ex->getMessage()); }
    catch (Throwable $ex) { error_log('Terminarz: '.$ex->getMessage()); ustaw_komunikat('error','Nie udało się zapisać operacji. Odśwież dostępność i spróbuj ponownie.'); }
    przekieruj($back);
}
function t_join()
{
    return " FROM WizytyUslug w JOIN Uslugi_dodatkowe s ON s.ID_Uslugi=w.ID_Uslugi JOIN Pracownicy p ON p.ID_Pracownika=w.ID_Pracownika JOIN Uzytkownicy u ON u.ID_Uzytkownika=p.ID_Uzytkownika JOIN Uzytkownicy k ON k.ID_Uzytkownika=w.ID_Klienta";
}
function t_select()
{
    return "SELECT w.*,s.Nazwa_Uslugi,CONCAT(u.Imie,' ',u.Nazwisko) AS Pracownik,CONCAT(k.Imie,' ',k.Nazwisko) AS Klient,k.Telefon";
}
