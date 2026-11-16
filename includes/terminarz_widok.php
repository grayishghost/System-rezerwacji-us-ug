<?php
require_once __DIR__.'/terminarz.php';
function t_ready()
{
    try { $ok=t_q("SELECT Nazwa FROM Migracje WHERE Nazwa='008_terminarz'")->fetchColumn(); }
    catch (Throwable $ex) { $ok=false; }
    if (!$ok) { http_response_code(503); exit('Rezerwacje usług wymagają aktualizacji 008. Administrator: sprawdź kontrolę instalacji i instrukcję wdrożenia.'); }
}
function t_start($title)
{
    $tytul=$title; require __DIR__.'/header.php';
    echo '<main class="panel-page"><h1>'.e($title).'</h1>'; pokaz_komunikat();
    echo '<nav class="booking-nav" aria-label="Rezerwacje"><a href="'.e(BASE_URL).'/public/uslugi.php">Oferta usług</a>';
    if (($_SESSION['rola']??'')==='klient') { echo '<a href="'.e(BASE_URL).'/public/klient/index.php">Moje konto i rezerwacje</a>'; }
    if (($_SESSION['rola']??'')==='pracownik') { echo '<a href="'.e(BASE_URL).'/public/pracownik/index.php">Moje wizyty</a>'; }
    echo '</nav>';
}
function t_end()
{
    echo '</main>';require __DIR__.'/footer.php';
}
function t_error($text)
{
    echo '<p class="form-message form-message-error" role="alert">'.e($text).'</p>';
}
function t_price($price)
{
    return number_format((float)$price,2,',',' ').' zł';
}
function t_cards($rows,$worker=false)
{
    if (!$rows) { echo '<p>Brak wizyt w tej sekcji.</p>'; return; }
    echo '<div class="visit-cards">';
    foreach ($rows as $r) {
        echo '<article class="visit-card"><span class="badge">'.e($r['Status']).'</span><h3>'.e($r['Nazwa_Uslugi']).'</h3><p><strong>'.e(substr($r['DataOd'],0,16)).' – '.e(substr($r['DataDo'],11,5)).'</strong></p><p>'.($worker?'Klient: '.e($r['Klient']):'Pracownik: '.e($r['Pracownik'])).'</p><p>'.e(t_price($r['Cena'])).'</p><a class="booking-button small-button" href="'.e(BASE_URL).'/public/'.($worker?'pracownik':'klient').'/wizyta.php?id='.(int)$r['ID_Wizyty'].'">Szczegóły wizyty #'.(int)$r['ID_Wizyty'].'</a></article>';
    }
    echo '</div>';
}
function t_list($worker=false)
{
    $scope=$worker?'p.ID_Uzytkownika=?':'w.ID_Klienta=?';
    $rows=t_q(t_select().t_join().' WHERE '.$scope.' ORDER BY w.DataOd,w.ID_Wizyty',[$_SESSION['user_id']])->fetchAll();
    $now=date('Y-m-d H:i:s');$today=[];$future=[];$history=[];
    foreach ($rows as $r) {
        if (!in_array($r['Status'],['Oczekująca','Potwierdzona'],true) || $r['DataDo']<=$now) { $history[]=$r; }
        elseif (substr($r['DataOd'],0,10)===date('Y-m-d')) { $today[]=$r; }
        else { $future[]=$r; }
    }
    echo '<section class="panel-box"><h2>Dzisiaj</h2>';t_cards($today,$worker);echo '</section><section class="panel-box"><h2>Przyszłe wizyty</h2>';t_cards($future,$worker);echo '</section><section class="panel-box"><h2>Historia i anulowane</h2>';t_cards(array_reverse($history),$worker);echo '</section>';
}
function t_detail($worker=false)
{
    try { $id=t_id(t_get('id'));t_own($id); }
    catch (DomainException $ex) { http_response_code(404);t_start('Wizyta niedostępna');t_error('Nie znaleziono dostępnej wizyty.');t_end();return; }
    t_post(function() use($id,$worker) {
        $action=pole_tekstowe('action');
        if (($worker && $action!=='status') || (!$worker && $action!=='cancel')) { throw new DomainException('Niedozwolona operacja.'); }
        t_change($id,pole_tekstowe('wersja'),$action,pole_tekstowe('status'));
    },'/public/'.($worker?'pracownik':'klient').'/wizyta.php?id='.$id);
    $r=t_q(t_select().t_join().' WHERE w.ID_Wizyty=?',[$id])->fetch();
    t_start('Wizyta #'.$id);
    echo '<section class="panel-box"><h2>'.e($r['Nazwa_Uslugi']).'</h2><dl class="visit-details"><dt>Status</dt><dd>'.e($r['Status']).'</dd><dt>Pracownik</dt><dd>'.e($r['Pracownik']).'</dd><dt>Termin</dt><dd>'.e($r['DataOd']).' – '.e($r['DataDo']).'</dd><dt>Cena uzgodniona</dt><dd>'.e(t_price($r['Cena'])).'</dd>';
    if ($worker) { echo '<dt>Klient</dt><dd>'.e($r['Klient']).'</dd><dt>Telefon klienta</dt><dd>'.e($r['Telefon']).'</dd>'; }
    echo '<dt>Uwagi</dt><dd>'.nl2br(e($r['Uwagi']?:'Brak')).'</dd></dl>';
    $active=in_array($r['Status'],['Oczekująca','Potwierdzona'],true);$future=$r['DataOd']>date('Y-m-d H:i:s');
    if ($worker && $active) {
        echo '<form method="post" class="booking-form" data-confirm="Zapisać status wizyty?">'.csrf_pole().'<input type="hidden" name="wersja" value="'.(int)$r['Wersja'].'"><label>Nowy status<select name="status">';
        $options=$r['Status']==='Oczekująca'?['Potwierdzona','Anulowana']:['Ukończone','Anulowana'];
        foreach ($options as $s) { echo '<option>'.e($s).'</option>'; }
        echo '</select></label><button name="action" value="status">Zmień status</button></form><p class="field-hint">Zrealizowaną wizytę oznacz jako Ukończone. Serwer nie pozwala ukończyć jej przed końcem terminu.</p>';
    }
    if (!$worker && $active && $future) {
        echo '<div class="booking-nav"><a class="booking-button small-button" href="'.e(BASE_URL).'/public/klient/rezerwuj.php?przeloz='.$id.'">Przełóż wizytę</a><form method="post" data-confirm="Anulować wizytę?">'.csrf_pole().'<input type="hidden" name="wersja" value="'.(int)$r['Wersja'].'"><button name="action" value="cancel">Anuluj wizytę</button></form></div>';
    }
    echo '</section><section class="panel-box"><h2>Historia statusów i terminów</h2><p class="field-hint">Stan początkowy oznacza zapis stanu z chwili aktualizacji, nie odtworzoną historię wcześniejszych operacji.</p><ol class="history-list">';
    foreach (t_q('SELECT Typ,Status,DataOd,DataDo,Czas FROM HistoriaWizyt WHERE ID_Wizyty=? ORDER BY Wersja,ID_Zmiany',[$id])->fetchAll() as $h) {
        echo '<li><strong>'.e($h['Typ'].' · '.$h['Status']).'</strong><br>'.e($h['DataOd'].' – '.$h['DataDo']).'<br><small>Zapis: '.e($h['Czas']).'</small></li>';
    }
    echo '</ol></section><script src="'.e(gah_asset_url('js/terminarz.js')).'" defer></script>';t_end();
}
