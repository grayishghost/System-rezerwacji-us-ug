<?php
require_once __DIR__.'/../../includes/terminarz_widok.php';
wymagaj_roli('klient');t_ready();
$error='';$confirmation=null;$old=null;$s=null;$eid=0;$day=t_get('data',date('Y-m-d'));$slots=[];$employees=[];
try {
    if (t_get('przeloz')!=='') {
        $old=t_own(t_id(t_get('przeloz')));
        if (!in_array($old['Status'],['Oczekująca','Potwierdzona'],true) || $old['DataOd']<=date('Y-m-d H:i:s')) { throw new DomainException('Możesz przełożyć wyłącznie własną przyszłą aktywną wizytę.'); }
        $sid=(int)$old['ID_Uslugi'];$eid=(int)$old['ID_Pracownika'];
    } else { $sid=t_id(t_get('usluga')); if (t_get('pracownik')!=='') { $eid=t_id(t_get('pracownik')); } }
    $s=t_service($sid);$employees=t_employees($sid);
    if ($eid) { $slots=t_slots($sid,$eid,$day,$old?(int)$old['ID_Wizyty']:null); }
} catch (DomainException $ex) { $error=$ex->getMessage(); }
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_sprawdz();
    try {
        $action=pole_tekstowe('action');
        foreach (($_SESSION['potwierdzenia_wizyt']??[]) as $k=>$v) { if ($v['expires']<time()) { unset($_SESSION['potwierdzenia_wizyt'][$k]); } }
        if ($action==='confirm') {
            $token=pole_tekstowe('token');$payload=$_SESSION['potwierdzenia_wizyt'][$token]??null;
            unset($_SESSION['potwierdzenia_wizyt'][$token]);
            if (!$payload || $payload['expires']<time()) { throw new DomainException('Potwierdzenie wygasło lub zostało już użyte. Wybierz ponownie termin.'); }
            $id=t_tx(function() use($payload) {
                if ($payload['move']) { return t_change($payload['move'],$payload['version'],'move',null,$payload['start']); }
                return t_create((int)$_SESSION['user_id'],$payload['sid'],$payload['eid'],$payload['start'],$payload['notes'],$payload);
            });
            ustaw_komunikat('success',$payload['move']?'Termin wizyty został zmieniony.':'Rezerwacja usługi została zapisana.');
            przekieruj('/public/klient/wizyta.php?id='.$id);
        } elseif ($action==='preview') {
            if (!$s || !$eid || $error) { throw new DomainException($error?:'Najpierw wybierz usługę i pracownika.'); }
            $hour=pole_tekstowe('godzina');$raw=$day.'T'.$hour;
            $notes=trim(pole_tekstowe('uwagi'));if (mb_strlen($notes)>1000) { throw new DomainException('Uwagi mogą mieć najwyżej 1000 znaków.'); }
            $plan=t_tx(fn()=>t_plan($sid,$eid,$raw,$old?(int)$old['ID_Wizyty']:null));
            $token=bin2hex(random_bytes(24));
            $_SESSION['potwierdzenia_wizyt']=$_SESSION['potwierdzenia_wizyt']??[];
            if (count($_SESSION['potwierdzenia_wizyt'])>=5) { array_shift($_SESSION['potwierdzenia_wizyt']); }
            $payload=['sid'=>$sid,'eid'=>$eid,'start'=>$raw,'price'=>$plan['price'],'duration'=>$plan['duration'],'notes'=>$notes,'move'=>$old?(int)$old['ID_Wizyty']:null,'version'=>$old['Wersja']??null,'expires'=>time()+900];
            $_SESSION['potwierdzenia_wizyt'][$token]=$payload;
            $confirmation=['token'=>$token,'plan'=>$plan];
        } else { throw new DomainException('Niedozwolona operacja.'); }
    } catch (DomainException $ex) { $error=$ex->getMessage(); }
    catch (Throwable $ex) { error_log('Rezerwacja klienta: '.$ex->getMessage());$error='Nie udało się zapisać operacji. Odśwież dostępność i spróbuj ponownie.'; }
}
t_start($old?'Przełóż wizytę #'.(int)$old['ID_Wizyty']:'Zarezerwuj usługę');
echo '<ol class="booking-steps"><li>Usługa</li><li>Pracownik</li><li>Data</li><li>Godzina</li><li>Potwierdzenie</li></ol>';
if ($error) { t_error($error); }
if ($confirmation) {
    $p=$confirmation['plan'];$employee=array_values(array_filter($employees,fn($v)=>(int)$v['ID_Pracownika']===$eid))[0]??['Nazwa'=>'Pracownik'];
    echo '<section class="panel-box"><h2>Sprawdź i potwierdź</h2><p>'.e($s['Nazwa_Uslugi']).' · '.e($employee['Nazwa']).'</p><p>'.e($p['from']).' – '.e($p['to']).'</p><p>'.e(t_price($p['price'])).' · '.(int)$p['duration'].' min</p><p>'.($old?'Przełożenie zachowuje uzgodnioną cenę i czas wizyty.':'Cena i czas są ponownie sprawdzane przy zapisie.').'</p><form method="post" class="booking-form">'.csrf_pole().'<input type="hidden" name="token" value="'.e($confirmation['token']).'"><button name="action" value="confirm">Potwierdzam '.($old?'przełożenie':'rezerwację').'</button></form><p><a href="'.e($_SERVER['REQUEST_URI']).'">Wróć do wyboru terminu</a></p></section>';
} elseif ($s) {
    echo '<section class="panel-box"><h2>'.e($s['Nazwa_Uslugi']).'</h2><p>'.e(t_price($old?$old['Cena']:$s['Cena'])).' · '.($old?(int)round((strtotime($old['DataDo'])-strtotime($old['DataOd']))/60):(int)$s['CzasTrwania']).' min</p>';
    if (!$old) {
        echo '<form method="get" class="booking-form"><input type="hidden" name="usluga" value="'.$sid.'"><label>2. Pracownik<select name="pracownik" required><option value="">Wybierz pracownika</option>';
        foreach ($employees as $p) { echo '<option value="'.(int)$p['ID_Pracownika'].'"'.($eid===(int)$p['ID_Pracownika']?' selected':'').'>'.e($p['Nazwa']).'</option>'; }
        echo '</select></label><button>Wybierz pracownika</button></form>';
    } else { echo '<p>Zachowujemy usługę i pracownika. Historia poprzedniego terminu pozostanie w szczegółach.</p>'; }
    if ($eid) {
        echo '<form method="get" class="booking-form" id="day-form">';
        if ($old) { echo '<input type="hidden" name="przeloz" value="'.(int)$old['ID_Wizyty'].'">'; }
        else { echo '<input type="hidden" name="usluga" value="'.$sid.'"><input type="hidden" name="pracownik" value="'.$eid.'">'; }
        echo '<label>3. Data<input type="date" name="data" value="'.e($day).'" min="'.date('Y-m-d').'" max="9998-12-31" required></label><button>Pokaż wolne godziny</button></form><p class="field-hint">Godziny są dostępne w odstępach 15 minut, uwzględniają czas usługi, grafik, urlopy, dni wolne i inne wizyty. Ostateczne sprawdzenie następuje przy zapisie.</p><p id="slots-message" role="status" aria-live="polite"></p>';
        echo '<form method="post" class="booking-form" id="slot-form">'.csrf_pole().'<fieldset><legend>4. Dostępna godzina</legend><div class="time-slots" id="time-slots">';
        foreach ($slots as $v) { echo '<label class="time-slot"><input type="radio" name="godzina" value="'.e($v['value']).'" required><span>'.e($v['value'].' – '.$v['end']).'</span></label>'; }
        if (!$slots) { echo '<p>Brak wolnych terminów w tym dniu. Wybierz inną datę lub pracownika.</p>'; }
        echo '</div></fieldset><label>Uwagi (opcjonalne)<textarea name="uwagi" maxlength="1000"></textarea></label><button name="action" value="preview"'.(!$slots?' disabled':'').'>Przejdź do potwierdzenia</button></form>';
        echo '<div id="booking-config" data-api="'.e(BASE_URL).'/public/klient/terminy.php" data-service="'.$sid.'" data-employee="'.$eid.'" data-move="'.(int)($old['ID_Wizyty']??0).'"></div><script src="'.e(gah_asset_url('js/terminarz.js')).'" defer></script>';
    }
    echo '</section>';
}
t_end();
