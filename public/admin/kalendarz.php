<?php
require_once __DIR__ . '/../../includes/admin.php';
$month = a_get('miesiac', date('Y-m'));
if (!preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])$/D', $month) || substr($month, 0, 4) < '1001' || substr($month, 0, 4) > '9998') {
    $month = date('Y-m');
}
$start = new DateTimeImmutable($month.'-01');
$end = $start->modify('first day of next month');
$status = a_get('status', 'aktywne');
if (!in_array($status, ['aktywne','wszystkie'], true)) { $status = 'aktywne'; }
$where = " WHERE r.DataOd < ? AND r.DataDo > ?";
$params = [$end->format('Y-m-d'),$start->format('Y-m-d')];
if ($status === 'aktywne') {
    $where .= " AND r.Status IN ('Oczekująca','Potwierdzona')";
}
$rows = a_query('SELECT r.ID_Rezerwacji,r.DataOd,r.DataDo,r.Status,p.NrPokoju FROM Rezerwacje r JOIN Pokoje p ON p.ID_Pokoju=r.ID_Pokoju'.$where.' ORDER BY p.NrPokoju,r.DataOd', $params)->fetchAll();
$blocks=a_query('SELECT b.*,p.NrPokoju FROM BlokadyPokoi b JOIN Pokoje p ON p.ID_Pokoju=b.ID_Pokoju WHERE b.DataOd<? AND b.DataDo>? ORDER BY b.DataOd',[$end->format('Y-m-d'),$start->format('Y-m-d')])->fetchAll();
$vsql="SELECT w.ID_Wizyty,w.DataOd,w.DataDo,w.Status,s.Nazwa_Uslugi,w.ID_Pracownika FROM WizytyUslug w JOIN Uslugi_dodatkowe s ON s.ID_Uslugi=w.ID_Uslugi WHERE w.DataOd<? AND w.DataDo>?";
if ($status==='aktywne') { $vsql.=" AND w.Status IN ('Oczekująca','Potwierdzona')"; }
$visits=a_query($vsql.' ORDER BY w.DataOd',[$end->format('Y-m-d'),$start->format('Y-m-d')])->fetchAll();
a_start('Kalendarz pobytów, blokad i wizyt'); ?>
<form method="get" class="toolbar"><?php a_input('miesiac', 'Miesiąc', $month, 'month');
a_select('status', 'Pokaż', ['aktywne' => 'Oczekujące i potwierdzone','wszystkie' => 'Wszystkie statusy'], $status); ?><button>Pokaż</button><a href="?<?= e(http_build_query(['miesiac'=>$start->modify('-1 month')->format('Y-m'),'status'=>$status])) ?>">← Poprzedni</a><a href="?<?= e(http_build_query(['miesiac'=>$end->format('Y-m'),'status'=>$status])) ?>">Następny →</a></form>
<p class="hint">Kliknij pobyt, aby otworzyć szczegóły. Dzień wymeldowania nie jest dniem zajętości pokoju. Kalendarz używa wyłącznie numerów rezerwacji i pokoi.</p>
<div class="table-wrap"><div class="calendar"><?php foreach (['Pon','Wt','Śr','Czw','Pt','Sob','Nd'] as $d): ?><strong class="day" style="min-height:0"><?= e($d) ?></strong><?php endforeach; ?>
<?php for ($n = 1;$n < (int)$start->format('N');$n++): ?><div></div><?php endfor; ?>
<?php for ($d = $start;$d < $end;$d = $d->modify('+1 day')): ?><div class="day <?= $d->format('Y-m-d') === date('Y-m-d') ? 'today' : '' ?>"><strong><?= e($d->format('j')) ?></strong>
<?php foreach ($rows as $r): if (substr($r['DataOd'], 0, 10) <= $d->format('Y-m-d') && substr($r['DataDo'], 0, 10) > $d->format('Y-m-d')): ?><a href="rezerwacje.php?szczegoly=<?= (int)$r['ID_Rezerwacji'] ?>">Pok. <?= (int)$r['NrPokoju'] ?> · #<?= (int)$r['ID_Rezerwacji'] ?><br><?= e($r['Status']) ?></a><?php endif; endforeach; ?>
<?php foreach ($blocks as $b): if ($b['DataOd']<=$d->format('Y-m-d') && $b['DataDo']>$d->format('Y-m-d')): ?><a href="dostepnosc.php?od=<?= e($b['DataOd']) ?>&amp;do=<?= e($b['DataDo']) ?>">Blokada · pok. <?= (int)$b['NrPokoju'] ?></a><?php endif; endforeach; ?>
<?php foreach ($visits as $w): if (substr($w['DataOd'],0,10)<=$d->format('Y-m-d') && $w['DataDo']>$d->format('Y-m-d').' 00:00:00'): ?><a href="wizyty.php?pracownik=<?= (int)$w['ID_Pracownika'] ?>">Wizyta #<?= (int)$w['ID_Wizyty'] ?> · <?= e(substr($w['DataOd'],11,5)) ?><br><?= e($w['Nazwa_Uslugi']) ?></a><?php endif; endforeach; ?>
</div><?php endfor; ?></div></div><?php a_end(); ?>
