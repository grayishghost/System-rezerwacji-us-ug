<?php
require_once __DIR__ . '/../../includes/admin.php';
a_post(function () {
    if (pole_tekstowe('action') === 'delete') {
        $id = a_id(pole_tekstowe('id'));
        $old = a_record('GrafikPracownikow', 'ID_Wpisu', $id, true);
        if (a_query("SELECT COUNT(*) FROM WizytyUslug WHERE ID_Pracownika=? AND DATE(DataOd)=? AND Status IN ('Oczekująca','Potwierdzona')", [$old['ID_Pracownika'],$old['Data']])->fetchColumn()) {
            throw new DomainException('Ten dzień ma aktywne wizyty usług. Najpierw anuluj wizyty.');
        }
        a_query('DELETE FROM GrafikPracownikow WHERE ID_Wpisu=?', [$id]);
        a_log('Usunięcie wpisu', 'grafik', $id, $old, null);
        return;
    }
    $employee = a_id(pole_tekstowe('pracownik'));
    a_record('Pracownicy', 'ID_Pracownika', $employee, true);
    $start = a_date(pole_tekstowe('od'));
    $end = a_date(pole_tekstowe('do'));
    $days = (int)(new DateTimeImmutable($start))->diff(new DateTimeImmutable($end))->format('%r%a');
    if ($days < 0 || $days > 89) {
        throw new DomainException('Zakres musi obejmować od 1 do 90 dni.');
    }
    $type = a_enum('typ', ['Praca','Urlop','Wolne','Zwolnienie']);
    $timeStart = $timeEnd = null;
    if ($type === 'Praca') {
        $timeStart = pole_tekstowe('godzina_od');
        $timeEnd = pole_tekstowe('godzina_do');
        if (!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/D', $timeStart) || !preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/D', $timeEnd) || $timeStart >= $timeEnd) {
            throw new DomainException('Podaj prawidłowe godziny pracy, od wcześniejszej do późniejszej (bez zmiany nocnej).');
        }
    }
    $overwrite = pole_tekstowe('nadpisz') === '1';
    for ($n = 0;$n <= $days;$n++) {
        $date = (new DateTimeImmutable($start))->modify("+$n days")->format('Y-m-d');

        $visits = a_query("SELECT DataOd,DataDo FROM WizytyUslug WHERE ID_Pracownika=? AND DATE(DataOd)=? AND Status IN ('Oczekująca','Potwierdzona')", [$employee,$date])->fetchAll();
        foreach ($visits as $visit) {
            if ($type !== 'Praca' || substr($visit['DataOd'], 11, 5) < $timeStart || substr($visit['DataDo'], 11, 5) > $timeEnd) {
                throw new DomainException('Zmiana grafiku narusza aktywną wizytę dnia '.$date.'. Najpierw anuluj wizytę.');
            }
        }

        $old = a_query('SELECT * FROM GrafikPracownikow WHERE ID_Pracownika=? AND Data=? FOR UPDATE', [$employee,$date])->fetch();
        if ($old && !$overwrite) {
            throw new DomainException('Istnieje wpis dla '.$date.'. Zaznacz świadomie nadpisywanie lub zmień zakres. Nie zapisano żadnych dni.');
        }
        if ($old) {
            a_query('UPDATE GrafikPracownikow SET TypDnia=?,GodzinaOd=?,GodzinaDo=? WHERE ID_Wpisu=?', [$type,$timeStart,$timeEnd,$old['ID_Wpisu']]);
            $id = $old['ID_Wpisu'];
        } else {
            a_query('INSERT INTO GrafikPracownikow(ID_Pracownika,Data,TypDnia,GodzinaOd,GodzinaDo) VALUES(?,?,?,?,?)', [$employee,$date,$type,$timeStart,$timeEnd]);
            global $pdo;
            $id = (int)$pdo->lastInsertId();
        }
        a_log($old ? 'Zmiana dnia' : 'Dodanie dnia', 'grafik', $id, $old ?: null, ['ID_Pracownika' => $employee,'Data' => $date,'TypDnia' => $type,'GodzinaOd' => $timeStart,'GodzinaDo' => $timeEnd]);
    }
});
$employees = a_query("SELECT p.ID_Pracownika,CONCAT(u.Imie,' ',u.Nazwisko) FROM Pracownicy p JOIN Uzytkownicy u ON u.ID_Uzytkownika=p.ID_Uzytkownika ORDER BY u.Nazwisko")->fetchAll(PDO::FETCH_KEY_PAIR);
$employee = a_get('pracownik');
$type = a_get('typ');
$month = a_get('miesiac', date('Y-m'));
if (!preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])$/D', $month) || substr($month, 0, 4) < '1000' || substr($month, 0, 4) > '9998') {
    $month = date('Y-m');
}
$start = $month.'-01';
$end = (new DateTimeImmutable($start))->modify('first day of next month')->format('Y-m-d');
$where = ['g.Data>=?','g.Data<?'];
$params = [$start,$end];
if (ctype_digit($employee)) {
    $where[] = 'g.ID_Pracownika=?';
    $params[] = (int)$employee;
}
if (in_array($type, ['Praca','Urlop','Wolne','Zwolnienie'], true)) {
    $where[] = 'g.TypDnia=?';
    $params[] = $type;
}
$from = ' FROM GrafikPracownikow g JOIN Pracownicy p ON p.ID_Pracownika=g.ID_Pracownika JOIN Uzytkownicy u ON u.ID_Uzytkownika=p.ID_Uzytkownika WHERE '.implode(' AND ', $where);
$count = a_query('SELECT COUNT(*)'.$from, $params)->fetchColumn();
[$page,$pages,$limit,$offset] = a_page($count);
$rows = a_query('SELECT g.*,u.Imie,u.Nazwisko'.$from." ORDER BY g.Data,u.Nazwisko LIMIT $limit OFFSET $offset", $params)->fetchAll();
a_start('Grafik, urlopy i dni wolne'); ?>
<section><h2>Dodaj lub zmień zakres dni</h2><p class="hint">Jeden wpis na pracownika i datę. Zakres obejmuje wszystkie dni, także weekendy. Brak wpisu = brak zadeklarowanej dostępności. Grafik nie zmienia istniejących rezerwacji pobytów; ich pracownik to opiekun/recepcjonista, nie zasób zajęty przez cały pobyt.</p>
<?php if ($employees): ?><form method="post" class="form-grid" data-confirm="Zapisać cały zakres dni? Zaznaczone nadpisywanie zastąpi istniejące wpisy."><?= csrf_pole() ?>
<?php a_select('pracownik', 'Pracownik', $employees, $employee);
    a_input('od', 'Od', date('Y-m-d'), 'date', 'required');
    a_input('do', 'Do', date('Y-m-d'), 'date', 'required');
    a_select('typ', 'Rodzaj dnia', ['Praca' => 'Praca','Urlop' => 'Urlop','Wolne' => 'Wolne','Zwolnienie' => 'Zwolnienie']);
    a_input('godzina_od', 'Praca od', '08:00', 'time');
    a_input('godzina_do', 'Praca do', '16:00', 'time'); ?>
