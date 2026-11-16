<?php
require_once __DIR__.'/../includes/terminarz_widok.php';
t_ready();
try { $s=t_service(t_id(t_get('id'))); }
catch (DomainException $ex) { http_response_code(404);t_start('Usługa niedostępna');t_error($ex->getMessage());t_end();exit; }
t_start($s['Nazwa_Uslugi']);
echo '<section class="panel-box"><p>Kategoria: '.e($s['Kategoria']).'</p><p>'.nl2br(e($s['Opis'])).'</p><p><strong>'.e(t_price($s['Cena'])).'</strong> · '.(int)$s['CzasTrwania'].' min</p><h2>Pracownicy wykonujący usługę</h2><ul>';
$employees=t_employees((int)$s['ID_Uslugi']);
foreach ($employees as $p) { echo '<li>'.e($p['Nazwa']).'</li>'; }
echo '</ul>';
if (!$employees) { echo '<p>Obecnie brak dostępnych pracowników. Sprawdź ofertę później.</p>'; }
elseif (!zalogowany()) { echo '<p>Zaloguj się jako klient, aby wybrać pracownika i termin.</p><a class="booking-button small-button" href="'.e(BASE_URL).'/public/logowanie.php">Zaloguj się</a>'; }
elseif (($_SESSION['rola']??'')==='klient') { echo '<a class="booking-button small-button" href="'.e(BASE_URL).'/public/klient/rezerwuj.php?usluga='.(int)$s['ID_Uslugi'].'">Wybierz pracownika i termin</a>'; }
else { echo '<p>Ten formularz jest przeznaczony dla klientów. Jako administrator zarządzasz wizytami w swoim panelu.</p>'; }
echo '</section>';t_end();
