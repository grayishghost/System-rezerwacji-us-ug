<?php
function gah_installation(PDO $pdo): array
{
    $tables=$pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $c=$pdo->query('SELECT TABLE_NAME,COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE()')->fetchAll(PDO::FETCH_NUM);
    $columns=[];
    foreach ($c as $v) { $columns[$v[0]][]=$v[1]; }
    $markers=in_array('Migracje',$tables,true) ? $pdo->query('SELECT Nazwa FROM Migracje ORDER BY Nazwa')->fetchAll(PDO::FETCH_COLUMN) : [];
    $checks=[['element'=>'PHP co najmniej 8.1','ok'=>version_compare(PHP_VERSION,'8.1.0','>=')],['element'=>'pdo_mysql','ok'=>extension_loaded('pdo_mysql')],['element'=>'mbstring','ok'=>extension_loaded('mbstring')]];
    foreach (['Uzytkownicy','Stanowiska','Pracownicy','TypyPokoi','Pokoje','Uslugi_dodatkowe','Pracownicy_Uslugi','Rezerwacje','Platnosci','ZamowieniaUslugi','GrafikPracownikow','Proby_logowania','KategorieUslug','LogAdministratora','Migracje','WizytyUslug','BlokadyPokoi'] as $t) {
        $checks[]=['element'=>'Tabela '.$t,'ok'=>in_array($t,$tables,true)];
    }
    foreach (['003_panel','004_uzupelnienia','005_pokoje','006_integralnosc'] as $m) { $checks[]=['element'=>'Marker '.$m,'ok'=>in_array($m,$markers,true)]; }
    foreach (['Pracownicy'=>['Aktywny','Opis'],'Uslugi_dodatkowe'=>['ID_Kategorii','CzasTrwania'],'Pokoje'=>['Aktywny','Wersja'],'Rezerwacje'=>['LiczbaOsob','Wersja']] as $t=>$fields) {
        foreach ($fields as $f) { $checks[]=['element'=>$t.'.'.$f,'ok'=>in_array($f,$columns[$t] ?? [],true)]; }
    }
    $checks[]=['element'=>'Usunięcie starej kopii tekstowej kategorii','ok'=>!in_array('Kategoria',$columns['Uslugi_dodatkowe'] ?? [],true)];
    $q=$pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='WizytyUslug' AND REFERENCED_TABLE_NAME IS NOT NULL");
    $checks[]=['element'=>'Trzy FK wizyty usługi','ok'=>(int)$q->fetchColumn()===3];
    $q=$pdo->query("SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND CONSTRAINT_TYPE='CHECK' AND CONSTRAINT_NAME IN ('chk_usluga_cena','chk_usluga_czas','chk_wizyta_przedzial','chk_wizyta_cena','chk_pokoj_cena','chk_pokoj_osoby','chk_pobyt_kwota','chk_platnosc_kwota','chk_zamowienie_ilosc','chk_zamowienie_cena','chk_grafik_godziny','chk_pobyt_osoby','chk_blokada_przedzial')");
    $checks[]=['element'=>'Ograniczenia integralności 003–005','ok'=>(int)$q->fetchColumn()===13];
    $q=$pdo->query("SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND CONSTRAINT_TYPE='CHECK' AND CONSTRAINT_NAME LIKE 'chk_i006_%'");
    $checks[]=['element'=>'23 dodatkowe CHECK migracji 006','ok'=>(int)$q->fetchColumn()===23];
    $q=$pdo->query("SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND CONSTRAINT_NAME IN ('fk_i006_platnosc_pobyt','fk_i006_zamowienie_pobyt') AND DELETE_RULE='RESTRICT'");
    $checks[]=['element'=>'Ochrona płatności i zamówień przed kaskadowym usunięciem','ok'=>(int)$q->fetchColumn()===2];
    $q=$pdo->query("SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Rezerwacje' AND INDEX_NAME='idx_pobyt_pokoj_status_daty'");
    $checks[]=['element'=>'Indeks pokoju, statusu i dat pobytu','ok'=>(int)$q->fetchColumn()===4];
    $ready=!in_array(false,array_column($checks,'ok'),true);
    return ['ready'=>$ready,'checks'=>$checks,'markers'=>$markers,'php'=>PHP_VERSION,'db'=>$pdo->getAttribute(PDO::ATTR_SERVER_VERSION)];
}
function gah_release_check(): array
{
    $file=__DIR__.'/wydanie.json';
    if (!is_file($file)) { return ['id'=>'Brak manifestu','changed'=>['includes/wydanie.json'],'checked'=>0,'hash'=>'']; }
    $m=json_decode(file_get_contents($file),true);
    if (!is_array($m) || !isset($m['files'],$m['id'],$m['hash'])) { return ['id'=>'Nieprawidłowy manifest','changed'=>['includes/wydanie.json'],'checked'=>0,'hash'=>'']; }
    $root=dirname(__DIR__); $changed=[];
    foreach ($m['files'] as $path=>$hash) {
        if (str_contains($path,'..') || str_starts_with($path,'/') || $path==='includes/config.php') { $changed[]='Niedozwolony wpis manifestu'; continue; }
        $p=$root.'/'.$path;
        if (!is_file($p) || hash_file('sha256',$p)!==$hash) { $changed[]=$path; }
    }
    return ['id'=>$m['id'],'hash'=>$m['hash'],'checked'=>count($m['files']),'changed'=>$changed];
}

function gah_integrity_data(PDO $pdo): array
{
    $rows=[];
    $sql=file_get_contents(__DIR__.'/../database/PRZED_006_ODCZYT.sql');
    foreach (preg_split('/;\s*(?:\n|$)/',trim($sql)) as $statement) {
        if (trim($statement)!=='') { $rows[]=$pdo->query($statement)->fetch(PDO::FETCH_ASSOC); }
    }
    return $rows;
}
