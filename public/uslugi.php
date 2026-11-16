<?php
require_once __DIR__.'/../includes/terminarz_widok.php';
t_ready();
$category=t_get('kategoria');$args=[];$where=' WHERE s.Czydostepna=1';$error='';
try { if ($category!=='') { $where.=' AND s.ID_Kategorii=?';$args[]=t_id($category); } }
catch (DomainException $ex) { $error=$ex->getMessage();$where.=' AND 1=0'; }
$rows=t_q('SELECT s.*,k.Nazwa AS Kategoria FROM Uslugi_dodatkowe s JOIN KategorieUslug k ON k.ID_Kategorii=s.ID_Kategorii'.$where.' ORDER BY k.Nazwa,s.Nazwa_Uslugi',$args)->fetchAll();
t_start('Usługi i rezerwacje');
echo '<section class="panel-box"><p>Wybierz usługę, następnie pracownika, datę i wolną godzinę. Rezerwacje usług są niezależne od pobytów w pokojach.</p><form method="get" class="booking-form"><label>Kategoria<select name="kategoria"><option value="">Wszystkie kategorie</option>';
foreach (t_q('SELECT ID_Kategorii,Nazwa FROM KategorieUslug ORDER BY Nazwa')->fetchAll() as $c) { echo '<option value="'.(int)$c['ID_Kategorii'].'"'.($category===(string)$c['ID_Kategorii']?' selected':'').'>'.e($c['Nazwa']).'</option>'; }
echo '</select></label><button>Filtruj ofertę</button></form></section>';
if ($error) { t_error($error); }
echo '<div class="visit-cards">';
foreach ($rows as $s) { echo '<article class="visit-card"><span class="badge">'.e($s['Kategoria']).'</span><h2>'.e($s['Nazwa_Uslugi']).'</h2><p>'.e($s['Opis']).'</p><p><strong>'.e(t_price($s['Cena'])).'</strong> · '.(int)$s['CzasTrwania'].' min</p><p>Dostępna w ofercie — wolne godziny sprawdzisz po wyborze pracownika.</p><a class="booking-button small-button" href="'.e(BASE_URL).'/public/usluga.php?id='.(int)$s['ID_Uslugi'].'">Szczegóły i rezerwacja</a></article>'; }
echo '</div>';if (!$rows) { echo '<p>Brak aktywnych usług w wybranej kategorii.</p>'; }t_end();
