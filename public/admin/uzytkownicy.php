<?php
require_once __DIR__.'/../../includes/admin_konta.php';
a_post('ak_save');
$edit=ctype_digit(a_get('edit'))?a_query('SELECT ID_Uzytkownika,Imie,Nazwisko,Email,Telefon,Rola,Aktywny,Wersja FROM Uzytkownicy WHERE ID_Uzytkownika=?',[a_get('edit')])->fetch():null;
$q=mb_substr(a_get('q'),0,100); $role=a_get('rola'); $where=['1=1']; $params=[];
if ($q!=='') { $where[]='(Imie LIKE ? OR Nazwisko LIKE ? OR Email LIKE ?)'; $params=['%'.$q.'%','%'.$q.'%','%'.$q.'%']; }
if (in_array($role,['klient','pracownik','admin'],true)) { $where[]='Rola=?'; $params[]=$role; }
$where=' WHERE '.implode(' AND ',$where);
$count=a_query('SELECT COUNT(*) FROM Uzytkownicy'.$where,$params)->fetchColumn();
[$page,$pages,$limit,$offset]=a_page($count);
$rows=a_query('SELECT ID_Uzytkownika,Imie,Nazwisko,Email,Telefon,Rola,Aktywny,Wersja FROM Uzytkownicy'.$where." ORDER BY ID_Uzytkownika DESC LIMIT $limit OFFSET $offset",$params)->fetchAll();
a_start('Konta użytkowników'); ?>
<section><h2><?= $edit?'Edytuj konto #'.(int)$edit['ID_Uzytkownika']:'Dodaj konto użytkownika' ?></h2>
<p class="hint">Tutaj zarządzasz klientami, pracownikami i administratorami. Rola pracownik tworzy automatycznie profil; stanowisko i kompetencje ustawisz w module Pracownicy. Konta z historią blokujemy, zamiast je usuwać.</p>
<form method="post" class="form-grid" data-confirm="Zapisać dane i uprawnienia konta?"><?php a_hidden($edit['ID_Uzytkownika']??0); ?><input type="hidden" name="wersja" value="<?= (int)($edit['Wersja']??0) ?>">
<?php a_input('imie','Imię',$edit['Imie']??'','text','required maxlength="50"'); a_input('nazwisko','Nazwisko',$edit['Nazwisko']??'','text','required maxlength="50"'); a_input('email','E-mail',$edit['Email']??'','email','required maxlength="100"'); a_input('telefon','Telefon',$edit['Telefon']??'','tel','required maxlength="20"'); a_select('rola','Rola',['klient'=>'Klient','pracownik'=>'Pracownik','admin'=>'Administrator'],$edit['Rola']??'klient'); a_select('aktywny','Konto',['1'=>'Aktywne','0'=>'Zablokowane'],$edit['Aktywny']??'1'); a_input('haslo',$edit?'Nowe hasło (puste = bez zmiany)':'Hasło','','password',($edit?'':'required ').'autocomplete="new-password" maxlength="72"'); ?>
<button name="action" value="save">Zapisz konto</button><a href="uzytkownicy.php">Nowe konto</a></form></section>
<form method="get" class="toolbar"><?php a_input('q','Imię, nazwisko lub e-mail',$q); a_select('rola','Rola',[''=>'Wszystkie','klient'=>'Klient','pracownik'=>'Pracownik','admin'=>'Administrator'],$role); ?><button>Szukaj</button><a href="uzytkownicy.php">Wyczyść</a></form>
<p>Wyników: <?= (int)$count ?></p><div class="table-wrap"><table><thead><tr><th>Użytkownik</th><th>Kontakt</th><th>Rola / dostęp</th><th>Operacje</th></tr></thead><tbody>
<?php foreach ($rows as $u): ?><tr><td>#<?= (int)$u['ID_Uzytkownika'] ?> <?= e($u['Imie'].' '.$u['Nazwisko']) ?></td><td><?= e($u['Email']) ?><br><?= e($u['Telefon']) ?></td><td><?= e($u['Rola']) ?><br><?= $u['Aktywny']?'Aktywne':'Zablokowane' ?></td><td><div class="actions"><a href="?edit=<?= (int)$u['ID_Uzytkownika'] ?>">Edytuj dane / rolę / hasło</a><?php if ((int)$u['ID_Uzytkownika']!==(int)$_SESSION['user_id']): ?><form method="post" data-confirm="Trwale usunąć konto bez historii? Konto z pobytami lub wizytami musi pozostać w systemie."><?php a_hidden($u['ID_Uzytkownika']); ?><input type="hidden" name="wersja" value="<?= (int)$u['Wersja'] ?>"><button name="action" value="delete" class="danger">Usuń konto</button></form><?php endif; ?></div></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="4">Brak kont.</td></tr><?php endif; ?></tbody></table></div><?php a_pages($page,$pages); a_end(); ?>
