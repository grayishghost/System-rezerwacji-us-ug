<?php
if (PHP_SAPI!=='cli') { http_response_code(404);exit; }
require __DIR__.'/../includes/db.php';
require __DIR__.'/../includes/instalacja.php';
$s=gah_installation($pdo); $r=gah_release_check();
foreach ($s['checks'] as $c) { echo ($c['ok']?'OK ':'BRAK ').$c['element'].PHP_EOL; }
echo 'Wydanie: '.$r['id'].PHP_EOL.'Odcisk: '.$r['hash'].PHP_EOL;
foreach ($r['changed'] as $p) { echo 'Niezgodny plik: '.$p.PHP_EOL; }
exit($s['ready'] && !$r['changed'] ? 0 : 1);
