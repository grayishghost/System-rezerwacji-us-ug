<?php
require_once __DIR__.'/../includes/terminarz_widok.php';
require_once __DIR__.'/../includes/kalendarz_ics.php';
wymagaj_roli('klient','pracownik');t_ready();
try {
    $id=t_id(t_get('id'));t_own($id);
    $visit=t_q(t_select().t_join().' WHERE w.ID_Wizyty=?',[$id])->fetch();
    $content=gah_visit_ics($visit);
} catch (DomainException $ex) {
    http_response_code(404);t_start('Wizyta niedostępna');t_error('Nie znaleziono dostępnej wizyty.');t_end();exit;
}
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="wizyta-'.$id.'.ics"');
header('Cache-Control: no-store');
echo $content;
