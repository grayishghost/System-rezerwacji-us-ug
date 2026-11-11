<?php
require_once __DIR__ . '/../../includes/admin_rezerwacje.php';
a_post(function () {
    $action=a_enum('action',['','update','create','reschedule']);
    if ($action==='create') { ar_create_booking(); return; }
    if ($action==='reschedule') { ar_reschedule(); return; }
    $id = a_id(pole_tekstowe('id'));
    
    $peek = a_record('Rezerwacje', 'ID_Rezerwacji', $id);
    a_record('Pokoje', 'ID_Pokoju', $peek['ID_Pokoju'], true);
    $old = a_record('Rezerwacje', 'ID_Rezerwacji', $id, true);
    if (!hash_equals(ar_version($old), pole_tekstowe('wersja'))) {
        throw new DomainException('Rezerwacja została zmieniona w innej karcie. Odśwież i spróbuj ponownie.');
    }
    $status = a_enum('status', ar_statuses());
    if (!in_array($status, ar_transitions($old['Status']), true)) {
        throw new DomainException('Niedozwolona zmiana statusu. Status końcowy jest nieodwracalny.');
    }
    if ($status === 'Ukończone' && $old['Status'] !== 'Ukończone' && new DateTimeImmutable($old['DataDo']) > new DateTimeImmutable()) {
        throw new DomainException('Pobyt można ukończyć dopiero po dacie wymeldowania.');
    }
    $employee = pole_tekstowe('pracownik');
    $employee = $employee === '' ? null : a_id($employee);
    $oldEmployee = $old['ID_Pracownika'] === null ? null : (int)$old['ID_Pracownika'];
    if ($employee !== $oldEmployee) {
        if (in_array($old['Status'], ['Anulowana','Ukończone'], true)) {
            throw new DomainException('Nie zmieniaj opiekuna zakończonej rezerwacji.');
        }
        if ($employee !== null) {
            $e = a_query("SELECT e.Aktywny,u.Aktywny AS Konto,u.Rola FROM Pracownicy e JOIN Uzytkownicy u ON u.ID_Uzytkownika=e.ID_Uzytkownika WHERE e.ID_Pracownika=? FOR UPDATE", [$employee])->fetch();
            if (!$e || !$e['Aktywny'] || !$e['Konto'] || $e['Rola'] !== 'pracownik') {
                throw new DomainException('Przydziel aktywnego pracownika z aktywnym kontem.');
            }
        }
    }
    a_query('UPDATE Rezerwacje SET Status=?,ID_Pracownika=?,Wersja=Wersja+1 WHERE ID_Rezerwacji=?', [$status,$employee,$id]);
    a_log('Status / opiekun pobytu', 'rezerwacja', $id, ['Status' => $old['Status'],'ID_Pracownika' => $oldEmployee], ['Status' => $status,'ID_Pracownika' => $employee]);
});
$f = ar_filter();
$count = a_query('SELECT COUNT(*)'.ar_from().$f['sql'], $f['params'])->fetchColumn();
[$page,$pages,$limit,$offset] = a_page($count);
$rows = a_query(ar_select().ar_from().$f['sql']." ORDER BY r.DataOd DESC,r.ID_Rezerwacji DESC LIMIT $limit OFFSET $offset", $f['params'])->fetchAll();
$employees = a_query("SELECT e.ID_Pracownika,CONCAT(u.Imie,' ',u.Nazwisko,IF(e.Aktywny=1 AND u.Aktywny=1,'',' (nieaktywny)')) FROM Pracownicy e JOIN Uzytkownicy u ON u.ID_Uzytkownika=e.ID_Uzytkownika ORDER BY u.Nazwisko")->fetchAll(PDO::FETCH_KEY_PAIR);
$activeEmployees = a_query("SELECT e.ID_Pracownika,CONCAT(u.Imie,' ',u.Nazwisko) FROM Pracownicy e JOIN Uzytkownicy u ON u.ID_Uzytkownika=e.ID_Uzytkownika WHERE e.Aktywny=1 AND u.Aktywny=1 AND u.Rola='pracownik' ORDER BY u.Nazwisko")->fetchAll(PDO::FETCH_KEY_PAIR);
$details = null;
$orders = [];
if (ctype_digit(a_get('szczegoly'))) {
    $details = a_query(ar_select().',u.Email,u.Telefon'.ar_from().' WHERE r.ID_Rezerwacji=?', [a_get('szczegoly')])->fetch();
    if ($details) {
        $orders = a_query('SELECT z.*,s.Nazwa_Uslugi FROM ZamowieniaUslugi z JOIN Uslugi_dodatkowe s ON s.ID_Uslugi=z.ID_Uslugi WHERE z.ID_Rezerwacji=? ORDER BY z.DataZamowienia', [$details['ID_Rezerwacji']])->fetchAll();
    }
}
a_start('Rezerwacje pobytów');
ar_booking_form();
if ($f['error']) {
    echo '<p class="form-message form-message-error">'.e($f['error']).'</p>';
}
?>
<p class="hint">Pracownik to opiekun pobytu/recepcjonista. Rezerwacje pokoi są dobowe; czas trwania usług dodatkowych jest przechowywany osobno. Zakres dat pokazuje pobyty przecinające wybrane dni (bez dnia wymeldowania).</p>
<form method="get" class="toolbar"><?php $v = $f['values'];
a_input('q', 'Klient / nr rezerwacji / pokój', $v['q']);
a_input('od', 'Od dnia', $v['od'], 'date');
a_input('do', 'Do dnia', $v['do'], 'date');
a_select('status', 'Status', ['' => 'Wszystkie'] + array_combine(ar_statuses(), ar_statuses()), $v['status']);
a_select('pracownik', 'Opiekun', ['' => 'Wszyscy','brak' => 'Bez opiekuna'] + $employees, $v['employee']); ?><button>Filtruj</button><a href="rezerwacje.php">Wyczyść</a></form>
<div class="toolbar"><span>Wyników: <?= (int)$count ?></span><a class="button" href="eksport.php?<?= e(http_build_query(array_diff_key($_GET, ['page' => 1,'szczegoly' => 1]))) ?>">Eksport wyników CSV</a></div>
<?php if ($details): ?><section><h2>Rezerwacja #<?= (int)$details['ID_Rezerwacji'] ?></h2><p><?= e($details['Klient']) ?> · <?= e($details['Email']) ?> · <?= e($details['Telefon']) ?></p><p>Pokój <?= (int)$details['NrPokoju'] ?> · <?= e($details['DataOd']) ?> — <?= e($details['DataDo']) ?></p><p>Kwota pobytu: <?= e($details['Kwota']) ?> PLN · Źródło: <?= e($details['Zrodlo_rezerwacji']) ?></p><p>Uwagi: <?= e($details['Uwagi']) ?></p><h3>Zamówienia usług dodatkowych</h3>
<?php if (!$orders): ?><p>Brak zamówień usług dla tego pobytu.</p><?php else: ?><ul><?php foreach ($orders as $o): ?><li><?= e($o['Nazwa_Uslugi']) ?> · <?= (int)$o['Ilosc'] ?> × <?= e($o['Cena_jednostkowa']) ?> PLN · <?= e($o['DataZamowienia']) ?></li><?php endforeach; ?></ul><?php endif; ?></section><?php endif; ?>
<div class="table-wrap"><table><thead><tr><th scope="col">Rezerwacja / klient</th><th scope="col">Pobyt / pokój</th><th scope="col">Kwota</th><th scope="col">Status i opiekun</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><a href="?<?= e(http_build_query(array_merge($_GET, ['szczegoly' => $r['ID_Rezerwacji']]))) ?>">#<?= (int)$r['ID_Rezerwacji'] ?></a><br><?= e($r['Klient']) ?></td><td><?= e(substr($r['DataOd'], 0, 10)) ?><br><?= e(substr($r['DataDo'], 0, 10)) ?><br>Pokój <?= (int)$r['NrPokoju'] ?><br><a href="?edit=<?= (int)$r['ID_Rezerwacji'] ?>">Edytuj termin i pokój</a></td><td><?= e(number_format((float)$r['Kwota'], 2, ',', ' ')) ?> PLN</td><td>
<form method="post" class="toolbar" data-confirm="Zapisać status i opiekuna? Anulowanie lub ukończenie jest nieodwracalne."><?php a_hidden($r['ID_Rezerwacji']); ?><input type="hidden" name="wersja" value="<?= e(ar_version($r)) ?>">
<?php a_select('status', 'Status', array_combine(ar_transitions($r['Status']), ar_transitions($r['Status'])), $r['Status']);
    $opts = ['' => 'Bez opiekuna'] + $activeEmployees;
    if ($r['ID_Pracownika'] && !isset($opts[$r['ID_Pracownika']])) {
        $opts[$r['ID_Pracownika']] = $r['Pracownik'].' (nieaktywny)';
    }
    a_select('pracownik', 'Opiekun', $opts, $r['ID_Pracownika']); ?><button>Zapisz</button></form></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="4" class="empty">Brak rezerwacji dla podanych filtrów.</td></tr><?php endif; ?></tbody></table></div><?php a_pages($page, $pages);
a_end(); ?>
