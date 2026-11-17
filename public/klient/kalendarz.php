<?php
require_once __DIR__.'/../../includes/terminarz_widok.php';
wymagaj_roli('klient');t_ready();
$error='';$month=t_get('miesiac',date('Y-m'));
try {
    if (!preg_match('/^[0-9]{4}-[0-9]{2}$/D',$month)) { throw new DomainException('Podaj poprawny miesiąc.'); }
    $first=t_day($month.'-01');
    if ((int)$first->format('Y')<1001 || (int)$first->format('Y')>9997) { throw new DomainException('Miesiąc jest poza obsługiwanym zakresem.'); }
} catch (DomainException $ex) { $error=$ex->getMessage();$first=new DateTimeImmutable('first day of this month');$month=$first->format('Y-m'); }
$next=$first->modify('+1 month');$previous=$first->modify('-1 month');
$rows=t_q(t_select().t_join().' WHERE w.ID_Klienta=? AND w.DataOd>=? AND w.DataOd<? ORDER BY w.DataOd',[$_SESSION['user_id'],$first->format('Y-m-d').' 00:00:00',$next->format('Y-m-d').' 00:00:00'])->fetchAll();$byday=[];
foreach ($rows as $r) { $byday[(int)substr($r['DataOd'],8,2)][]=$r; }
t_start('Mój kalendarz wizyt');
if ($error) { t_error($error); }
echo '<section class="panel-box"><h2>'.e($month).'</h2><div class="booking-nav"><a href="?miesiac='.e($previous->format('Y-m')).'">← Poprzedni miesiąc</a><a href="?miesiac='.date('Y-m').'">Bieżący miesiąc</a><a href="?miesiac='.e($next->format('Y-m')).'">Następny miesiąc →</a></div><form class="booking-form" method="get"><label>Miesiąc<input type="month" name="miesiac" value="'.e($month).'" required></label><button>Pokaż miesiąc</button></form><p class="field-hint">Wyłącznie Twoje wizyty. Na telefonie kalendarz można przesuwać poziomo; dostępna jest też lista poniżej.</p><div class="calendar-scroll" tabindex="0" aria-label="Kalendarz miesięczny"><div class="month-grid">';
foreach (['Pon','Wt','Śr','Czw','Pt','Sob','Ndz'] as $label) { echo '<strong>'.e($label).'</strong>'; }
for ($i=1;$i<(int)$first->format('N');$i++) { echo '<div aria-hidden="true"></div>'; }
for ($d=1;$d<=(int)$first->format('t');$d++) {
    $day=$month.'-'.str_pad((string)$d,2,'0',STR_PAD_LEFT);
    echo '<div class="month-day'.($day===date('Y-m-d')?' today':'').'"><time datetime="'.e($day).'">'.$d.'</time>';
    foreach ($byday[$d]??[] as $r) { echo '<a href="wizyta.php?id='.(int)$r['ID_Wizyty'].'">'.e(substr($r['DataOd'],11,5).' '.$r['Nazwa_Uslugi']).'<br>'.e($r['Status']).'</a>'; }
    echo '</div>';
}
echo '</div></div><h2>Lista wizyt w wybranym miesiącu</h2>';t_cards($rows);echo '</section>';t_end();
