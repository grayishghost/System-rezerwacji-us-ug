<?php
if (PHP_SAPI!=='cli') { http_response_code(404);exit; }
require_once __DIR__.'/../includes/instalacja.php';
$root=sys_get_temp_dir().'/gah-manifest-'.bin2hex(random_bytes(10));
mkdir($root);mkdir($root.'/includes');
$checks=[];
function manifest_expect($name,$ok)
{
    global $checks;
    $checks[]=(bool)$ok;echo ($ok?'PASS ':'FAIL ').$name.PHP_EOL;
}
function manifest_write($value)
{
    global $root;
    file_put_contents($root.'/includes/wydanie.json',is_string($value)?$value:json_encode($value,JSON_UNESCAPED_UNICODE));
}
function manifest_make($files)
{
    ksort($files,SORT_STRING);$canonical='';
    foreach ($files as $p=>$h) { $canonical.=$p."\0".$h."\n"; }
    return ['id'=>'GAH-TEST','hash'=>hash('sha256',$canonical),'files'=>$files];
}
try {
    manifest_expect('Brak manifestu wykrywany',gah_manifest_check($root)['changed']!==[]);
    foreach (['{','null','[]','{"id":7,"hash":[],"files":"x"}'] as $n=>$value) { manifest_write($value);manifest_expect('Uszkodzony JSON lub typ '.$n,gah_manifest_check($root)['changed']!==[]); }
    file_put_contents($root.'/plik.txt','ABC');$good=manifest_make(['plik.txt'=>hash('sha256','ABC')]);manifest_write($good);
    manifest_expect('Poprawny manifest i plik',gah_manifest_check($root)['changed']===[] && gah_manifest_check($root)['checked']===1);
    file_put_contents($root.'/plik.txt','ABD');manifest_expect('Zmiana pliku wykryta',gah_manifest_check($root)['changed']===['plik.txt']);
    unlink($root.'/plik.txt');manifest_expect('Brak pliku wykryty',gah_manifest_check($root)['changed']===['plik.txt']);file_put_contents($root.'/plik.txt','ABC');
    $bad=$good;$bad['hash']=str_repeat('0',64);manifest_write($bad);manifest_expect('Zly hash zbiorczy wykryty',gah_manifest_check($root)['changed']!==[]);
    $bad=$good;$bad['files']['plik.txt']=hash('sha256','ABD');manifest_write($bad);manifest_expect('Zmiana listy bez przeliczenia wykryta',gah_manifest_check($root)['changed']!==[]);
    foreach (['../plik.txt','/plik.txt','includes/config.php','includes/wydanie.json',"a\0b","a\\b"] as $path) { manifest_write(manifest_make([$path=>hash('sha256','ABC')]));manifest_expect('Niedozwolona sciezka '.json_encode($path),gah_manifest_check($root)['changed']!==[]); }
    manifest_write(manifest_make([]));manifest_expect('Pusty manifest odrzucony',gah_manifest_check($root)['changed']!==[]);
    file_put_contents($root.'/z.txt','Z');$good=manifest_make(['z.txt'=>hash('sha256','Z'),'plik.txt'=>hash('sha256','ABC')]);$good['files']=array_reverse($good['files'],true);manifest_write($good);
    manifest_expect('Kolejnosc kluczy nie zmienia zbiorczego hasha',gah_manifest_check($root)['changed']===[]);
} finally {
    foreach (['includes/wydanie.json','plik.txt','z.txt'] as $path) { if (is_file($root.'/'.$path)) { unlink($root.'/'.$path); } }
    rmdir($root.'/includes');rmdir($root);
}
echo 'WYNIK '.count(array_filter($checks)).'/'.count($checks).PHP_EOL;
exit(in_array(false,$checks,true)?1:0);
