<?php
require_once __DIR__ . '/../../includes/admin_pokoje.php';
a_post(function () {
    a_enum('action', ['save','delete']);
    $id = pole_tekstowe('id') === '0' ? 0 : a_id(pole_tekstowe('id'));
    $old = $id ? a_record('Pokoje', 'ID_Pokoju', $id, true) : null;
    if ($old && (string)$old['Wersja'] !== pole_tekstowe('wersja')) {
        throw new DomainException('Pokój został zmieniony w innej karcie. Odśwież formularz.');
    }
    if (pole_tekstowe('action') === 'delete') {
        if (!$old) { throw new DomainException('Wybierz pokój.'); }
        if (a_query('SELECT ID_Rezerwacji FROM Rezerwacje WHERE ID_Pokoju=? LIMIT 1 FOR UPDATE', [$id])->fetchColumn()) {
            throw new DomainException('Pokój ma historię pobytów. Wycofaj go z oferty zamiast usuwać.');
        }
        a_query('DELETE FROM Pokoje WHERE ID_Pokoju=?', [$id]);
        a_log('Usunięcie pokoju', 'pokoj', $id, $old, null);
        return;
    }
    $nr = filter_var(pole_tekstowe('numer'), FILTER_VALIDATE_INT, ['options'=>['min_range'=>1,'max_range'=>99999]]);
    $cap = filter_var(pole_tekstowe('osoby'), FILTER_VALIDATE_INT, ['options'=>['min_range'=>1,'max_range'=>50]]);
    $floor = filter_var(pole_tekstowe('pietro'), FILTER_VALIDATE_INT, ['options'=>['min_range'=>-5,'max_range'=>200]]);
    if (!$nr || !$cap || $floor === false) { throw new DomainException('Sprawdź numer pokoju, pojemność i piętro.'); }
    $price = str_replace(',', '.', pole_tekstowe('cena'));
    if (!preg_match('/^(0|[1-9][0-9]{0,7})(\.[0-9]{1,2})?$/D', $price) || (float)$price <= 0) {
        throw new DomainException('Cena dobowa musi być dodatnia, do 99999999.99.');
    }
    $type = a_id(pole_tekstowe('typ'));
    a_record('TypyPokoi', 'ID_Typu', $type);
    $status = a_enum('status', ['Wolny','Zajęty','Serwis']);
    $active = a_enum('aktywny', ['0','1']);
    $photo = pole_tekstowe('zdjecie');
    if ($photo !== '' && (!preg_match('~^assets/[^/]+\.(jpe?g|png|webp)$~iD', $photo) || !gah_asset_file($photo))) {
        throw new DomainException('Wybierz istniejący lokalny obraz.');
    }
    if ($old && ($status === 'Serwis' || $cap !== (int)$old['IloscOsob'] || $type !== (int)$old['ID_Typu'])) {
        if (a_query("SELECT ID_Rezerwacji FROM Rezerwacje WHERE ID_Pokoju=? AND DataDo>NOW() AND Status IN ('Oczekująca','Potwierdzona') LIMIT 1 FOR UPDATE", [$id])->fetchColumn()) {
            throw new DomainException('Pokój ma aktywne pobyty. Nie zmieniaj typu/pojemności ani stanu na Serwis; najpierw rozwiąż rezerwacje.');
        }
    }
    $values = [$nr,$type,$price,$cap,$floor,$status,$active,$photo === '' ? null : $photo];
    if ($old) {
        a_query('UPDATE Pokoje SET NrPokoju=?,ID_Typu=?,CenaDobowa=?,IloscOsob=?,Pietro=?,Status=?,Aktywny=?,Zdjecie_Pokoju=?,Wersja=Wersja+1 WHERE ID_Pokoju=?', array_merge($values,[$id]));
    } else {
        global $pdo;
        a_query('INSERT INTO Pokoje(NrPokoju,ID_Typu,CenaDobowa,IloscOsob,Pietro,Status,Aktywny,Zdjecie_Pokoju) VALUES(?,?,?,?,?,?,?,?)', $values);
        $id = (int)$pdo->lastInsertId();
    }
    a_log($old ? 'Edycja / udostępnienie pokoju' : 'Dodanie pokoju', 'pokoj', $id, $old, ['NrPokoju'=>$nr,'ID_Typu'=>$type,'CenaDobowa'=>$price,'IloscOsob'=>$cap,'Pietro'=>$floor,'Status'=>$status,'Aktywny'=>$active,'Zdjecie_Pokoju'=>$photo]);
});
$edit = ctype_digit(a_get('edit')) ? a_query('SELECT * FROM Pokoje WHERE ID_Pokoju=?', [a_get('edit')])->fetch() : null;
$types = a_query('SELECT ID_Typu,NazwaTypu FROM TypyPokoi ORDER BY NazwaTypu')->fetchAll(PDO::FETCH_KEY_PAIR);
$photos = [''=>'Ilustracja domyślna'];
foreach (glob(__DIR__.'/../../assets/*') as $f) {
    if (preg_match('/\.(jpe?g|png|webp)$/iD', $f)) { $photos['assets/'.basename($f)] = basename($f); }
}
$filter = a_get('stan');
$where = in_array($filter, ['0','1'], true) ? ' WHERE p.Aktywny=?' : '';
$params = $where ? [$filter] : [];
$total = a_query('SELECT COUNT(*) FROM Pokoje p'.$where,$params)->fetchColumn();
[$page,$pages,$limit,$offset] = a_page($total);
$rows = a_query('SELECT p.*,t.NazwaTypu FROM Pokoje p JOIN TypyPokoi t ON t.ID_Typu=p.ID_Typu'.$where." ORDER BY p.NrPokoju LIMIT $limit OFFSET $offset",$params)->fetchAll();
a_start('Pokoje i udostępnianie oferty'); ?>
<p class="hint">Udostępniony pokój pojawia się klientom. Wolny termin zależy dodatkowo od pobytów, blokad oraz stanu Serwis. Wycofanie oferty nie kasuje istniejących rezerwacji.</p>
<section><h2><?= $edit ? 'Edytuj pokój' : 'Dodaj pokój' ?></h2>
<form method="post" class="form-grid"><?= csrf_pole() ?><input type="hidden" name="id" value="<?= (int)($edit['ID_Pokoju'] ?? 0) ?>"><input type="hidden" name="wersja" value="<?= (int)($edit['Wersja'] ?? 0) ?>">
<?php a_input('numer','Numer pokoju',$edit['NrPokoju'] ?? '','number','required min="1" max="99999"');
a_select('typ','Typ pokoju',$types,$edit['ID_Typu'] ?? '');
a_input('cena','Cena dobowa PLN',$edit['CenaDobowa'] ?? '200.00','number','required min="0.01" step="0.01"');
a_input('osoby','Maksymalna liczba osób',$edit['IloscOsob'] ?? 1,'number','required min="1" max="50"');
a_input('pietro','Piętro',$edit['Pietro'] ?? 1,'number','required min="-5" max="200"');
a_select('status','Stan pokoju',['Wolny'=>'Wolny','Zajęty'=>'Zajęty','Serwis'=>'Serwis'],$edit['Status'] ?? 'Wolny');
a_select('aktywny','Udostępnienie',['1'=>'Udostępniony','0'=>'Wycofany z oferty'],$edit['Aktywny'] ?? '1');
a_select('zdjecie','Ilustracja',$photos,$edit['Zdjecie_Pokoju'] ?? ''); ?>
<button name="action" value="save">Zapisz pokój</button><a href="pokoje.php">Nowy formularz</a></form></section>
<form method="get" class="toolbar"><?php a_select('stan','Oferta',[''=>'Wszystkie','1'=>'Udostępnione','0'=>'Wycofane'],$filter); ?><button>Filtruj</button><a href="dostepnosc.php">Sprawdź terminy i blokady</a></form>
<div class="table-wrap"><table><thead><tr><th scope="col">Pokój</th><th scope="col">Cena / miejsca</th><th scope="col">Udostępnienie</th><th scope="col">Operacje</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><?= (int)$r['NrPokoju'] ?> · <?= e($r['NazwaTypu']) ?><br>Piętro <?= (int)$r['Pietro'] ?></td><td><?= e($r['CenaDobowa']) ?> PLN<br><?= (int)$r['IloscOsob'] ?> os.</td><td><?= $r['Aktywny'] ? 'Udostępniony' : 'Wycofany' ?><br><?= e($r['Status']) ?></td><td><a href="?edit=<?= (int)$r['ID_Pokoju'] ?>">Edytuj</a><form method="post" data-confirm="Usunąć pokój bez historii? Jeśli ma pobyty, wycofaj go z oferty."><?= csrf_pole() ?><input type="hidden" name="id" value="<?= (int)$r['ID_Pokoju'] ?>"><input type="hidden" name="wersja" value="<?= (int)$r['Wersja'] ?>"><button class="danger" name="action" value="delete">Usuń</button></form></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="4">Brak pokoi.</td></tr><?php endif; ?></tbody></table></div><?php a_pages($page,$pages); a_end(); ?>
