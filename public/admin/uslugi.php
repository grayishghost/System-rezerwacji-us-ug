<?php
require_once __DIR__ . '/../../includes/admin.php';
a_post(function () {
    global $pdo;
    $id = pole_tekstowe('id') === '0' ? 0 : a_id(pole_tekstowe('id'));
    $old = $id ? a_record('Uslugi_dodatkowe', 'ID_Uslugi', a_id($id), true) : null;
    if (pole_tekstowe('action') === 'delete') {
        if (!$old) {
            throw new DomainException('Nie wybrano usługi.');
        }
        if (a_query('SELECT COUNT(*) FROM ZamowieniaUslugi WHERE ID_Uslugi=?', [$id])->fetchColumn() || a_query('SELECT COUNT(*) FROM WizytyUslug WHERE ID_Uslugi=?', [$id])->fetchColumn()) {
            throw new DomainException('Usługa występuje w zamówieniach pobytu lub wizytach. Zamiast usuwania ustaw ją jako nieaktywną.');
        }
        a_query('DELETE FROM Uslugi_dodatkowe WHERE ID_Uslugi=?', [$id]);
        a_log('Usunięcie', 'usluga', $id, $old, null);
        return;
    }
    $name = a_text('nazwa', 100);
    $desc = a_text('opis', 5000, false);
    $price = str_replace(',', '.', pole_tekstowe('cena'));
    if (!preg_match('/^(0|[1-9][0-9]{0,7})(\.[0-9]{1,2})?$/D', $price)) {
        throw new DomainException('Cena: od 0 do 99999999.99, maksymalnie dwa miejsca po przecinku.');
    }
    $duration = filter_var(pole_tekstowe('czas'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1,'max_range' => 1440]]);
    if (!$duration) {
        throw new DomainException('Czas trwania: od 1 do 1440 minut.');
    }
    $cat = a_record('KategorieUslug', 'ID_Kategorii', a_id(pole_tekstowe('kategoria')), true);
    $active = a_enum('aktywna', ['0','1']);
    $data = [$name,$desc,$price,$active,$cat['ID_Kategorii'],$duration];
    if ($old) {
        a_query('UPDATE Uslugi_dodatkowe SET Nazwa_Uslugi=?,Opis=?,Cena=?,Czydostepna=?,ID_Kategorii=?,CzasTrwania=? WHERE ID_Uslugi=?', array_merge($data, [$id]));
    } else {
        a_query('INSERT INTO Uslugi_dodatkowe(Nazwa_Uslugi,Opis,Cena,Czydostepna,ID_Kategorii,CzasTrwania) VALUES(?,?,?,?,?,?)', $data);
        $id = (int)$pdo->lastInsertId();
    }
    a_log($old ? 'Edycja' : 'Dodanie', 'usluga', $id, $old, ['Nazwa' => $name,'Cena' => $price,'CzasTrwania' => $duration,'ID_Kategorii' => $cat['ID_Kategorii'],'Aktywna' => $active]);
});
$edit = ctype_digit(a_get('edit')) ? a_query('SELECT * FROM Uslugi_dodatkowe WHERE ID_Uslugi=?', [a_get('edit')])->fetch() : null;
$cats = a_query('SELECT ID_Kategorii,Nazwa FROM KategorieUslug ORDER BY Nazwa')->fetchAll(PDO::FETCH_KEY_PAIR);
$q = mb_substr(a_get('q'), 0, 100);
$cat = a_get('kat');
$state = a_get('stan');
$where = ['1=1'];
$params = [];
if ($q !== '') {
    $where[] = 'u.Nazwa_Uslugi LIKE ?';
    $params[] = '%'.$q.'%';
}
if (ctype_digit($cat)) {
    $where[] = 'u.ID_Kategorii=?';
    $params[] = (int)$cat;
}
if (in_array($state, ['0','1'], true)) {
    $where[] = 'u.Czydostepna=?';
    $params[] = $state;
}
$where = ' WHERE '.implode(' AND ', $where);
$count = a_query('SELECT COUNT(*) FROM Uslugi_dodatkowe u'.$where, $params)->fetchColumn();
[$page,$pages,$limit,$offset] = a_page($count);
$rows = a_query('SELECT u.*,k.Nazwa AS NazwaKategorii FROM Uslugi_dodatkowe u JOIN KategorieUslug k ON k.ID_Kategorii=u.ID_Kategorii'.$where." ORDER BY u.ID_Uslugi DESC LIMIT $limit OFFSET $offset", $params)->fetchAll();
a_start('Usługi dodatkowe'); ?>
<section><h2><?= $edit ? 'Edycja usługi' : 'Nowa usługa' ?></h2>
<?php if (!$cats): ?><p>Najpierw <a href="kategorie.php">dodaj kategorię</a>.</p><?php else: ?>
<form method="post" class="form-grid"><?php a_hidden($edit['ID_Uslugi'] ?? 0);
    a_input('nazwa', 'Nazwa', $edit['Nazwa_Uslugi'] ?? '', 'text', 'required maxlength="100"');
    a_select('kategoria', 'Kategoria', $cats, $edit['ID_Kategorii'] ?? '');
    a_input('cena', 'Cena (PLN)', $edit['Cena'] ?? '0.00', 'number', 'required min="0" max="99999999.99" step="0.01"');
    a_input('czas', 'Czas trwania (min)', $edit['CzasTrwania'] ?? 30, 'number', 'required min="1" max="1440"');
    a_select('aktywna', 'Dostępność', ['1' => 'Aktywna','0' => 'Nieaktywna'], $edit['Czydostepna'] ?? '1'); ?>
<label class="wide">Opis<textarea name="opis" maxlength="5000"><?= e($edit['Opis'] ?? '') ?></textarea></label><button name="action" value="save">Zapisz usługę</button><a href="uslugi.php">Nowy formularz</a></form><?php endif; ?></section>
<form method="get" class="toolbar"><?php a_input('q', 'Nazwa usługi', $q);
a_select('kat', 'Kategoria', ['' => 'Wszystkie'] + $cats, $cat);
a_select('stan', 'Dostępność', ['' => 'Wszystkie','1' => 'Aktywne','0' => 'Nieaktywne'], $state); ?><button>Filtruj</button><a href="uslugi.php">Wyczyść</a></form>
<p class="muted">Wyników: <?= (int)$count ?>. Zmiana ceny nie zmienia historycznej ceny zamówień.</p>
<div class="table-wrap"><table><thead><tr><th>Usługa / kategoria</th><th>Cena</th><th>Czas</th><th>Status</th><th>Operacje</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><strong><?= e($r['Nazwa_Uslugi']) ?></strong><br><?= e($r['NazwaKategorii']) ?></td><td><?= e(number_format((float)$r['Cena'], 2, ',', ' ')) ?> PLN</td><td><?= (int)$r['CzasTrwania'] ?> min</td><td><span class="badge"><?= $r['Czydostepna'] ? 'Aktywna' : 'Nieaktywna' ?></span></td><td><div class="actions"><a href="?edit=<?= (int)$r['ID_Uslugi'] ?>">Edytuj</a><?php a_delete($r['ID_Uslugi']); ?></div></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="5" class="empty">Brak usług.</td></tr><?php endif; ?></tbody></table></div><?php a_pages($page, $pages);
a_end(); ?>
