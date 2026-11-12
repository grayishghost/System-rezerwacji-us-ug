<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/assets.php';
require_once __DIR__ . '/instalacja.php';
wymagaj_roli('admin');


$pdo->exec('SET time_zone = ' . $pdo->quote(date('P')));
set_exception_handler(function (Throwable $ex) {
    global $pdo;
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Nieobsłużony błąd panelu: ' . $ex->getMessage());
    http_response_code(500);
    echo 'Nie udało się wczytać danych. Spróbuj ponownie lub skontaktuj się z administratorem.';
});
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
$installation=gah_installation($pdo);
if (!$installation['ready']) {
    http_response_code(503);
    echo 'Panel wymaga aktualizacji schematu bazy. ';
    echo '<a href="'.e(BASE_URL.'/public/admin/instalacja.php').'">Sprawdź instalację i brakujące migracje</a>';
    exit;
}

function a_query($sql, $params = [])
{
    global $pdo;
    $st = $pdo->prepare($sql);
    $st->execute($params);
    return $st;
}
function a_id($v)
{
    $id = filter_var($v, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$id) {
        throw new DomainException('Nieprawidłowy identyfikator.');
    }
    return $id;
}
function a_text($name, $max = 100, $required = true)
{
    $v = trim(pole_tekstowe($name));
    if (($required && $v === '') || mb_strlen($v) > $max) {
        throw new DomainException("Pole $name: wymagane poprawne dane, maks. $max znaków.");
    }
    return $v;
}
function a_enum($name, $allowed)
{
    $v = pole_tekstowe($name);
    if (!in_array($v, $allowed, true)) {
        throw new DomainException('Nieprawidłowa wartość: ' . $name);
    }
    return $v;
}
function a_date($v)
{
    if (!is_string($v)) {
        throw new DomainException('Niepoprawna data.');
    }
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $v);
    if (!$d || $d->format('Y-m-d') !== $v || $d->format('Y') < '1000' || $d->format('Y') > '9998') {
        throw new DomainException('Niepoprawna data.');
    }
    return $v;
}
function a_get($name, $default = '')
{
    return isset($_GET[$name]) && is_string($_GET[$name]) ? trim($_GET[$name]) : $default;
}
function a_record($table, $pk, $id, $lock = false)
{
    
    $row = a_query("SELECT * FROM $table WHERE $pk = ?" . ($lock ? ' FOR UPDATE' : ''), [$id])->fetch();
    if (!$row) {
        throw new DomainException('Nie znaleziono rekordu. Odśwież listę.');
    }
    return $row;
}
function a_log($action, $object, $id, $before, $after)
{
    
    a_query(
        'INSERT INTO LogAdministratora(ID_Admina,Akcja,Obiekt,ID_Obiektu,Przed,Po) VALUES(?,?,?,?,?,?)',
        [$_SESSION['user_id'], $action, $object, $id,
         $before === null ? null : json_encode($before, JSON_UNESCAPED_UNICODE),
         $after === null ? null : json_encode($after, JSON_UNESCAPED_UNICODE)]
    );
}
function a_post(callable $action)
{
    global $pdo,$a_created_files;
    $a_created_files=[];
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    csrf_sprawdz();
    try {
        $pdo->beginTransaction();
        $action();
        $pdo->commit();
        $a_created_files=[];
        ustaw_komunikat('success', 'Zapisano zmiany.');
    } catch (DomainException $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        ustaw_komunikat('error', $ex->getMessage());
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Panel admina: ' . $ex->getMessage());
        ustaw_komunikat('error', 'Nie udało się zapisać. Sprawdź, czy dane nie są powtórzone lub powiązane z innymi rekordami.');
    }
    
    foreach ($a_created_files as $path) { if (is_file($path)) { unlink($path); } }
    header('Location: '.a_url());
    exit;
}
function a_page($count, $size = 12)
{
    $pages = max(1, (int)ceil($count / $size));
    $page = min($pages, max(1, (int)a_get('page', '1')));
    return [$page, $pages, $size, ($page - 1) * $size];
}
function a_pages($page, $pages)
{
    echo '<nav class="pagination" aria-label="Strony">';
    foreach (array_unique([1, max(1, $page - 1), $page, min($pages, $page + 1), $pages]) as $n) {
        $q = $_GET;
        $q['page'] = $n;
        echo '<a ' . ($n === $page ? 'aria-current="page"' : '') . ' href="?' . e(http_build_query($q)) . '">' . $n . '</a> ';
    }
    echo '<span>Strona ' . $page . ' z ' . $pages . '</span></nav>';
}
function a_start($title)
{
    $links = ['index.php' => 'Pulpit', 'pokoje.php'=>'Pokoje i oferta', 'dostepnosc.php'=>'Dostępność', 'uzytkownicy.php' => 'Konta', 'pracownicy.php' => 'Pracownicy',
        'grafik.php' => 'Grafik i urlopy',
        'uslugi.php' => 'Usługi', 'rezerwacje.php' => 'Pobyty', 'wizyty.php' => 'Wizyty usług', 'kalendarz.php' => 'Kalendarz', 'logi.php' => 'Historia', 'instalacja.php'=>'Kontrola instalacji'];
    ?><!doctype html><html lang="pl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($title) ?> | GAH</title><link rel="stylesheet" href="<?= e(gah_asset_url('css/admin.css')) ?>"></head><body>
    <a class="skip-link" href="#tresc">Przejdź do treści</a>
    <header class="top"><a href="<?= e(BASE_URL) ?>/public/index.php" class="brand" aria-label="Grand Access Hotel — strona główna"><img src="<?= e(gah_asset_url('assets/logo.png')) ?>" alt="" width="48" height="48"><span>Grand Access Hotel</span></a>
    <a href="<?= e(BASE_URL) ?>/public/index.php">Strona hotelu</a><a href="<?= e(BASE_URL) ?>/public/profil.php">Profil</a>
    <form method="post" action="<?= e(BASE_URL) ?>/public/wyloguj.php"><?= csrf_pole() ?><button>Wyloguj</button></form></header>
    <div class="layout"><aside><p class="eyebrow">ADMINISTRACJA</p><nav aria-label="Moduły administracji">
    <?php foreach ($links as $url => $label): ?><a <?= a_module() === $url ? 'aria-current="page"' : '' ?> href="<?= e($url) ?>"><?= e($label) ?></a><?php endforeach; ?>
    </nav><p class="muted">Etap 3 · panel hotelu</p></aside><main id="tresc"><p class="eyebrow">GRAND ACCESS / ZARZĄDZANIE</p><h1><?= e($title) ?></h1>
    <?php pokaz_komunikat(); a_tabs();
}
function a_end()
{
    echo '</main></div><script src="' . e(gah_asset_url('js/admin.js')) . '"></script></body></html>';
}
function a_input($name, $label, $value = '', $type = 'text', $extra = '')
{
    echo '<label>' . e($label) . '<input name="' . e($name) . '" type="' . e($type) . '" value="' . e($value) . '" ' . $extra . '></label>';
}
function a_select($name, $label, $options, $selected = '')
{
    echo '<label>' . e($label) . '<select name="' . e($name) . '">';
    foreach ($options as $v => $text) {
        echo '<option value="' . e($v) . '" ' . ((string)$v === (string)$selected ? 'selected' : '') . '>' . e($text) . '</option>';
    }
    echo '</select></label>';
}
function a_hidden($id)
{
    echo csrf_pole() . '<input type="hidden" name="id" value="' . (int)$id . '">';
}
function a_delete($id, $label = 'Usuń')
{
    ?><form method="post" data-confirm="Czy na pewno wykonać operację? Powiązane dane mogą uniemożliwić usunięcie."><?= csrf_pole() ?><input type="hidden" name="id" value="<?= (int)$id ?>"><button class="danger" name="action" value="delete"><?= e($label) ?></button></form><?php
}

