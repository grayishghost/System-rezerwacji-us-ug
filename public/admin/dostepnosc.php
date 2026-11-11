<?php
require_once __DIR__ . '/../../includes/admin_pokoje.php';
a_post(function () {
    $action = a_enum('action', ['block_create','block_delete']);
    if ($action === 'block_create') { ap_block_create(); } else { ap_block_delete(); }
});
$error = '';
$od = a_get('od',date('Y-m-d'));
$do = a_get('do',date('Y-m-d',strtotime('+1 day')));
$day = a_get('dzien',$od);
try { ap_dates($od,$do); a_date($day); } catch (DomainException $ex) {
    $error = $ex->getMessage(); $od=date('Y-m-d'); $do=date('Y-m-d',strtotime('+1 day')); $day=$od;
}
$rooms = a_query("SELECT p.*,t.NazwaTypu,
 (SELECT COUNT(*) FROM Rezerwacje r WHERE r.ID_Pokoju=p.ID_Pokoju AND r.Status IN ('Oczekująca','Potwierdzona') AND r.DataOd<? AND r.DataDo>?) AS IlePobytow,
 (SELECT COUNT(*) FROM BlokadyPokoi b WHERE b.ID_Pokoju=p.ID_Pokoju AND b.DataOd<? AND b.DataDo>?) AS IleBlokad
 FROM Pokoje p JOIN TypyPokoi t ON t.ID_Typu=p.ID_Typu ORDER BY p.NrPokoju",[$do,$od,$do,$od])->fetchAll();
$options=[];
foreach ($rooms as $r) { $options[$r['ID_Pokoju']] = 'Pokój '.$r['NrPokoju']; }
$blocks = a_query('SELECT b.*,p.NrPokoju FROM BlokadyPokoi b JOIN Pokoje p ON p.ID_Pokoju=b.ID_Pokoju WHERE b.DataOd<? AND b.DataDo>? ORDER BY b.DataOd,b.ID_Blokady',[$do,$od])->fetchAll();
$staff = a_query('SELECT p.*,u.Imie,u.Nazwisko,u.Aktywny AS Konto,u.Rola,g.TypDnia,g.GodzinaOd,g.GodzinaDo FROM Pracownicy p JOIN Uzytkownicy u ON u.ID_Uzytkownika=p.ID_Uzytkownika LEFT JOIN GrafikPracownikow g ON g.ID_Pracownika=p.ID_Pracownika AND g.Data=? ORDER BY u.Nazwisko',[$day])->fetchAll();
$visits = a_query("SELECT ID_Pracownika,DataOd,DataDo FROM WizytyUslug WHERE DataOd<? AND DataDo>? AND Status IN ('Oczekująca','Potwierdzona') ORDER BY DataOd",[date('Y-m-d',strtotime($day.' +1 day')),$day])->fetchAll();
$byEmployee=[];
foreach ($visits as $v) { $byEmployee[$v['ID_Pracownika']][]=$v; }
a_start('Dostępność pokoi i pracowników'); ?>
<?php if ($error): ?><p class="form-message form-message-error"><?= e($error) ?></p><?php endif; ?>
<form method="get" class="toolbar"><?php a_input('od','Pokoje: od',$od,'date','required'); a_input('do','Pokoje: wymeldowanie',$do,'date','required'); a_input('dzien','Pracownicy: dzień',$day,'date','required'); ?><button>Sprawdź dostępność</button></form>
<section><h2>Pokoje · <?= e($od) ?> — <?= e($do) ?></h2><p class="hint">Wymeldowanie jest końcem przedziału. Wycofanie z oferty, Serwis, pobyt lub blokada terminu uniemożliwiają nową rezerwację.</p>
<div class="table-wrap"><table><thead><tr><th scope="col">Pokój</th><th scope="col">Wynik</th><th scope="col">Powód / operacja</th></tr></thead><tbody>
<?php foreach ($rooms as $r): $reasons=[];
 if (!$r['Aktywny']) { $reasons[]='Wycofany z oferty'; }
 if ($r['Status']==='Serwis') { $reasons[]='Serwis'; }
 if ($r['IlePobytow']) { $reasons[]='Kolizja z pobytem'; }
 if ($r['IleBlokad']) { $reasons[]='Blokada terminu'; }
 ?><tr><td>Pokój <?= (int)$r['NrPokoju'] ?><br><?= e($r['NazwaTypu']) ?></td><td><span class="badge"><?= $reasons ? 'NIEDOSTĘPNY' : 'WOLNY' ?></span></td><td><?= $reasons ? e(implode(' · ',$reasons)) : '<a href="rezerwacje.php">Dodaj rezerwację</a>' ?> · <a href="pokoje.php?edit=<?= (int)$r['ID_Pokoju'] ?>">Oferta pokoju</a></td></tr><?php endforeach; ?>
<?php if (!$rooms): ?><tr><td colspan="3">Najpierw dodaj pokój.</td></tr><?php endif; ?></tbody></table></div></section>
<section><h2>Zablokuj pokój w terminie</h2><form method="post" class="form-grid" data-confirm="Zablokować pokój? Termin zostanie wyłączony z nowych rezerwacji."><?= csrf_pole() ?>
<?php a_select('pokoj','Pokój',$options); a_input('od','Od',$od,'date','required'); a_input('do','Do (bez tego dnia)',$do,'date','required'); a_input('powod','Powód','','text','required maxlength="250"'); ?><button name="action" value="block_create">Dodaj blokadę</button></form>
<h3>Blokady przecinające wybrany zakres</h3><div class="table-wrap"><table><thead><tr><th scope="col">Pokój / termin</th><th scope="col">Powód</th><th scope="col">Operacja</th></tr></thead><tbody>
<?php foreach ($blocks as $r): ?><tr><td><?= (int)$r['NrPokoju'] ?> · <?= e($r['DataOd']) ?> — <?= e($r['DataDo']) ?></td><td><?= e($r['Powod']) ?></td><td><form method="post" data-confirm="Usunąć blokadę i ponownie dopuścić nowe rezerwacje w tym terminie?"><?php a_hidden($r['ID_Blokady']); ?><button name="action" value="block_delete">Usuń blokadę</button></form></td></tr><?php endforeach; ?>
<?php if (!$blocks): ?><tr><td colspan="3">Brak blokad w tym zakresie.</td></tr><?php endif; ?></tbody></table></div></section>
<section><h2>Pracownicy · <?= e($day) ?></h2><p class="hint">Wolne przedziały wynikają z godzin pracy pomniejszonych o aktywne wizyty. Do wizyty potrzebna jest też kompetencja wybranej usługi. Brak wpisu w grafiku nie oznacza dostępności.</p>
<div class="table-wrap"><table><thead><tr><th scope="col">Pracownik</th><th scope="col">Grafik</th><th scope="col">Wolne przedziały</th><th scope="col">Operacja</th></tr></thead><tbody>
<?php foreach ($staff as $r):
 $enabled=$r['Aktywny'] && $r['Konto'] && $r['Rola']==='pracownik';
 $windows=$enabled && $day>=date('Y-m-d') ? ap_free_windows($day,$r,$byEmployee[$r['ID_Pracownika']] ?? []) : [];
 ?><tr><td><?= e($r['Imie'].' '.$r['Nazwisko']) ?></td><td><?= e($r['TypDnia'] ?? 'Brak grafiku') ?><?php if ($r['TypDnia']==='Praca'): ?><br><?= e(substr($r['GodzinaOd'],0,5).'–'.substr($r['GodzinaDo'],0,5)) ?><?php endif; ?></td><td><?php if (!$enabled): ?>Profil lub konto nieaktywne<?php elseif (!$windows): ?>Brak wolnego przedziału<?php else: foreach ($windows as $w): ?><?= e(substr($w[0],11,5).'–'.substr($w[1],11,5)) ?><br><?php endforeach; endif; ?></td><td><a href="grafik.php?pracownik=<?= (int)$r['ID_Pracownika'] ?>">Grafik</a> · <a href="przypisania.php?pracownik=<?= (int)$r['ID_Pracownika'] ?>">Kompetencje</a> · <a href="wizyty.php?pracownik=<?= (int)$r['ID_Pracownika'] ?>">Wizyty</a></td></tr><?php endforeach; ?>
<?php if (!$staff): ?><tr><td colspan="4">Brak pracowników.</td></tr><?php endif; ?></tbody></table></div></section><?php a_end(); ?>
