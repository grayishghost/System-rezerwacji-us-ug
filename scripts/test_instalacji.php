<?php
if (PHP_SAPI!=='cli') { http_response_code(404);exit; }
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/instalacja.php';
try {
    $state=gah_installation($pdo);$release=gah_release_check();
    $issues=array_values(array_map(fn($row)=>$row['element'],array_filter($state['checks'],fn($row)=>!$row['ok'])));
    $data=$state['ready']?gah_integrity_data($pdo):[];
    $bad=array_values(array_filter($data,fn($row)=>(int)$row['Niepoprawne']!==0));
    $ok=$state['ready'] && !$release['changed'] && !$bad;
    echo json_encode(['ok'=>$ok,'wydanie'=>$release['id'],'manifest'=>$release['hash'],'sprawdzone_pliki'=>$release['checked'],'braki_schematu'=>$issues,'niezgodne_pliki'=>$release['changed'],'sprawdzone_reguly_danych'=>count($data),'naruszenia'=>$bad],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT).PHP_EOL;
    exit($ok?0:1);
} catch (Throwable $ex) { fwrite(STDERR,"Nie udalo sie sprawdzic instalacji. Sprawdz prywatny log i konfiguracje.\n");exit(1); }
