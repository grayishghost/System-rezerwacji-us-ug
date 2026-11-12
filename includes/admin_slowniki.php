<?php
require_once __DIR__.'/admin.php';
function as_view($kind)
{
    $cfg=['stanowiska'=>['Stanowiska','ID_Stanowiska','Nazwa_stanowiska','Pracownicy','ID_Stanowiska','Stanowiska pracowników','stanowisko'],'typy'=>['TypyPokoi','ID_Typu','NazwaTypu','Pokoje','ID_Typu','Typy pokoi','typ_pokoju']];
    if (!isset($cfg[$kind])) { throw new DomainException('Nieznany słownik.'); }
    [$table,$pk,$nameCol,$ref,$refCol,$title,$object]=$cfg[$kind];
    a_post(function () use($table,$pk,$nameCol,$ref,$refCol,$kind,$object) {
        global $pdo;
        $action=a_enum('action',['save','delete']); $id=pole_tekstowe('id')==='0'?0:a_id(pole_tekstowe('id'));
        $old=$id?a_record($table,$pk,$id,true):null;
        if ($old) { a_version($old); }
        if ($action==='delete') {
            if (!$old) { throw new DomainException('Wybierz rekord.'); }
            if (a_query("SELECT $refCol FROM $ref WHERE $refCol=? LIMIT 1",[$id])->fetchColumn()) { throw new DomainException('Rekord jest używany. Najpierw przypisz pracownikom inne stanowisko lub pokojom inny typ.'); }
            a_query("DELETE FROM $table WHERE $pk=?",[$id]); a_log('Usunięcie',$object,$id,$old,null); return;
        }
        $name=a_text('nazwa',50);
        if ($kind==='stanowiska') {
            $salary=str_replace(',','.',pole_tekstowe('placa'));
            if (!preg_match('/^(0|[1-9][0-9]{0,7})(\.[0-9]{1,2})?$/D',$salary)) { throw new DomainException('Podaj nieujemną płacę, do dwóch miejsc po przecinku.'); }
            $values=[$name,$salary]; $fields='Nazwa_stanowiska,Placa_brutto'; $set='Nazwa_stanowiska=?,Placa_brutto=?';
        } else {
            $desc=a_text('opis',5000,false); $values=[$name,$desc]; $fields='NazwaTypu,Opis'; $set='NazwaTypu=?,Opis=?';
        }
        if (a_query("SELECT $pk FROM $table WHERE $nameCol=? AND $pk<>? LIMIT 1",[$name,$id])->fetchColumn()) { throw new DomainException('Taka nazwa już istnieje.'); }
        if ($old) { a_query("UPDATE $table SET $set,Wersja=Wersja+1 WHERE $pk=?",array_merge($values,[$id])); }
        else { a_query("INSERT INTO $table($fields) VALUES(?,?)",$values); $id=(int)$pdo->lastInsertId(); }
        a_log($old?'Edycja':'Dodanie',$object,$id,$old,['Nazwa'=>$name]);
    });
    $edit=ctype_digit(a_get('edit'))?a_query("SELECT * FROM $table WHERE $pk=?",[a_get('edit')])->fetch():null;
    $rows=a_query("SELECT t.*,(SELECT COUNT(*) FROM $ref r WHERE r.$refCol=t.$pk) AS Ile FROM $table t ORDER BY t.$nameCol")->fetchAll();
    a_start($title); ?>
    <section><h2><?= $edit?'Edytuj':'Dodaj' ?> <?= $kind==='stanowiska'?'stanowisko':'typ pokoju' ?></h2><form method="post" class="form-grid"><?php a_hidden($edit[$pk]??0); ?><input type="hidden" name="wersja" value="<?= (int)($edit['Wersja']??0) ?>">
    <?php a_input('nazwa','Nazwa',$edit[$nameCol]??'','text','required maxlength="50"');
    if ($kind==='stanowiska') { a_input('placa','Płaca brutto PLN',$edit['Placa_brutto']??'0.00','number','required min="0" max="99999999.99" step="0.01"'); }
    else { ?><label>Opis<textarea name="opis" maxlength="5000"><?= e($edit['Opis']??'') ?></textarea></label><?php } ?>
    <button name="action" value="save">Zapisz</button><a href="<?= e(a_url(['edit'=>null])) ?>">Nowy formularz</a></form></section>
    <div class="table-wrap"><table><thead><tr><th>Nazwa</th><th><?= $kind==='stanowiska'?'Płaca brutto':'Opis' ?></th><th>Powiązania</th><th>Operacje</th></tr></thead><tbody>
    <?php foreach ($rows as $row): ?><tr><td><?= e($row[$nameCol]) ?></td><td><?= e($kind==='stanowiska'?$row['Placa_brutto'].' PLN':$row['Opis']) ?></td><td><?= (int)$row['Ile'] ?></td><td><div class="actions"><a href="<?= e(a_url(['edit'=>$row[$pk]])) ?>">Edytuj</a><form method="post" data-confirm="Usunąć nieużywany rekord?"><?php a_hidden($row[$pk]); ?><input type="hidden" name="wersja" value="<?= (int)$row['Wersja'] ?>"><button class="danger" name="action" value="delete">Usuń</button></form></div></td></tr><?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="4">Brak rekordów.</td></tr><?php endif; ?></tbody></table></div><p class="hint">Używanego rekordu nie usuwamy automatycznie. Najpierw zmień jego przypisania.</p><?php a_end();
}
