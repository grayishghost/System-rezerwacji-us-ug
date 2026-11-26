<?php
if (PHP_SAPI!=='cli') { http_response_code(404);exit; }
require_once __DIR__.'/../includes/reguly.php';
$tests=[];
function check_unit($name,$ok)
{
    global $tests;
    $tests[]=['test'=>$name,'ok'=>(bool)$ok];
    echo ($ok?'PASS ':'FAIL ').$name.PHP_EOL;
}
foreach (['2028-02-29','2026-11-27','1000-01-01','9998-12-31'] as $day) { check_unit('Poprawna data '.$day,gah_poprawna_data($day)); }
foreach (['2026-02-29','2026-13-01','2026-11-31','2026-1-01','2026-11-27x',"2026-11-27\0",'0000-01-01','0999-01-01','9999-01-01','',null,[]] as $n=>$day) { check_unit('Bledna data '.$n,!gah_poprawna_data($day)); }
foreach ([['09:30','10:30',true],['10:00','11:00',true],['10:15','10:45',true],['10:30','11:30',true],['09:30','11:30',true],['09:00','10:00',false],['11:00','12:00',false]] as [$from,$to,$expected]) { check_unit('Przedzial '.$from.'-'.$to,gah_nakladanie('10:00','11:00',$from,$to)===$expected); }
$ok=!in_array(false,array_column($tests,'ok'),true);
echo 'WYNIK '.count(array_filter(array_column($tests,'ok'))).'/'.count($tests).PHP_EOL;
exit($ok?0:1);