<label class="check"><input type="checkbox" name="nadpisz" value="1">Nadpisz istniejące wpisy</label><button name="action" value="save">Zapisz zakres</button></form><?php else: ?><p>Najpierw dodaj pracownika.</p><?php endif; ?></section>
<form method="get" class="toolbar"><?php a_input('miesiac', 'Miesiąc', $month, 'month');
a_select('pracownik', 'Pracownik', ['' => 'Wszyscy'] + $employees, $employee);
a_select('typ', 'Rodzaj', ['' => 'Wszystkie','Praca' => 'Praca','Urlop' => 'Urlop','Wolne' => 'Wolne','Zwolnienie' => 'Zwolnienie'], $type); ?><button>Filtruj</button></form>
<div class="table-wrap"><table><thead><tr><th>Data</th><th>Pracownik</th><th>Rodzaj</th><th>Godziny</th><th>Operacje</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['Data']) ?></td><td><?= e($r['Imie'].' '.$r['Nazwisko']) ?></td><td><?= e($r['TypDnia']) ?></td><td><?= $r['TypDnia'] === 'Praca' ? e(substr($r['GodzinaOd'], 0, 5).'–'.substr($r['GodzinaDo'], 0, 5)) : '—' ?></td><td><?php a_delete($r['ID_Wpisu']); ?></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="5" class="empty">Brak wpisów w wybranym miesiącu.</td></tr><?php endif; ?></tbody></table></div><?php a_pages($page, $pages);
a_end(); ?>
