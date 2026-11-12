<?php
require_once __DIR__ . '/../../includes/auth.php';
wymagaj_roli('admin');
require_once __DIR__ . '/../../includes/instalacja.php';
header('Cache-Control: no-store');
$state=gah_installation($pdo);
$release=gah_release_check();
$data=$state['ready'] ? gah_integrity_data($pdo) : [];
$tytul='Kontrola instalacji i zgodności wydania';
require __DIR__ . '/../../includes/header.php'; ?>
<main class="panel-page"><h1>Kontrola instalacji i zgodności wydania</h1>
<section class="panel-box"><h2><?= $state['ready'] ? 'Schemat bazy gotowy' : 'Baza wymaga aktualizacji lub naprawy' ?></h2>
<p>Ta strona tylko odczytuje stan. Nie wykonuje migracji i nie zmienia danych.</p>
<p>PHP <?= e($state['php']) ?> · baza <?= e($state['db']) ?></p>
<p>Wydanie: <strong><?= e($release['id']) ?></strong><br>Odcisk: <code><?= e($release['hash']) ?></code></p>
<p><?= $release['changed'] ? 'NIEZGODNOŚĆ PLIKÓW: '.count($release['changed']) : 'Pliki zgodne z manifestem: '.$release['checked'] ?></p>
<?php if ($release['changed']): ?><ul><?php foreach ($release['changed'] as $p): ?><li><?= e($p) ?></li><?php endforeach; ?></ul><?php endif; ?>
<p>Porównaj identyfikator i odcisk z plikiem START wydania oraz wynikiem lokalnego skryptu. Nie oznacza to automatycznej synchronizacji z GitHubem.</p>
<div class="table-wrap"><table class="panel-table"><thead><tr><th>Element</th><th>Wynik</th></tr></thead><tbody>
<?php foreach ($state['checks'] as $c): ?><tr><td><?= e($c['element']) ?></td><td><?= $c['ok'] ? 'OK' : 'BRAK / DO POPRAWY' ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php if ($data): ?><h2>Odczyt kontroli danych</h2><p>Wyniki większe od zera wymagają analizy. Ta kontrola nie naprawia danych. Nie zastępuje blokad transakcyjnych przy rezerwowaniu.</p><div class="table-wrap"><table class="panel-table"><thead><tr><th>Reguła</th><th>Naruszenia</th></tr></thead><tbody><?php foreach ($data as $d): ?><tr><td><?= e($d['Regula']) ?></td><td><?= (int)$d['Niepoprawne'] ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
<h2>Przesyłanie ilustracji</h2><ul><li>GD: <?= extension_loaded('gd')?'OK':'BRAK — włącz rozszerzenie PHP GD' ?></li><li>fileinfo: <?= extension_loaded('fileinfo')?'OK':'BRAK — włącz rozszerzenie fileinfo' ?></li><li>Katalog assets/pokoje: <?= is_writable(__DIR__.'/../../assets/pokoje')?'zapisywalny':'brak prawa zapisu — sprawdź właściciela i uprawnienia katalogu' ?></li><li>Limit PHP pliku: <?= e(ini_get('upload_max_filesize')) ?>, żądania: <?= e(ini_get('post_max_size')) ?></li></ul><p>Pliki dodane przez formularz nie należą do stałego manifestu wydania. Zachowuj je w kopii hostingu; nie publikuj ich automatycznie w repozytorium.</p>
<p>Wykonane migracje: <?= e(implode(', ',$state['markers']) ?: 'brak') ?></p>
<?php if (!$state['ready']): ?><p><strong>Najpierw backup. W phpMyAdmin wykonuj wyłącznie brakujące migracje 003, 004, 005, 006 i 007 według instrukcji. Nie importuj pełnego SQL na istniejących danych i nie dopisuj markerów bez pełnego wykonania migracji.</strong></p><?php endif; ?>
<?php if ($state['ready'] && !$release['changed']): ?><a class="booking-button room-button" href="index.php">Otwórz pełny pulpit administratora</a><?php endif; ?>
</section></main><?php require __DIR__ . '/../../includes/footer.php'; ?>
