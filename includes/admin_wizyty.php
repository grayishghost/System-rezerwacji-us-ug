<?php

require_once __DIR__ . '/admin_rezerwacje.php';
function aw_select()
{
    return "SELECT w.*,CONCAT(k.Imie,' ',k.Nazwisko) AS Klient,CONCAT(u.Imie,' ',u.Nazwisko) AS Pracownik,s.Nazwa_Uslugi";
}
function aw_from()
{
    return ' FROM WizytyUslug w JOIN Uzytkownicy k ON k.ID_Uzytkownika=w.ID_Klienta JOIN Pracownicy p ON p.ID_Pracownika=w.ID_Pracownika JOIN Uzytkownicy u ON u.ID_Uzytkownika=p.ID_Uzytkownika JOIN Uslugi_dodatkowe s ON s.ID_Uslugi=w.ID_Uslugi';
}
function aw_filter()
{
    $where = ['1=1'];
    $params = [];
    $error = '';
    try {
        $od = a_get('od');
        $do = a_get('do');
        if ($od !== '') {
            a_date($od);
            $where[] = 'w.DataOd >= ?';
            $params[] = $od.' 00:00:00';
        }
        if ($do !== '') {
            a_date($do);
            $where[] = 'w.DataOd < ?';
            $params[] = (new DateTimeImmutable($do))->modify('+1 day')->format('Y-m-d').' 00:00:00';
        }
        if ($od !== '' && $do !== '' && $od > $do) {
            throw new DomainException('Błędny zakres dat.');
        }
        if (a_get('status') !== '') {
            if (!in_array(a_get('status'), ar_statuses(), true)) {
                throw new DomainException('Błędny status.');
            }$where[] = 'w.Status=?';
            $params[] = a_get('status');
        }
        foreach (['pracownik' => 'w.ID_Pracownika','usluga' => 'w.ID_Uslugi'] as $key => $col) {
            if (a_get($key) !== '') {
                $where[] = $col.'=?';
                $params[] = a_id(a_get($key));
            }
        }
    } catch (DomainException $ex) {
        $where = ['1=0'];
        $params = [];
        $error = $ex->getMessage();
    }
    return ['sql' => ' WHERE '.implode(' AND ', $where),'params' => $params,'error' => $error];
}
require_once __DIR__.'/terminarz.php';
function aw_create()
{
    $id=t_create(a_id(pole_tekstowe('klient')),a_id(pole_tekstowe('usluga')),a_id(pole_tekstowe('pracownik')),pole_tekstowe('termin'),a_text('uwagi',1000,false));
    a_log('Utworzenie wizyty','wizyta',$id,null,['Status'=>'Oczekująca']);
}
