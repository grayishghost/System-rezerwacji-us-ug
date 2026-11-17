<?php
require_once __DIR__.'/../../includes/terminarz_widok.php';
wymagaj_roli('pracownik');t_ready();
t_start('Panel pracownika');
$profile=t_q('SELECT p.Aktywny,s.Nazwa_stanowiska FROM Pracownicy p LEFT JOIN Stanowiska s ON s.ID_Stanowiska=p.ID_Stanowiska WHERE p.ID_Uzytkownika=?',[$_SESSION['user_id']])->fetch();
if (!$profile || !$profile['Aktywny']) { http_response_code(403);t_error('Profil pracownika jest nieaktywny. Skontaktuj się z administratorem.');t_end();exit; }
echo '<section class="panel-box"><h2>Witaj, '.e($_SESSION['imie']).'!</h2><p>Stanowisko: '.e($profile['Nazwa_stanowiska']?:'Nie przypisano').'</p><p>W szczegółach własnej wizyty znajdziesz dane potrzebne do obsługi oraz zmianę statusu.</p><a href="'.e(BASE_URL).'/public/profil.php">Mój profil</a></section>';
t_list(true);t_end();
