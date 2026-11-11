<?php
require_once __DIR__ . '/../../includes/admin.php';
$stats = [
 'Pokoje udostępnione'=>a_query('SELECT COUNT(*) FROM Pokoje WHERE Aktywny=1')->fetchColumn(),
 'Pokoje w serwisie'=>a_query("SELECT COUNT(*) FROM Pokoje WHERE Status='Serwis'")->fetchColumn(),
 'Wizyty usług (wszystkie)' => a_query('SELECT COUNT(*) FROM WizytyUslug')->fetchColumn(),
 'Wizyty usług dziś' => a_query("SELECT COUNT(*) FROM WizytyUslug WHERE DATE(DataOd)=CURDATE() AND Status IN ('Oczekująca','Potwierdzona')")->fetchColumn(),
 'Klienci' => a_query("SELECT COUNT(*) FROM Uzytkownicy WHERE Rola='klient'")->fetchColumn(),
 'Pracownicy (wszyscy)' => a_query('SELECT COUNT(*) FROM Pracownicy')->fetchColumn(),
 'Usługi (wszystkie)' => a_query('SELECT COUNT(*) FROM Uslugi_dodatkowe')->fetchColumn(),
 'Pobyty (wszystkie)' => a_query('SELECT COUNT(*) FROM Rezerwacje')->fetchColumn(),
 'Pobyty dziś' => a_query("SELECT COUNT(*) FROM Rezerwacje WHERE DataOd < DATE_ADD(CURDATE(),INTERVAL 1 DAY) AND DataDo > CURDATE() AND Status IN ('Oczekująca','Potwierdzona')")->fetchColumn(),
 'Przyjazdy dziś' => a_query("SELECT COUNT(*) FROM Rezerwacje WHERE DATE(DataOd)=CURDATE() AND Status IN ('Oczekująca','Potwierdzona')")->fetchColumn()
];
$monthly = a_query("SELECT DATE_FORMAT(DataOd,'%Y-%m') AS Miesiac,COUNT(*) AS Ile,SUM(Kwota) AS Wartosc FROM Rezerwacje WHERE Status<>'Anulowana' AND DataOd>=DATE_FORMAT(DATE_SUB(CURDATE(),INTERVAL 5 MONTH),'%Y-%m-01') AND DataOd<DATE_FORMAT(DATE_ADD(CURDATE(),INTERVAL 1 MONTH),'%Y-%m-01') GROUP BY Miesiac ORDER BY Miesiac")->fetchAll();
$map = [];
foreach ($monthly as $r) {
    $map[$r['Miesiac']] = $r;
}$months = [];
for ($n = 5;$n >= 0;$n--) {
    $m = (new DateTimeImmutable('first day of this month'))->modify("-$n months")->format('Y-m');
    $months[] = $map[$m] ?? ['Miesiac' => $m,'Ile' => 0,'Wartosc' => 0];
}
$max = max(1, ...array_column($months, 'Ile'));
$statuses = a_query('SELECT Status,COUNT(*) AS Ile FROM Rezerwacje GROUP BY Status')->fetchAll();
$upcoming = a_query("SELECT r.ID_Rezerwacji,r.DataOd,p.NrPokoju FROM Rezerwacje r JOIN Pokoje p ON p.ID_Pokoju=r.ID_Pokoju WHERE r.Status IN ('Oczekująca','Potwierdzona') AND r.DataOd >= CURDATE() ORDER BY r.DataOd LIMIT 5")->fetchAll();
a_start('Pulpit administratora'); ?>
<section><h2>Szybkie działania</h2><div class="toolbar"><a class="button" href="rezerwacje.php">Dodaj / obsłuż pobyt</a><a class="button" href="pokoje.php">Udostępnij pokój</a><a class="button" href="dostepnosc.php">Sprawdź dostępność</a><a class="button" href="pracownicy.php">Pracownicy</a></div></section>
<div class="cards"><?php foreach ($stats as $label => $value): ?><div class="card"><span class="muted"><?= e($label) ?></span><strong><?= (int)$value ?></strong></div><?php endforeach; ?></div>
<section><h2>Ostatnie 6 miesięcy · rezerwacje według daty przyjazdu</h2><p class="hint">Bez anulowanych. Wartość oznacza kwotę pobytów, a nie faktycznie otrzymane płatności. Zero oznacza brak rezerwacji w danym miesiącu.</p>
<div class="table-wrap"><table><thead><tr><th scope="col">Miesiąc</th><th scope="col">Liczba pobytów</th><th scope="col">Wykres</th><th scope="col">Wartość pobytów</th></tr></thead><tbody>
<?php foreach ($months as $m): ?><tr><td><?= e($m['Miesiac']) ?></td><td><?= (int)$m['Ile'] ?></td><td style="width:40%"><div class="bar" role="img" aria-label="<?= (int)$m['Ile'] ?> rezerwacji"><span style="width:<?= round(100 * $m['Ile'] / $max, 2) ?>%"></span></div></td><td><?= e(number_format((float)$m['Wartosc'], 2, ',', ' ')) ?> PLN</td></tr><?php endforeach; ?></tbody></table></div></section>
<div class="cards"><section><h2>Statusy wszystkich pobytów</h2><?php foreach ($statuses as $s): ?><p><a href="rezerwacje.php?status=<?= e(urlencode($s['Status'])) ?>"><?= e($s['Status']) ?></a> <strong><?= (int)$s['Ile'] ?></strong></p><?php endforeach; ?><?php if (!$statuses): ?><p>Brak rezerwacji.</p><?php endif; ?></section>
<section><h2>Najbliższe przyjazdy</h2><?php foreach ($upcoming as $r): ?><p><a href="rezerwacje.php?szczegoly=<?= (int)$r['ID_Rezerwacji'] ?>">#<?= (int)$r['ID_Rezerwacji'] ?></a> · <?= e(substr($r['DataOd'], 0, 10)) ?> · pokój <?= (int)$r['NrPokoju'] ?></p><?php endforeach; ?><?php if (!$upcoming): ?><p>Brak zaplanowanych przyjazdów.</p><?php endif; ?></section></div>
<section><h2>Ostatnie operacje administracyjne</h2><ul><?php foreach (a_query('SELECT Czas,Akcja,Obiekt,ID_Obiektu FROM LogAdministratora ORDER BY ID_Logu DESC LIMIT 5')->fetchAll() as $op): ?><li><?= e($op['Czas'].' · '.$op['Akcja'].' · '.$op['Obiekt'].' #'.$op['ID_Obiektu']) ?></li><?php endforeach; ?></ul><a href="logi.php">Pełna historia zmian</a></section>
<?php a_end(); ?>
