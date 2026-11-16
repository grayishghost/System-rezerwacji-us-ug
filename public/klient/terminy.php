<?php
require_once __DIR__.'/../../includes/terminarz_widok.php';
wymagaj_roli('klient');t_ready();
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
try {
    $sid=t_id(t_get('usluga'));$eid=t_id(t_get('pracownik'));$except=null;
    if (t_get('przeloz')!=='' && t_get('przeloz')!=='0') {
        $old=t_own(t_id(t_get('przeloz')));$except=(int)$old['ID_Wizyty'];
        if ($sid!==(int)$old['ID_Uslugi'] || $eid!==(int)$old['ID_Pracownika']) { throw new DomainException('Przełożenie zachowuje usługę i pracownika.'); }
    }
    echo json_encode(['slots'=>t_slots($sid,$eid,t_get('data'),$except)],JSON_UNESCAPED_UNICODE);
} catch (DomainException $ex) { http_response_code(422);echo json_encode(['error'=>$ex->getMessage()],JSON_UNESCAPED_UNICODE); }
catch (Throwable $ex) { error_log('Terminy: '.$ex->getMessage());http_response_code(503);echo json_encode(['error'=>'Nie udało się pobrać godzin. Spróbuj ponownie.']); }
