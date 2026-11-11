<?php
require_once __DIR__ . '/admin.php';
require_once __DIR__ . '/rezerwacje.php';
function ap_dates($od, $do, $future = false)
{
    a_date($od);
    a_date($do);
    $days = (int)(new DateTimeImmutable($od))->diff(new DateTimeImmutable($do))->format('%r%a');
    if ($days < 1 || $days > 366) {
        throw new DomainException('Zakres musi obejmować od 1 do 366 nocy.');
    }
    if ($future && $od < date('Y-m-d')) {
        throw new DomainException('Początek nie może być w przeszłości.');
    }
}
function ap_check_booking($room, $od, $do, $persons, $except = null)
{
    global $pdo;
    ap_dates($od, $do, true);
    if (!$room['Aktywny'] || $room['Status'] === 'Serwis') {
        throw new DomainException('Pokój nie jest udostępniony do nowych rezerwacji.');
    }
    if ($persons < 1 || $persons > (int)$room['IloscOsob']) {
        throw new DomainException('Liczba osób przekracza pojemność pokoju.');
    }
    if (pokoj_ma_kolizje($pdo, $room['ID_Pokoju'], $od, $do, $except)) {
        throw new DomainException('Pokój jest zajęty lub zablokowany w wybranym terminie.');
    }
    $price = kwota_pobytu($room, $od, $do);
    if ($price <= 0 || $price > 99999999.99) {
        throw new DomainException('Nie można obliczyć poprawnej kwoty pobytu.');
    }
    return number_format($price, 2, '.', '');
}
function ap_block_create()
{
    global $pdo;
    $rid = a_id(pole_tekstowe('pokoj'));
    a_record('Pokoje', 'ID_Pokoju', $rid, true);
    $od = pole_tekstowe('od');
    $do = pole_tekstowe('do');
    ap_dates($od, $do, true);
    if (pokoj_ma_kolizje($pdo, $rid, $od, $do)) {
        throw new DomainException('Blokada narusza istniejącą rezerwację lub inną blokadę.');
    }
    $reason = a_text('powod', 250);
    a_query('INSERT INTO BlokadyPokoi(ID_Pokoju,DataOd,DataDo,Powod,ID_Admina) VALUES(?,?,?,?,?)', [$rid,$od,$do,$reason,$_SESSION['user_id']]);
    $id = (int)$pdo->lastInsertId();
    a_log('Blokada terminu pokoju', 'blokada', $id, null, ['ID_Pokoju'=>$rid,'DataOd'=>$od,'DataDo'=>$do,'Powod'=>$reason]);
}
function ap_block_delete()
{
    $id = a_id(pole_tekstowe('id'));
    $peek = a_record('BlokadyPokoi', 'ID_Blokady', $id);
    a_record('Pokoje', 'ID_Pokoju', $peek['ID_Pokoju'], true);
    $old = a_record('BlokadyPokoi', 'ID_Blokady', $id, true);
    a_query('DELETE FROM BlokadyPokoi WHERE ID_Blokady=?', [$id]);
    a_log('Usunięcie blokady pokoju', 'blokada', $id, $old, null);
}
function ap_free_windows($day, $schedule, $visits)
{
    if (!$schedule || $schedule['TypDnia'] !== 'Praca') { return []; }
    $cursor = $day.' '.substr($schedule['GodzinaOd'], 0, 8);
    $end = $day.' '.substr($schedule['GodzinaDo'], 0, 8);
    if ($day === date('Y-m-d')) { $cursor = max($cursor, date('Y-m-d H:i:s')); }
    $windows = [];
    foreach ($visits as $v) {
        if ($v['DataDo'] <= $cursor) { continue; }
        if ($v['DataOd'] >= $end) { break; }
        if ($v['DataOd'] > $cursor) { $windows[] = [$cursor,min($end,$v['DataOd'])]; }
        $cursor = max($cursor,$v['DataDo']);
    }
    if ($cursor < $end) { $windows[] = [$cursor,$end]; }
    return $windows;
}
