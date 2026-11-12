<?php
require_once __DIR__.'/admin.php';
function au_photo()
{
    global $a_created_files;
    if (!isset($_FILES['ilustracja'])) { return null; }
    $f=$_FILES['ilustracja'];
    if (!is_array($f) || !isset($f['error']) || is_array($f['error'])) { throw new DomainException('Niepoprawny plik ilustracji.'); }
    if ((int)$f['error']===UPLOAD_ERR_NO_FILE) { return null; }
    if ((int)$f['error']!==UPLOAD_ERR_OK) { throw new DomainException('Upload nie powiódł się. Limit aplikacji to 5 MB; obowiązuje też limit plików ustawiony w PHP hostingu.'); }
    if (!extension_loaded('gd') || !extension_loaded('fileinfo')) { throw new DomainException('Przesyłanie zdjęć wymaga rozszerzeń PHP GD i fileinfo. Wybierz istniejącą ilustrację lub włącz te rozszerzenia na hostingu.'); }
    $tmp=$f['tmp_name'];
    if (!is_string($tmp) || !is_uploaded_file($tmp) || filesize($tmp)>5*1024*1024 || filesize($tmp)<1) { throw new DomainException('Plik musi mieć od 1 bajtu do 5 MB.'); }
    $info=@getimagesize($tmp); $mime=(new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    if (!$info || !in_array($mime,['image/jpeg','image/png','image/webp'],true) || $info['mime']!==$mime || $info[0]<1 || $info[1]<1 || $info[0]>6000 || $info[1]>6000 || $info[0]*$info[1]>8000000) {
        throw new DomainException('Dozwolone są poprawne JPG, PNG i WEBP do 6000 px na bok i 8 mln pikseli. SVG, HTML i pliki wykonywalne są niedozwolone.');
    }
    $source=@imagecreatefromstring(file_get_contents($tmp));
    if (!$source) { throw new DomainException('Nie udało się odczytać obrazu.'); }
    $scale=min(1,1600/max($info[0],$info[1])); $w=max(1,(int)round($info[0]*$scale)); $h=max(1,(int)round($info[1]*$scale));
    $canvas=imagecreatetruecolor($w,$h); imagefill($canvas,0,0,imagecolorallocate($canvas,251,250,246));
    imagecopyresampled($canvas,$source,0,0,0,0,$w,$h,$info[0],$info[1]);
    $dir=dirname(__DIR__).'/assets/pokoje';
    if (!is_dir($dir) || !is_writable($dir)) { imagedestroy($source); imagedestroy($canvas); throw new DomainException('Katalog assets/pokoje musi istnieć i być zapisywalny dla PHP. Nie ustawiaj uprawnień 777.'); }
    $relative='assets/pokoje/'.bin2hex(random_bytes(24)).'.jpg'; $file=dirname(__DIR__).'/'.$relative;
    $a_created_files[]=$file;
    $ok=imagejpeg($canvas,$file,88); imagedestroy($canvas); imagedestroy($source);
    if (!$ok) { throw new DomainException('Nie udało się zapisać ilustracji.'); }
    chmod($file,0644);
    return $relative;
}
