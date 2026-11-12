<?php
require_once __DIR__.'/../../includes/admin.php';
if ($_SERVER['REQUEST_METHOD']==='GET') {
    $q=$_GET; $q['sekcja']='kompetencje';
    przekieruj('/public/admin/pracownicy.php?'.http_build_query($q));
}
require __DIR__.'/../../includes/admin_kompetencje_view.php';
