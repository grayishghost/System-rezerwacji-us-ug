<?php
require_once __DIR__ . '/admin.php';
a_post(function () {
    $id = a_id(pole_tekstowe('id'));
    a_record('Pracownicy', 'ID_Pracownika', $id, true);
    $ids = $_POST['uslugi'] ?? [];
    if (!is_array($ids) || count($ids) > 1000) {
        throw new DomainException('Niepoprawna lista usług.');
    }
    $ids = array_values(array_unique(array_map('a_id', $ids)));
    sort($ids);
    foreach ($ids as $sid) {
        a_record('Uslugi_dodatkowe', 'ID_Uslugi', $sid);
    }
    $old = a_query('SELECT ID_Uslugi FROM Pracownicy_Uslugi WHERE ID_Pracownika=? ORDER BY ID_Uslugi', [$id])->fetchAll(PDO::FETCH_COLUMN);
    $upcoming = a_query("SELECT DISTINCT ID_Uslugi FROM WizytyUslug WHERE ID_Pracownika=? AND DataDo>NOW() AND Status IN ('Oczekująca','Potwierdzona')", [$id])->fetchAll(PDO::FETCH_COLUMN);
    if (array_diff($upcoming, $ids)) {
        throw new DomainException('Nie można odpiąć usługi potrzebnej do aktywnej przyszłej wizyty. Najpierw rozwiąż tę wizytę.');
    }
    a_query('DELETE FROM Pracownicy_Uslugi WHERE ID_Pracownika=?', [$id]);
    foreach ($ids as $sid) {
        a_query('INSERT INTO Pracownicy_Uslugi(ID_Pracownika,ID_Uslugi) VALUES(?,?)', [$id,$sid]);
    }
    a_log('Przypisanie usług', 'pracownik', $id, $old, $ids);
});
$employees = a_query("SELECT p.ID_Pracownika,CONCAT(u.Imie,' ',u.Nazwisko) FROM Pracownicy p JOIN Uzytkownicy u ON u.ID_Uzytkownika=p.ID_Uzytkownika ORDER BY u.Nazwisko")->fetchAll(PDO::FETCH_KEY_PAIR);
$id = ctype_digit(a_get('pracownik')) ? (int)a_get('pracownik') : (int)(array_key_first($employees) ?? 0);
$assigned = a_query('SELECT ID_Uslugi FROM Pracownicy_Uslugi WHERE ID_Pracownika=?', [$id])->fetchAll(PDO::FETCH_COLUMN);
$services = a_query('SELECT ID_Uslugi,Nazwa_Uslugi,Czydostepna FROM Uslugi_dodatkowe ORDER BY Nazwa_Uslugi')->fetchAll();
a_start('Kompetencje pracowników'); ?>
<form method="get" class="toolbar"><input type="hidden" name="sekcja" value="kompetencje"><?php a_select('pracownik', 'Pracownik', $employees, $id); ?><button>Pokaż</button></form>
<?php if (isset($employees[$id])): ?><section><h2><?= e($employees[$id]) ?></h2><p>Pracownik może obsługiwać wiele usług, każda usługa może mieć wielu pracowników.</p><form method="post"><?php a_hidden($id); ?>
<?php foreach ($services as $s): ?><label class="check"><input type="checkbox" name="uslugi[]" value="<?= (int)$s['ID_Uslugi'] ?>" <?= in_array($s['ID_Uslugi'], $assigned) ? 'checked' : '' ?>><?= e($s['Nazwa_Uslugi']) ?> <?= !$s['Czydostepna'] ? '(nieaktywna)' : '' ?></label><?php endforeach; ?>
<button>Zapisz przypisania</button></form></section><?php else: ?><p>Dodaj pracownika, aby przypisać usługi.</p><?php endif;
a_end(); ?>
