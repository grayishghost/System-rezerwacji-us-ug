<?php

if (PHP_SAPI !== 'cli') { http_response_code(404);exit; }
$bad=false;
function wynik($name,$ok) { global $bad;echo ($ok?'OK   ':'BŁĄD ').$name.PHP_EOL;if(!$ok){$bad=true;} }
wynik('PHP co najmniej 8.1',version_compare(PHP_VERSION,'8.1.0','>='));
foreach(['pdo_mysql','mbstring'] as $ext){wynik('Rozszerzenie '.$ext,extension_loaded($ext));}
$config=__DIR__.'/../includes/config.php';wynik('Lokalny includes/config.php',is_file($config));
if($bad){exit(1);}
require $config;
try{
 $pdo=new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
 wynik('Połączenie z bazą',true);
 foreach(['003_panel','004_uzupelnienia'] as $name){$q=$pdo->prepare('SELECT COUNT(*) FROM Migracje WHERE Nazwa=?');$q->execute([$name]);wynik('Migracja '.$name,(bool)$q->fetchColumn());}
 $tables=['Uzytkownicy','Pracownicy','Stanowiska','KategorieUslug','Uslugi_dodatkowe','WizytyUslug','Rezerwacje','Pokoje','TypyPokoi','Platnosci','ZamowieniaUslugi','Pracownicy_Uslugi','GrafikPracownikow','Proby_logowania','LogAdministratora','Migracje'];
 foreach($tables as $table){$q=$pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');$q->execute([$table]);wynik('Tabela '.$table,(bool)$q->fetchColumn());}
 $q=$pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='WizytyUslug' AND REFERENCED_TABLE_NAME IS NOT NULL");wynik('Trzy FK rezerwacji usługi',(int)$q->fetchColumn()===3);
 $q=$pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Uslugi_dodatkowe' AND COLUMN_NAME='Kategoria'");wynik('Kategoria znormalizowana (bez starej kopii tekstowej)',(int)$q->fetchColumn()===0);
}catch(Throwable $ex){wynik('Baza i schemat',false);fwrite(STDERR,'Sprawdź konfigurację, migracje i log bazy. Szczegóły lokalne: '.$ex->getMessage().PHP_EOL);}
echo $bad?'Wymagane poprawki przed uruchomieniem strony.'.PHP_EOL:'Konfiguracja i schemat gotowe. Sprawdź jeszcze stronę w przeglądarce.'.PHP_EOL;
exit($bad?1:0);
