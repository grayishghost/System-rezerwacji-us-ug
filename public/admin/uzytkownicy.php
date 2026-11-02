<?php
require_once __DIR__ . '/../../includes/admin.php';
a_post(function () {

    a_query("SELECT ID_Uzytkownika FROM Uzytkownicy WHERE Rola='admin' ORDER BY ID_Uzytkownika FOR UPDATE")->fetchAll();
    $id = a_id(pole_tekstowe('id'));
    $old = a_record('Uzytkownicy', 'ID_Uzytkownika', $id, true);
    $role = a_enum('rola', ['klient','pracownik','admin']);
    $active = a_enum('aktywny', ['0','1']);
    if ($id === (int)$_SESSION['user_id'] && ($role !== 'admin' || $active !== '1')) {
        throw new DomainException('Nie możesz odebrać sobie uprawnień ani zablokować własnego konta.');
    }
    $employee = a_query('SELECT ID_Pracownika FROM Pracownicy WHERE ID_Uzytkownika=?', [$id])->fetchColumn();
    if ($employee && $role !== 'pracownik') {
        throw new DomainException('Konto ma profil pracownika. Pozostaw rolę pracownik; nieaktywnego pracownika dezaktywuj w module Pracownicy.');
    }
    if (!$employee && $role === 'pracownik') {
        throw new DomainException('Utwórz profil w module Pracownicy — tam przypiszesz konto do pracownika.');
    }
    if ($old['Rola'] === 'admin' && ($role !== 'admin' || $active === '0')) {
        $n = a_query("SELECT COUNT(*) FROM Uzytkownicy WHERE Rola='admin' AND Aktywny=1")->fetchColumn();
        if ($old['Aktywny'] && $n <= 1) {
            throw new DomainException('W systemie musi pozostać aktywny administrator.');
        }
    }
    a_query('UPDATE Uzytkownicy SET Rola=?,Aktywny=? WHERE ID_Uzytkownika=?', [$role,$active,$id]);
    a_log('Konto: rola / blokada', 'konto', $id, ['Rola' => $old['Rola'],'Aktywny' => $old['Aktywny']], ['Rola' => $role,'Aktywny' => $active]);
});
$q = mb_substr(a_get('q'), 0, 100);
$role = a_get('rola');
$where = ['1=1'];
$params = [];
if ($q !== '') {
    $where[] = '(Imie LIKE ? OR Nazwisko LIKE ? OR Email LIKE ?)';
    $params = ['%'.$q.'%','%'.$q.'%','%'.$q.'%'];
}
if (in_array($role, ['klient','pracownik','admin'], true)) {
    $where[] = 'Rola=?';
    $params[] = $role;
}
$where = ' WHERE '.implode(' AND ', $where);
$count = a_query('SELECT COUNT(*) FROM Uzytkownicy'.$where, $params)->fetchColumn();
[$page,$pages,$limit,$offset] = a_page($count);
$rows = a_query('SELECT ID_Uzytkownika,Imie,Nazwisko,Email,Rola,Aktywny FROM Uzytkownicy'.$where." ORDER BY ID_Uzytkownika LIMIT $limit OFFSET $offset", $params)->fetchAll();
a_start('Konta użytkowników'); ?>
<p class="hint">Nowego pracownika dodasz w module Pracownicy. Zablokowanie konta odbiera dostęp przy następnym żądaniu — także z istniejącej sesji.</p>
<form method="get" class="toolbar"><?php a_input('q', 'Imię, nazwisko lub e-mail', $q);
a_select('rola', 'Rola', ['' => 'Wszystkie','klient' => 'Klient','pracownik' => 'Pracownik','admin' => 'Administrator'], $role); ?><button>Szukaj</button><a href="uzytkownicy.php">Wyczyść</a></form>
<p class="muted">Wyników: <?= (int)$count ?></p><div class="table-wrap"><table><thead><tr><th>Użytkownik</th><th>E-mail</th><th>Uprawnienia i blokada</th></tr></thead><tbody>
<?php foreach ($rows as $u): ?><tr><td>#<?= (int)$u['ID_Uzytkownika'] ?> <?= e($u['Imie'].' '.$u['Nazwisko']) ?></td><td><?= e($u['Email']) ?></td><td><form method="post" class="toolbar" data-confirm="Zmienić rolę lub dostęp tego konta?"><?php a_hidden($u['ID_Uzytkownika']);
    a_select('rola', 'Rola', ['klient' => 'Klient','pracownik' => 'Pracownik','admin' => 'Administrator'], $u['Rola']);
    a_select('aktywny', 'Konto', ['1' => 'Aktywne','0' => 'Zablokowane'], $u['Aktywny']); ?><button>Zapisz</button></form></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="3" class="empty">Brak kont.</td></tr><?php endif; ?></tbody></table></div><?php a_pages($page, $pages);
a_end(); ?>
