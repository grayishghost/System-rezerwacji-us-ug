<?php
if (PHP_SAPI!=='cli') { http_response_code(404);exit; }
require_once __DIR__.'/../includes/kalendarz_ics.php';
$tests=[];
function check_unit($name,$ok)
{
    global $tests;
    $tests[]=['test'=>$name,'ok'=>(bool)$ok];
    echo ($ok?'PASS ':'FAIL ').$name.PHP_EOL;
}
check_unit('Zima Warszawa do UTC',gah_ics_utc('2026-11-27 10:00:00')==='20261127T090000Z');
check_unit('Lato Warszawa do UTC',gah_ics_utc('2026-07-27 10:00:00')==='20260727T080000Z');
check_unit('Escaping ICS',gah_ics_text("A,B;C\\D\r\nE")==='A\\,B\\;C\\\\D\\nE');
$long=str_repeat('Zażółć gęślą jaźń,',18);$fold=gah_ics_fold('SUMMARY:'.gah_ics_text($long));
check_unit('Folding zachowuje tresc',str_replace("\r\n ",'',$fold)==='SUMMARY:'.gah_ics_text($long));
check_unit('Kazda linia najwyzej 75 bajtow',max(array_map('strlen',explode("\r\n",$fold)))<=75);
check_unit('Folding nie rozcina UTF-8',mb_check_encoding($fold,'UTF-8'));
$visit=['ID_Wizyty'=>7,'Wersja'=>2,'Status'=>'Potwierdzona','DataOd'=>'2026-11-27 10:00:00','DataDo'=>'2026-11-27 11:00:00','Nazwa_Uslugi'=>"Relaks\r\nBEGIN:VALARM",'Pracownik'=>'Anna Testowa','Uwagi'=>'TAJNE_UWAGI','Telefon'=>'TAJNY_TELEFON','Email'=>'TAJNY_EMAIL'];
$ics=gah_visit_ics($visit);
check_unit('Jeden VEVENT',substr_count($ics,"\r\nBEGIN:VEVENT\r\n")===1);
check_unit('Brak wstrzyknietego komponentu',!str_contains($ics,"\r\nBEGIN:VALARM"));
check_unit('Brak prywatnych danych w eksporcie',!str_contains($ics,'TAJNE_') && !str_contains($ics,'TAJNY_'));
check_unit('Sekwencja wersji',str_contains($ics,"\r\nSEQUENCE:2\r\n"));
check_unit('Stabilny UID',str_contains($ics,'UID:gah-wizyta-7@grand-access-hotel.invalid'));
check_unit('Plik zakonczony CRLF',str_ends_with($ics,"END:VCALENDAR\r\n"));
foreach (['Oczekująca'=>'TENTATIVE','Potwierdzona'=>'CONFIRMED','Ukończone'=>'CONFIRMED','Anulowana'=>'CANCELLED'] as $status=>$expected) { $visit['Status']=$status;check_unit('Status '.$status,str_contains(gah_visit_ics($visit),'STATUS:'.$expected)); }
$ok=!in_array(false,array_column($tests,'ok'),true);
echo 'WYNIK '.count(array_filter(array_column($tests,'ok'))).'/'.count($tests).PHP_EOL;
exit($ok?0:1);