function a_csv_row($output, array $row)
{
    foreach ($row as &$cell) {
        $cell = (string)$cell;
        if (preg_match('/^[\x00-\x20]*[=+@\-]/u', $cell) || preg_match('/^[\t\r\n]/', $cell)) {
            $cell = "'" . $cell;
        }
    }
    unset($cell);
    fputcsv($output, $row, ';', '"', '');
}

function a_module()
{
    $f=basename($_SERVER['SCRIPT_NAME']);
    return ['kategorie.php'=>'uslugi.php','przypisania.php'=>'pracownicy.php'][$f] ?? $f;
}
function a_url($changes=[])
{
    $q=$_GET;
    $f=basename($_SERVER['SCRIPT_NAME']);
    if ($f==='kategorie.php') { $q['sekcja']='kategorie'; }
    if ($f==='przypisania.php') { $q['sekcja']='kompetencje'; }
    $q=array_merge($q,$changes);
    foreach ($q as $k=>$v) { if ($v===null) { unset($q[$k]); } }
    return BASE_URL.'/public/admin/'.a_module().($q?'?'.http_build_query($q):'');
}
function a_tabs()
{
    $f=a_module();
    $map=['uslugi.php'=>[''=>'Usługi','kategorie'=>'Kategorie usług'],'pracownicy.php'=>[''=>'Pracownicy','kompetencje'=>'Kompetencje','stanowiska'=>'Stanowiska'],'pokoje.php'=>[''=>'Pokoje i oferta','typy'=>'Typy pokoi']];
    if (!isset($map[$f])) { return; }
    $section=a_get('sekcja');
    if (basename($_SERVER['SCRIPT_NAME'])==='kategorie.php') { $section='kategorie'; }
    if (basename($_SERVER['SCRIPT_NAME'])==='przypisania.php') { $section='kompetencje'; }
    echo '<nav class="subnav" aria-label="Sekcje modułu">';
    foreach ($map[$f] as $key=>$text) {
        echo '<a '.($section===$key?'aria-current="page"':'').' href="'.e(BASE_URL.'/public/admin/'.$f.($key?'?sekcja='.$key:'')).'">'.e($text).'</a>';
    }
    echo '</nav>';
}
function a_version($row)
{
    if ((string)$row['Wersja']!==pole_tekstowe('wersja')) { throw new DomainException('Rekord zmienił się w innej karcie. Odśwież formularz.'); }
}
