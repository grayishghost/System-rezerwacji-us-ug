<?php
require_once __DIR__ . '/../../includes/admin.php';
a_post(function () {
    a_enum('action', ['', 'save', 'delete']);
    global $pdo;
    $id = pole_tekstowe('id') === '0' ? 0 : a_id(pole_tekstowe('id'));
    $old = $id ? a_record('Pracownicy', 'ID_Pracownika', a_id($id), true) : null;
    if (pole_tekstowe('action') === 'delete') {
        if (!$old) {
            throw new DomainException('Nie wybrano pracownika.');
        }
        if (a_query('SELECT COUNT(*) FROM Rezerwacje WHERE ID_Pracownika=?', [$id])->fetchColumn() || a_query('SELECT COUNT(*) FROM WizytyUslug WHERE ID_Pracownika=?', [$id])->fetchColumn()) {
            throw new DomainException('Pracownik ma historię rezerwacji. Dezaktywuj profil zamiast usuwać.');
        }
        a_query('DELETE FROM Pracownicy WHERE ID_Pracownika=?', [$id]);
        a_query("UPDATE Uzytkownicy SET Rola='klient' WHERE ID_Uzytkownika=?", [$old['ID_Uzytkownika']]);
        a_log('Usunięcie profilu', 'pracownik', $id, ['ID_Uzytkownika' => $old['ID_Uzytkownika']], null);
        return;
    }
    $position = pole_tekstowe('stanowisko');
    if ($position !== '') {
        a_record('Stanowiska', 'ID_Stanowiska', a_id($position));
    } else {
        $position = null;
    }
    $desc = a_text('opis', 3000, false);
    $active = a_enum('aktywny', ['0','1']);
    if ($old) {
        $uid = $old['ID_Uzytkownika'];
        a_record('Uzytkownicy', 'ID_Uzytkownika', $uid, true);
    } else {
        $existing = pole_tekstowe('konto');
        if ($existing !== '') {
            $user = a_record('Uzytkownicy', 'ID_Uzytkownika', a_id($existing), true);
            if ($user['Rola'] !== 'klient' || !$user['Aktywny']) {
                throw new DomainException('Wybierz aktywne konto klienta albo utwórz nowe konto.');
            }
            if (a_query('SELECT COUNT(*) FROM Pracownicy WHERE ID_Uzytkownika=?', [$user['ID_Uzytkownika']])->fetchColumn()) {
                throw new DomainException('Konto ma już profil.');
            }
            $uid = $user['ID_Uzytkownika'];
        } else {
            $imie = a_text('imie', 50);
            $nazwisko = a_text('nazwisko', 50);
            $phone = a_text('telefon', 20);
            $errors = sprawdz_dane_osobowe($imie, $nazwisko, $phone);
            $email = a_text('email', 100);
            $password = pole_tekstowe('haslo');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Niepoprawny e-mail.';
            }
            if ($err = sprawdz_haslo($password)) {
                $errors[] = $err;
            }
            if ($errors) {
                throw new DomainException(implode(' ', $errors));
            }
            a_query("INSERT INTO Uzytkownicy(Imie,Nazwisko,Email,Telefon,Haslo,Rola) VALUES(?,?,?,?,?,'pracownik')", [$imie,$nazwisko,$email,$phone,password_hash($password, PASSWORD_DEFAULT)]);
            $uid = (int)$pdo->lastInsertId();
        }
        a_query("UPDATE Uzytkownicy SET Rola='pracownik' WHERE ID_Uzytkownika=?", [$uid]);
    }
    if ($old) {
        a_query('UPDATE Pracownicy SET ID_Stanowiska=?,Opis=?,Aktywny=? WHERE ID_Pracownika=?', [$position,$desc,$active,$id]);
        $imie = a_text('imie', 50);
        $nazwisko = a_text('nazwisko', 50);
        $phone = a_text('telefon', 20);
        if ($errors = sprawdz_dane_osobowe($imie, $nazwisko, $phone)) {
            throw new DomainException(implode(' ', $errors));
        }
        a_query('UPDATE Uzytkownicy SET Imie=?,Nazwisko=?,Telefon=? WHERE ID_Uzytkownika=?', [$imie,$nazwisko,$phone,$uid]);
    } else {
        a_query('INSERT INTO Pracownicy(ID_Uzytkownika,ID_Stanowiska,Opis,Aktywny) VALUES(?,?,?,?)', [$uid,$position,$desc,$active]);
        $id = (int)$pdo->lastInsertId();
    }
    a_log($old ? 'Edycja' : 'Dodanie', 'pracownik', $id, $old ? ['Aktywny' => $old['Aktywny'],'ID_Stanowiska' => $old['ID_Stanowiska']] : null, ['Aktywny' => $active,'ID_Stanowiska' => $position,'ID_Uzytkownika' => $uid]);
});
$edit = ctype_digit(a_get('edit')) ? a_query('SELECT p.*,u.Imie,u.Nazwisko,u.Telefon FROM Pracownicy p JOIN Uzytkownicy u ON u.ID_Uzytkownika=p.ID_Uzytkownika WHERE p.ID_Pracownika=?', [a_get('edit')])->fetch() : null;
$positions = a_query('SELECT ID_Stanowiska,Nazwa_stanowiska FROM Stanowiska ORDER BY Nazwa_stanowiska')->fetchAll(PDO::FETCH_KEY_PAIR);
$clients = a_query("SELECT u.ID_Uzytkownika,CONCAT(u.Imie,' ',u.Nazwisko,' · ',u.Email) FROM Uzytkownicy u LEFT JOIN Pracownicy p ON p.ID_Uzytkownika=u.ID_Uzytkownika WHERE u.Rola='klient' AND u.Aktywny=1 AND p.ID_Pracownika IS NULL ORDER BY u.Nazwisko")->fetchAll(PDO::FETCH_KEY_PAIR);
$q = mb_substr(a_get('q'), 0, 100);
$params = $q === '' ? [] : ['%'.$q.'%','%'.$q.'%'];
$where = $q === '' ? '' : ' WHERE (u.Imie LIKE ? OR u.Nazwisko LIKE ?)';
$from = ' FROM Pracownicy p JOIN Uzytkownicy u ON u.ID_Uzytkownika=p.ID_Uzytkownika LEFT JOIN Stanowiska s ON s.ID_Stanowiska=p.ID_Stanowiska';
$count = a_query('SELECT COUNT(*)'.$from.$where, $params)->fetchColumn();
[$page,$pages,$limit,$offset] = a_page($count);
$rows = a_query('SELECT p.*,u.Imie,u.Nazwisko,u.Aktywny AS KontoAktywne,s.Nazwa_stanowiska'.$from.$where." ORDER BY u.Nazwisko LIMIT $limit OFFSET $offset", $params)->fetchAll();
a_start('Pracownicy'); ?>
<section><h2><?= $edit ? 'Edycja pracownika' : 'Dodaj pracownika' ?></h2>
<p class="hint">Aktywność profilu oznacza dostępność do przydziału pracy. Blokada logowania jest niezależna i znajduje się w module Konta.</p>
<form method="post" class="form-grid"><?php a_hidden($edit['ID_Pracownika'] ?? 0);
if (!$edit) {
    a_select('konto', 'Istniejące konto (lub nowe)', ['' => 'Utwórz nowe konto — uzupełnij dane poniżej'] + $clients);
}
a_input('imie', 'Imię (dla nowego konta / edycji)', $edit['Imie'] ?? '', 'text', 'maxlength="50"');
a_input('nazwisko', 'Nazwisko', $edit['Nazwisko'] ?? '', 'text', 'maxlength="50"');
a_input('telefon', 'Telefon', $edit['Telefon'] ?? '', 'tel', 'maxlength="20"');
if (!$edit) {
    a_input('email', 'E-mail nowego konta', '', 'email', 'maxlength="100"');
    a_input('haslo', 'Hasło nowego konta', '', 'password', 'autocomplete="new-password" maxlength="72"');
}
a_select('stanowisko', 'Stanowisko', ['' => 'Bez stanowiska'] + $positions, $edit['ID_Stanowiska'] ?? '');
a_select('aktywny', 'Profil pracownika', ['1' => 'Aktywny','0' => 'Nieaktywny'], $edit['Aktywny'] ?? '1'); ?>
<label class="wide">Opis kompetencji<textarea name="opis" maxlength="3000"><?= e($edit['Opis'] ?? '') ?></textarea></label>
<button name="action" value="save">Zapisz pracownika</button><a href="pracownicy.php">Nowy formularz</a></form></section>
<form method="get" class="toolbar"><?php a_input('q', 'Imię lub nazwisko', $q); ?><button>Szukaj</button></form>
<div class="table-wrap"><table><thead><tr><th scope="col">Pracownik</th><th scope="col">Stanowisko</th><th scope="col">Profil / konto</th><th scope="col">Operacje</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['Imie'].' '.$r['Nazwisko']) ?><br><small><?= e($r['Opis']) ?></small></td><td><?= e($r['Nazwa_stanowiska'] ?? '—') ?></td><td><?= $r['Aktywny'] ? 'Aktywny profil' : 'Nieaktywny profil' ?><br><?= $r['KontoAktywne'] ? 'Logowanie dostępne' : 'Konto zablokowane' ?></td><td><div class="actions"><a href="?edit=<?= (int)$r['ID_Pracownika'] ?>">Edytuj</a><a href="przypisania.php?pracownik=<?= (int)$r['ID_Pracownika'] ?>">Usługi</a><a href="grafik.php?pracownik=<?= (int)$r['ID_Pracownika'] ?>">Grafik</a><?php a_delete($r['ID_Pracownika'], 'Usuń profil'); ?></div></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="4" class="empty">Brak pracowników.</td></tr><?php endif; ?></tbody></table></div><?php a_pages($page, $pages);
a_end(); ?>
