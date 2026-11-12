<?php
require_once __DIR__.'/admin.php';
function ak_employee_history($eid)
{
    return a_query('SELECT ID_Rezerwacji FROM Rezerwacje WHERE ID_Pracownika=? LIMIT 1',[$eid])->fetchColumn() || a_query('SELECT ID_Wizyty FROM WizytyUslug WHERE ID_Pracownika=? LIMIT 1',[$eid])->fetchColumn();
}
function ak_public($r)
{
    return ['Rola'=>$r['Rola'],'Aktywny'=>$r['Aktywny'],'Wersja'=>$r['Wersja']];
}
function ak_save()
{
    global $pdo;
    $action=a_enum('action',['','save','delete']);
    a_query("SELECT ID_Uzytkownika FROM Uzytkownicy WHERE Rola='admin' ORDER BY ID_Uzytkownika FOR UPDATE")->fetchAll();
    $raw=pole_tekstowe('id'); $id=$raw==='0'?0:a_id($raw);
    $peek=$id?a_query('SELECT * FROM Pracownicy WHERE ID_Uzytkownika=?',[$id])->fetch():null;
    $emp=$peek?a_record('Pracownicy','ID_Pracownika',$peek['ID_Pracownika'],true):null;
    $old=$id?a_record('Uzytkownicy','ID_Uzytkownika',$id,true):null;
    if ($old && $action!=='') { a_version($old); }
    if ($action==='delete') {
        if (!$old || $id===(int)$_SESSION['user_id']) { throw new DomainException('Nie można usunąć własnego konta.'); }
        ak_admin_guard($old,null,'0');
        if (a_query('SELECT ID_Rezerwacji FROM Rezerwacje WHERE ID_Klienta=? LIMIT 1',[$id])->fetchColumn() || a_query('SELECT ID_Wizyty FROM WizytyUslug WHERE ID_Klienta=? LIMIT 1',[$id])->fetchColumn() || ($emp && ak_employee_history($emp['ID_Pracownika']))) {
            throw new DomainException('Konto ma historię pobytów lub wizyt. Zablokuj konto zamiast usuwać historię.');
        }
        if ($emp) { a_query('DELETE FROM Pracownicy WHERE ID_Pracownika=?',[$emp['ID_Pracownika']]); }
        a_query('DELETE FROM Uzytkownicy WHERE ID_Uzytkownika=?',[$id]);
        a_log('Usunięcie konta','konto',$id,ak_public($old),null);
        return;
    }
    $role=a_enum('rola',['klient','pracownik','admin']); $active=a_enum('aktywny',['0','1']);
    if ($old) { ak_admin_guard($old,$role,$active); }
    if ($action==='') {
        if (!$old) { throw new DomainException('Wybierz konto.'); }
        if ($emp && $role!=='pracownik') { throw new DomainException('Konto ma profil pracownika. Zmień rolę w pełnym formularzu konta.'); }
        if (!$emp && $role==='pracownik') { throw new DomainException('W pełnym formularzu zapisz konto jako pracownika. Profil zostanie utworzony automatycznie.'); }
        a_query('UPDATE Uzytkownicy SET Rola=?,Aktywny=?,Wersja=Wersja+1 WHERE ID_Uzytkownika=?',[$role,$active,$id]);
        a_log('Konto: rola / blokada','konto',$id,ak_public($old),['Rola'=>$role,'Aktywny'=>$active]);
        return;
    }
    $name=a_text('imie',50); $surname=a_text('nazwisko',50); $phone=a_text('telefon',20); $email=mb_strtolower(a_text('email',100));
    $errors=sprawdz_dane_osobowe($name,$surname,$phone);
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) { $errors[]='Podaj poprawny e-mail.'; }
    $password=pole_tekstowe('haslo');
    if (!$old || $password!=='') { if ($err=sprawdz_haslo($password)) { $errors[]=$err; } }
    if ($errors) { throw new DomainException(implode(' ',$errors)); }
    if (a_query('SELECT ID_Uzytkownika FROM Uzytkownicy WHERE Email=? AND ID_Uzytkownika<>? LIMIT 1',[$email,$id])->fetchColumn()) { throw new DomainException('Adres e-mail jest już zajęty.'); }
    if ($emp && $role!=='pracownik') {
        if (ak_employee_history($emp['ID_Pracownika'])) { throw new DomainException('Pracownik ma historię. Zachowaj rolę pracownik i zablokuj konto lub dezaktywuj profil.'); }
        a_query('DELETE FROM Pracownicy WHERE ID_Pracownika=?',[$emp['ID_Pracownika']]);
        a_log('Usunięcie pustego profilu przy zmianie roli','pracownik',$emp['ID_Pracownika'],['ID_Uzytkownika'=>$id],null);
    }
    if ($old) {
        a_query('UPDATE Uzytkownicy SET Imie=?,Nazwisko=?,Email=?,Telefon=?,Rola=?,Aktywny=?,Wersja=Wersja+1 WHERE ID_Uzytkownika=?',[$name,$surname,$email,$phone,$role,$active,$id]);
        if ($password!=='') {
            a_query('UPDATE Uzytkownicy SET Haslo=?,SesjaWersja=SesjaWersja+1 WHERE ID_Uzytkownika=?',[password_hash($password,PASSWORD_DEFAULT),$id]);
            if ($id===(int)$_SESSION['user_id']) { $_SESSION['wersja_sesji']=(int)$old['SesjaWersja']+1; session_regenerate_id(true); }
        }
    } else {
        a_query('INSERT INTO Uzytkownicy(Imie,Nazwisko,Email,Telefon,Haslo,Rola,Aktywny) VALUES(?,?,?,?,?,?,?)',[$name,$surname,$email,$phone,password_hash($password,PASSWORD_DEFAULT),$role,$active]);
        $id=(int)$pdo->lastInsertId();
    }
    if ($role==='pracownik' && !$emp) {
        a_query('INSERT INTO Pracownicy(ID_Uzytkownika) VALUES(?)',[$id]);
        a_log('Automatyczny profil konta pracownika','pracownik',(int)$pdo->lastInsertId(),null,['ID_Uzytkownika'=>$id]);
    }
    a_log($old?'Edycja konta':'Dodanie konta','konto',$id,$old?ak_public($old):null,['Rola'=>$role,'Aktywny'=>$active,'ZmienionoHaslo'=>$password!=='']);
}
function ak_admin_guard($old,$role,$active)
{
    if ((int)$old['ID_Uzytkownika']===(int)$_SESSION['user_id'] && ($role!=='admin' || $active!=='1')) { throw new DomainException('Nie możesz usunąć, zablokować ani odebrać uprawnień własnemu kontu.'); }
    if ($old['Rola']==='admin' && $old['Aktywny'] && ($role!=='admin' || $active!=='1')) {
        if ((int)a_query("SELECT COUNT(*) FROM Uzytkownicy WHERE Rola='admin' AND Aktywny=1")->fetchColumn()<=1) { throw new DomainException('Musi pozostać co najmniej jeden aktywny administrator.'); }
    }
}
