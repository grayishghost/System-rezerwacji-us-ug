<?php
require_once __DIR__ . '/../../includes/admin_wizyty.php';
a_post(function () {
    if (a_enum('action', ['create','status']) === 'create') {
        aw_create();
        return;
    }
    $id = a_id(pole_tekstowe('id'));
    $peek = a_record('WizytyUslug', 'ID_Wizyty', $id);
    a_record('Pracownicy', 'ID_Pracownika', $peek['ID_Pracownika'], true);
    $old = a_record('WizytyUslug', 'ID_Wizyty', $id, true);
    if ((string)$old['Wersja'] !== pole_tekstowe('wersja')) {
        throw new DomainException('Wizyta została zmieniona. Odśwież listę.');
    }
    $status = a_enum('status', ar_statuses());
    if (!in_array($status, ar_transitions($old['Status']), true)) {
        throw new DomainException('Niedozwolone przejście statusu.');
    }
    if ($status === 'Ukończone' && new DateTimeImmutable($old['DataDo']) > new DateTimeImmutable()) {
        throw new DomainException('Nie można ukończyć przyszłej wizyty.');
    }
    a_query('UPDATE WizytyUslug SET Status=?,Wersja=Wersja+1 WHERE ID_Wizyty=?', [$status,$id]);
    a_log('Status wizyty', 'wizyta', $id, ['Status' => $old['Status']], ['Status' => $status]);
});
$f = aw_filter();
if (a_get('format') === 'csv') {
    if ($f['error']) {
        http_response_code(400);
        exit($f['error']);
    }
    $rows = a_query(aw_select().aw_from().$f['sql'].' ORDER BY w.DataOd', $f['params']);
    a_log('Eksport CSV wizyt', 'wizyta', null, null, ['format' => 'CSV']);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="wizyty.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['ID','Klient','Pracownik','Usługa','Od','Do','Cena PLN','Status'], ';', '"', '');
    while ($r = $rows->fetch()) {
        $row = [$r['ID_Wizyty'],$r['Klient'],$r['Pracownik'],$r['Nazwa_Uslugi'],$r['DataOd'],$r['DataDo'],$r['Cena'],$r['Status']];
        a_csv_row($out, $row);
    }
    fclose($out);
    exit;
}
$employees = a_query("SELECT p.ID_Pracownika,CONCAT(u.Imie,' ',u.Nazwisko) FROM Pracownicy p JOIN Uzytkownicy u ON u.ID_Uzytkownika=p.ID_Uzytkownika ORDER BY u.Nazwisko")->fetchAll(PDO::FETCH_KEY_PAIR);
$clients = a_query("SELECT ID_Uzytkownika,CONCAT(Imie,' ',Nazwisko) FROM Uzytkownicy WHERE Rola='klient' AND Aktywny=1 ORDER BY Nazwisko")->fetchAll(PDO::FETCH_KEY_PAIR);
$services = a_query('SELECT ID_Uslugi,Nazwa_Uslugi FROM Uslugi_dodatkowe ORDER BY Nazwa_Uslugi')->fetchAll(PDO::FETCH_KEY_PAIR);
$count = a_query('SELECT COUNT(*)'.aw_from().$f['sql'], $f['params'])->fetchColumn();
[$page,$pages,$limit,$offset] = a_page($count);
$rows = a_query(aw_select().aw_from().$f['sql']." ORDER BY w.DataOd DESC,w.ID_Wizyty DESC LIMIT $limit OFFSET $offset", $f['params'])->fetchAll();
a_start('Rezerwacje usług na godziny');
if ($f['error']) {
    echo '<p class="form-message form-message-error">'.e($f['error']).'</p>';
}
?>
<p class="hint">Osobny terminarz usług, niezależny od pobytów w pokojach. Każda wizyta jest bezpośrednio powiązana z klientem, pracownikiem i usługą. Cena i czas końca są zapisywane jako wartości historyczne.</p>
<section><h2>Dodaj wizytę jako administrator</h2><p class="hint">Najpierw przypisz usługę pracownikowi w Kompetencjach i ustaw jego dzień pracy w Grafiku. Serwer sprawdza grafik, urlop, czas trwania i kolizje.</p>
<?php if ($employees && $clients && $services): ?><form method="post" class="form-grid"><?= csrf_pole() ?>
<?php a_select('klient', 'Klient', $clients);
    a_select('pracownik', 'Pracownik', $employees);
    a_select('usluga', 'Usługa', $services);
    a_input('termin', 'Początek wizyty', '', 'datetime-local', 'required');
    a_input('uwagi', 'Uwagi', '', 'text', 'maxlength="1000"'); ?>
<button name="action" value="create">Dodaj wizytę</button></form><?php else: ?><p>Potrzebujesz przynajmniej jednego klienta, pracownika i usługi.</p><?php endif; ?></section>
<form method="get" class="toolbar"><?php a_input('od', 'Od dnia', a_get('od'), 'date');
a_input('do', 'Do dnia', a_get('do'), 'date');
a_select('status', 'Status', ['' => 'Wszystkie'] + array_combine(ar_statuses(), ar_statuses()), a_get('status'));
a_select('pracownik', 'Pracownik', ['' => 'Wszyscy'] + $employees, a_get('pracownik'));
a_select('usluga', 'Usługa', ['' => 'Wszystkie'] + $services, a_get('usluga')); ?><button>Filtruj</button><a href="wizyty.php">Wyczyść</a></form>
<div class="toolbar"><span>Wyników: <?= (int)$count ?></span><a class="button" href="?<?= e(http_build_query(array_merge($_GET, ['format' => 'csv']))) ?>">Eksport wizyt CSV</a></div>
<div class="table-wrap"><table><thead><tr><th scope="col">Klient / usługa</th><th scope="col">Pracownik</th><th scope="col">Termin</th><th scope="col">Cena</th><th scope="col">Status</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td>#<?= (int)$r['ID_Wizyty'] ?> <?= e($r['Klient']) ?><br><strong><?= e($r['Nazwa_Uslugi']) ?></strong><br><small><?= e($r['Uwagi']) ?></small></td><td><?= e($r['Pracownik']) ?></td><td><?= e($r['DataOd']) ?><br><?= e($r['DataDo']) ?></td><td><?= e($r['Cena']) ?> PLN</td><td><form method="post" class="toolbar" data-confirm="Zapisać status wizyty? Status końcowy jest nieodwracalny."><?php a_hidden($r['ID_Wizyty']); ?><input type="hidden" name="wersja" value="<?= (int)$r['Wersja'] ?>"><?php a_select('status', 'Status', array_combine(ar_transitions($r['Status']),ar_transitions($r['Status'])), $r['Status']); ?><button name="action" value="status">Zapisz</button></form></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="5" class="empty">Brak wizyt.</td></tr><?php endif; ?></tbody></table></div><?php a_pages($page,$pages);
a_end(); ?>
