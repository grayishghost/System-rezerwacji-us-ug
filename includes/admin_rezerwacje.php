<?php

require_once __DIR__ . '/admin.php';
function ar_statuses()
{
    return ['Oczekująca','Potwierdzona','Ukończone','Anulowana'];
}
function ar_transitions($status)
{
    $map = ['Oczekująca' => ['Oczekująca','Potwierdzona','Anulowana'],
          'Potwierdzona' => ['Potwierdzona','Ukończone','Anulowana'],
          'Ukończone' => ['Ukończone'],'Anulowana' => ['Anulowana']];
    return $map[$status] ?? [];
}
function ar_version($r)
{
    return hash('sha256', json_encode([$r['Status'],(string)$r['ID_Pracownika']]));
}
function ar_filter()
{
    $where = ['1=1'];
    $params = [];
    $error = '';
    $od = a_get('od');
    $do = a_get('do');
    $status = a_get('status');
    $employee = a_get('pracownik');
    $q = mb_substr(a_get('q'), 0, 100);
    try {
        if ($od !== '') {
            a_date($od);
            $where[] = 'r.DataDo > ?';
            $params[] = $od.' 00:00:00';
        }
        if ($do !== '') {
            a_date($do);
            $where[] = 'r.DataOd < ?';
            $params[] = (new DateTimeImmutable($do))->modify('+1 day')->format('Y-m-d').' 00:00:00';
        }
        if ($od !== '' && $do !== '' && $od > $do) {
            throw new DomainException('Początek zakresu nie może być późniejszy niż koniec.');
        }
        if ($status !== '') {
            if (!in_array($status, ar_statuses(), true)) {
                throw new DomainException('Niepoprawny status.');
            }
            $where[] = 'r.Status=?';
            $params[] = $status;
        }
        if ($employee === 'brak') {
            $where[] = 'r.ID_Pracownika IS NULL';
        } elseif ($employee !== '') {
            $where[] = 'r.ID_Pracownika=?';
            $params[] = a_id($employee);
        }
        if ($q !== '') {
            $where[] = "(u.Imie LIKE ? OR u.Nazwisko LIKE ? OR CAST(r.ID_Rezerwacji AS CHAR)=? OR CAST(p.NrPokoju AS CHAR)=?)";
            $params = array_merge($params, ['%'.$q.'%','%'.$q.'%',$q,$q]);
        }
    } catch (DomainException $ex) {
        $error = $ex->getMessage();
        $where = ['1=0'];
        $params = [];
    }
    return ['sql' => ' WHERE '.implode(' AND ', $where),'params' => $params,'error' => $error,
            'values' => compact('od', 'do', 'status', 'employee', 'q')];
}
function ar_from()
{
    return " FROM Rezerwacje r JOIN Uzytkownicy u ON u.ID_Uzytkownika=r.ID_Klienta
      JOIN Pokoje p ON p.ID_Pokoju=r.ID_Pokoju LEFT JOIN Pracownicy e ON e.ID_Pracownika=r.ID_Pracownika
      LEFT JOIN Uzytkownicy eu ON eu.ID_Uzytkownika=e.ID_Uzytkownika";
}
function ar_select()
{
    return "SELECT r.*,p.NrPokoju,CONCAT(u.Imie,' ',u.Nazwisko) AS Klient,
            CONCAT(eu.Imie,' ',eu.Nazwisko) AS Pracownik";
}
