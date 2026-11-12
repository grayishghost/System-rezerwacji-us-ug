<?php
require_once __DIR__ . '/admin.php';
a_post(function () {
    a_enum('action', ['', 'save', 'delete']);
    $id = pole_tekstowe('id') === '0' ? 0 : a_id(pole_tekstowe('id'));
    if (pole_tekstowe('action') === 'delete') {
        $old = a_record('KategorieUslug', 'ID_Kategorii', a_id($id), true);
        if (a_query('SELECT COUNT(*) FROM Uslugi_dodatkowe WHERE ID_Kategorii=?', [$id])->fetchColumn()) {
            throw new DomainException('Kategoria ma usługi. Przenieś je do innej kategorii przed usunięciem.');
        }
        a_query('DELETE FROM KategorieUslug WHERE ID_Kategorii=?', [$id]);
        a_log('Usunięcie', 'kategoria', $id, $old, null);
        return;
    }
    $name = a_text('nazwa', 50);
    $desc = a_text('opis', 3000, false);
    $old = $id ? a_record('KategorieUslug', 'ID_Kategorii', a_id($id), true) : null;
    if ($id) {
        a_query('UPDATE KategorieUslug SET Nazwa=?,Opis=? WHERE ID_Kategorii=?', [$name,$desc,$id]);
    } else {
        a_query('INSERT INTO KategorieUslug(Nazwa,Opis) VALUES(?,?)', [$name,$desc]);
        global $pdo;
        $id = (int)$pdo->lastInsertId();
    }
    a_log($old ? 'Edycja' : 'Dodanie', 'kategoria', $id, $old, ['Nazwa' => $name,'Opis' => $desc]);
});
$edit = null;
if (ctype_digit(a_get('edit'))) {
    $edit = a_query('SELECT * FROM KategorieUslug WHERE ID_Kategorii=?', [a_get('edit')])->fetch();
}
$q = mb_substr(a_get('q'), 0, 100);
$where = $q === '' ? '' : ' WHERE k.Nazwa LIKE ?';
$params = $q === '' ? [] : ['%'.$q.'%'];
$count = a_query('SELECT COUNT(*) FROM KategorieUslug k'.$where, $params)->fetchColumn();
[$page,$pages,$limit,$offset] = a_page($count);
$rows = a_query('SELECT k.*, (SELECT COUNT(*) FROM Uslugi_dodatkowe u WHERE u.ID_Kategorii=k.ID_Kategorii) AS Ile FROM KategorieUslug k'.$where." ORDER BY k.Nazwa LIMIT $limit OFFSET $offset", $params)->fetchAll();
a_start('Kategorie usług');
?>
<section><h2><?= $edit ? 'Edycja kategorii' : 'Nowa kategoria' ?></h2>
<form method="post" class="form-grid"><?php a_hidden($edit['ID_Kategorii'] ?? 0);
a_input('nazwa', 'Nazwa', $edit['Nazwa'] ?? '', 'text', 'required maxlength="50"'); ?>
<label>Opis<textarea name="opis" maxlength="3000"><?= e($edit['Opis'] ?? '') ?></textarea></label>
<button name="action" value="save">Zapisz kategorię</button><a href="<?= e(a_url(['edit'=>null,'q'=>null,'page'=>null])) ?>">Nowy formularz</a></form></section>
<form method="get" class="toolbar"><input type="hidden" name="sekcja" value="kategorie"><?php a_input('q', 'Szukaj kategorii', $q); ?><button>Szukaj</button><a href="<?= e(a_url(['edit'=>null,'q'=>null,'page'=>null])) ?>">Wyczyść</a></form>
<p class="muted">Wyników: <?= (int)$count ?>. Usunięcie kategorii nie usuwa usług.</p>
<div class="table-wrap"><table><thead><tr><th scope="col">Nazwa</th><th scope="col">Opis</th><th scope="col">Usługi</th><th scope="col">Operacje</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['Nazwa']) ?></td><td><?= e($r['Opis']) ?></td><td><?= (int)$r['Ile'] ?></td><td><div class="actions"><a href="<?= e(a_url(['edit'=>$r['ID_Kategorii']])) ?>">Edytuj</a><?php a_delete($r['ID_Kategorii']); ?></div></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="4" class="empty">Brak kategorii spełniających kryteria.</td></tr><?php endif; ?>
</tbody></table></div><?php a_pages($page, $pages);
a_end(); ?>
