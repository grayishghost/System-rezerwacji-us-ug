<?php
require_once __DIR__ . '/auth.php';
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
try {
    $gotowe = $pdo->query("SELECT COUNT(*) FROM Migracje WHERE Nazwa = '004_uzupelnienia'")->fetchColumn();
} catch (PDOException $ex) {
    $gotowe = false;
}
if (!$gotowe) {
    http_response_code(503);
    exit('Panel wymaga migracji bazy. W katalogu projektu uruchom: php scripts/migrate_etap3.php');
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
    if (!$d || $d->format('Y-m-d') !== $v) {
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
    global $pdo;
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    csrf_sprawdz();
    try {
        $pdo->beginTransaction();
        $action();
        $pdo->commit();
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

    $file = basename($_SERVER['SCRIPT_NAME']);
    $query = $_SERVER['QUERY_STRING'] ?? '';
    przekieruj('/public/admin/' . $file . ($query ? '?' . $query : ''));
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
    $links = ['index.php' => 'Pulpit', 'uzytkownicy.php' => 'Konta', 'pracownicy.php' => 'Pracownicy',
        'przypisania.php' => 'Kompetencje', 'grafik.php' => 'Grafik i urlopy', 'kategorie.php' => 'Kategorie',
        'uslugi.php' => 'Usługi', 'rezerwacje.php' => 'Rezerwacje', 'wizyty.php' => 'Wizyty usług', 'kalendarz.php' => 'Kalendarz', 'logi.php' => 'Historia'];
    ?><!doctype html><html lang="pl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($title) ?> | GAH</title><link rel="stylesheet" href="<?= e(BASE_URL) ?>/css/admin.css"></head><body>
    <header class="top"><a href="index.php" class="brand">GAH <small>Grand Access Hotel</small></a>
    <a href="<?= e(BASE_URL) ?>/public/index.php">Strona hotelu</a><a href="<?= e(BASE_URL) ?>/public/profil.php">Profil</a>
    <form method="post" action="<?= e(BASE_URL) ?>/public/wyloguj.php"><?= csrf_pole() ?><button>Wyloguj</button></form></header>
    <div class="layout"><aside><p class="eyebrow">ADMINISTRACJA</p><nav>
    <?php foreach ($links as $url => $label): ?><a <?= basename($_SERVER['SCRIPT_NAME']) === $url ? 'aria-current="page"' : '' ?> href="<?= e($url) ?>"><?= e($label) ?></a><?php endforeach; ?>
    </nav><p class="muted">Etap 3 · panel hotelu</p></aside><main><p class="eyebrow">GRAND ACCESS / ZARZĄDZANIE</p><h1><?= e($title) ?></h1>
    <?php pokaz_komunikat();
}
function a_end()
{
    echo '</main></div><script src="' . e(BASE_URL) . '/js/admin.js"></script></body></html>';
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
