<?php
require_once __DIR__ . '/../../includes/admin.php';
$object = a_get('obiekt');
$q = mb_substr(a_get('q'), 0, 80);
$where = ['1=1'];
$params = [];
if (in_array($object, ['konto','pracownik','grafik','kategoria','usluga','rezerwacja','wizyta','pokoj','blokada','stanowisko','typ_pokoju'], true)) {
    $where[] = 'l.Obiekt=?';
    $params[] = $object;
}
if ($q !== '') {
    $where[] = 'l.Akcja LIKE ?';
    $params[] = '%'.$q.'%';
}
$where = ' WHERE '.implode(' AND ', $where);
$count = a_query('SELECT COUNT(*) FROM LogAdministratora l'.$where, $params)->fetchColumn();
[$page,$pages,$limit,$offset] = a_page($count, 20);
$rows = a_query("SELECT l.*,CONCAT(u.Imie,' ',u.Nazwisko) AS Admin FROM LogAdministratora l LEFT JOIN Uzytkownicy u ON u.ID_Uzytkownika=l.ID_Admina".$where." ORDER BY l.ID_Logu DESC LIMIT $limit OFFSET $offset", $params)->fetchAll();
a_start('Historia operacji administratorów'); ?>
<p class="hint">Dziennik operacji wykonanych przez nowy panel. Brak przycisku edycji/usuwania. Nie jest to zabezpieczony kryptograficznie audyt i nie rejestruje ręcznych zmian SQL. Nie przechowuje haseł ani danych kontaktowych klientów.</p>
<form method="get" class="toolbar"><?php a_input('q', 'Nazwa operacji', $q);
a_select('obiekt', 'Moduł', ['' => 'Wszystkie','konto' => 'Konta','pracownik' => 'Pracownicy','grafik' => 'Grafik','kategoria' => 'Kategorie','usluga' => 'Usługi','rezerwacja' => 'Pobyty','wizyta' => 'Wizyty usług','pokoj'=>'Pokoje','blokada'=>'Blokady pokoi','stanowisko'=>'Stanowiska','typ_pokoju'=>'Typy pokoi'], $object); ?><button>Filtruj</button></form>
<div class="table-wrap"><table><thead><tr><th scope="col">Czas / administrator</th><th scope="col">Operacja</th><th scope="col">Obiekt</th><th scope="col">Zmiana</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['Czas']) ?><br><?= e($r['Admin'] ?? 'Usunięte konto') ?></td><td><?= e($r['Akcja']) ?></td><td><?= e($r['Obiekt']) ?> #<?= (int)$r['ID_Obiektu'] ?></td><td><details><summary>Przed / po</summary><strong>Przed</strong><pre><?= e($r['Przed'] ?? '—') ?></pre><strong>Po</strong><pre><?= e($r['Po'] ?? '—') ?></pre></details></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="4" class="empty">Brak operacji.</td></tr><?php endif; ?></tbody></table></div><?php a_pages($page, $pages);
a_end(); ?>
