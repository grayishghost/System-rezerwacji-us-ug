<?php
require_once __DIR__ . '/../includes/rezerwacje.php';
wymagaj_roli('klient');

$bledy = [];
$wejscie = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
$wartosc = static function ($nazwa, $domyslna = '') use ($wejscie) {
    return isset($wejscie[$nazwa]) && is_string($wejscie[$nazwa]) ? $wejscie[$nazwa] : $domyslna;
};
$dane = [
    'id_pokoju' => $wartosc('id_pokoju'),
    'data_od' => $wartosc('data_od'),
    'data_do' => $wartosc('data_do'),
    'ilosc_osob' => $wartosc('ilosc_osob', '1'),
];
$wynik = null;
$pokoj = null;
$kwota = null;
$token = null;
$oczekujaca = null;

if (isset($_GET['wynik'])) {
    $id_wyniku = filter_var($_GET['wynik'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id_wyniku) {
        $st = $pdo->prepare(
            'SELECT r.ID_Rezerwacji, r.DataOd, r.DataDo, r.Kwota, r.Status, p.NrPokoju, t.NazwaTypu
             FROM Rezerwacje r JOIN Pokoje p ON p.ID_Pokoju = r.ID_Pokoju
             JOIN TypyPokoi t ON t.ID_Typu = p.ID_Typu
             WHERE r.ID_Rezerwacji = ? AND r.ID_Klienta = ?'
        );
        $st->execute([$id_wyniku, $_SESSION['user_id']]);
        $wynik = $st->fetch();
    }
    if (!$wynik) {
        http_response_code(404);
        $bledy[] = 'Nie znaleziono Twojej rezerwacji.';
    }
} else {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        unset($_SESSION['rezerwacja_oczekujaca']);
    }
    $czy_wybrano = $dane['id_pokoju'] !== '' || $dane['data_od'] !== '' || $dane['data_do'] !== '';
    $id_pokoju = filter_var($dane['id_pokoju'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $ilosc_osob = filter_var($dane['ilosc_osob'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_sprawdz();
        $oczekujaca = $_SESSION['rezerwacja_oczekujaca'] ?? null;
        $wyslany_token = $_POST['token_rezerwacji'] ?? null;
        if (!is_array($oczekujaca) || !is_string($wyslany_token)
            || !hash_equals($oczekujaca['token'], $wyslany_token)
            || $oczekujaca['dane'] !== $dane) {
            $bledy[] = 'Ten formularz rezerwacji jest nieaktualny. Sprawdź dane ponownie.';
        }
        unset($_SESSION['rezerwacja_oczekujaca']);
    }

    if ($czy_wybrano || $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!$id_pokoju) {
            $bledy[] = 'Wybierz poprawny pokój.';
        } else {
            $pokoj = pokoj_do_rezerwacji($pdo, $id_pokoju);
            if (!$pokoj || $pokoj['Status'] === 'Serwis') {
                $bledy[] = 'Wybrany pokój nie jest dostępny do rezerwacji.';
            }
        }
        if (!is_string($dane['data_od']) || !is_string($dane['data_do'])) {
            $bledy[] = 'Podaj poprawne daty pobytu.';
        } elseif ($dane['data_od'] === '' || $dane['data_do'] === '') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' || $dane['data_od'] !== '' || $dane['data_do'] !== '') {
                $bledy[] = 'Podaj obie daty pobytu.';
            }
        } else {
            $blad_dat = sprawdz_daty_pobytu($dane['data_od'], $dane['data_do']);
            if ($blad_dat !== '') {
                $bledy[] = $blad_dat;
            }
        }
        if (!$ilosc_osob || ($pokoj && $ilosc_osob > (int)$pokoj['IloscOsob'])) {
            $bledy[] = 'Liczba osób przekracza pojemność pokoju albo jest nieprawidłowa.';
        }
        if (!$bledy && $dane['data_od'] !== '' && $dane['data_do'] !== '') {
            $kwota = kwota_pobytu($pokoj, $dane['data_od'], $dane['data_do']);
            if ($kwota <= 0 || $kwota > 99999999.99) {
                $bledy[] = 'Nie można obliczyć ceny dla tego pobytu.';
            } elseif (pokoj_ma_kolizje($pdo, $id_pokoju, $dane['data_od'], $dane['data_do'])) {
                $bledy[] = 'Pokój jest już zarezerwowany w wybranym terminie.';
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$bledy) {
        try {
            $pdo->beginTransaction();
            // Blokada wiersza pokoju serializuje równoczesne próby rezerwacji tego pokoju.
            $pokoj = pokoj_do_rezerwacji($pdo, $id_pokoju, true);
            if (!$pokoj || $pokoj['Status'] === 'Serwis'
                || $ilosc_osob > (int)$pokoj['IloscOsob']
                || pokoj_ma_kolizje($pdo, $id_pokoju, $dane['data_od'], $dane['data_do'])) {
                $bledy[] = 'Pokój nie jest już dostępny w wybranym terminie. Wybierz inny termin.';
            } else {
                $kwota = kwota_pobytu($pokoj, $dane['data_od'], $dane['data_do']);
                if ($kwota <= 0 || $kwota > 99999999.99) {
                    $bledy[] = 'Nie można obliczyć ceny dla tego pobytu.';
                } elseif (number_format($kwota, 2, '.', '') !== $oczekujaca['kwota']) {
                    $bledy[] = 'Cena pokoju zmieniła się. Sprawdź rezerwację ponownie.';
                } else {
                    $st = $pdo->prepare(
                        "INSERT INTO Rezerwacje (ID_Pokoju, ID_Klienta, DataOd, DataDo, Kwota, Status, Zrodlo_rezerwacji)
                         VALUES (?, ?, ?, ?, ?, 'Potwierdzona', 'Strona internetowa')"
                    );
                    $st->execute([
                        $id_pokoju, $_SESSION['user_id'], $dane['data_od'] . ' 00:00:00',
                        $dane['data_do'] . ' 00:00:00', number_format($kwota, 2, '.', ''),
                    ]);
                    $id_wyniku = $pdo->lastInsertId();
                }
            }
            if ($bledy) {
                $pdo->rollBack();
            } else {
                $pdo->commit();
                przekieruj('/public/rezerwacja.php?wynik=' . $id_wyniku);
            }
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Błąd rezerwacji: ' . $ex->getMessage());
            $bledy[] = 'Nie udało się zapisać rezerwacji. Spróbuj ponownie później.';
        }
    } elseif ($_SERVER['REQUEST_METHOD'] !== 'POST' && $czy_wybrano && !$bledy && $dane['data_od'] !== '' && $dane['data_do'] !== '') {
        $token = bin2hex(random_bytes(32));
        $_SESSION['rezerwacja_oczekujaca'] = [
            'token' => $token, 'dane' => $dane, 'kwota' => number_format($kwota, 2, '.', ''),
        ];
    }
}

$pokoje = pokoje_do_rezerwacji($pdo);
$tytul = 'Rezerwacja pobytu';
require __DIR__ . '/../includes/header.php';
?>
<main class="login-page">
    <section class="login-panel login-panel-wide" aria-labelledby="rezerwacja-tytul">
        <h1 id="rezerwacja-tytul">Rezerwacja pobytu</h1>
        <?php if ($bledy): ?>
            <div class="form-message form-message-error" role="alert"><ul>
                <?php foreach ($bledy as $blad): ?><li><?= e($blad) ?></li><?php endforeach; ?>
            </ul></div>
        <?php endif; ?>

        <?php if ($wynik): ?>
            <p class="form-message form-message-success" role="status">Rezerwacja nr <?= (int)$wynik['ID_Rezerwacji'] ?> została zapisana.</p>
            <p>Pokój <?= e($wynik['NrPokoju']) ?> — <?= e($wynik['NazwaTypu']) ?></p>
            <p>Pobyt: <?= e(substr($wynik['DataOd'], 0, 10)) ?> – <?= e(substr($wynik['DataDo'], 0, 10)) ?></p>
            <p>Kwota: <strong><?= e(number_format((float)$wynik['Kwota'], 2, ',', ' ')) ?> zł</strong></p>
            <p>Status: <?= e($wynik['Status']) ?></p>
            <a class="login-back-link" href="<?= e(BASE_URL) ?>/public/klient/index.php">Przejdź do panelu klienta</a>
        <?php elseif (!isset($_GET['wynik'])): ?>
            <p>Wybierz pokój i daty pobytu. Przed zapisem pokażemy Ci podsumowanie.</p>
            <form class="login-form" action="rezerwacja.php" method="get">
                <div class="login-field">
                    <label for="id_pokoju">Pokój</label>
                    <select id="id_pokoju" name="id_pokoju" required>
                        <option value="">-- Wybierz pokój --</option>
                        <?php foreach ($pokoje as $opcja): ?>
                            <option value="<?= (int)$opcja['ID_Pokoju'] ?>" <?= (string)$opcja['ID_Pokoju'] === (string)$dane['id_pokoju'] ? 'selected' : '' ?>>
                                Pokój <?= e($opcja['NrPokoju']) ?> — <?= e($opcja['NazwaTypu']) ?> (<?= e($opcja['CenaDobowa']) ?> zł/doba, do <?= (int)$opcja['IloscOsob'] ?> os.)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="login-field"><label for="data_od">Zameldowanie</label><input type="date" id="data_od" name="data_od" min="<?= date('Y-m-d') ?>" required value="<?= e(is_string($dane['data_od']) ? $dane['data_od'] : '') ?>"></div>
                <div class="login-field"><label for="data_do">Wymeldowanie</label><input type="date" id="data_do" name="data_do" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required value="<?= e(is_string($dane['data_do']) ? $dane['data_do'] : '') ?>"></div>
                <div class="login-field"><label for="ilosc_osob">Liczba osób</label><input type="number" id="ilosc_osob" name="ilosc_osob" min="1" required value="<?= e(is_scalar($dane['ilosc_osob']) ? $dane['ilosc_osob'] : '1') ?>"></div>
                <button class="booking-button login-button" type="submit">Sprawdź termin i cenę</button>
            </form>
            <?php if ($token): ?>
                <div class="booking-summary">
                    <h2>Potwierdź rezerwację</h2>
                    <p>Pokój <?= e($pokoj['NrPokoju']) ?> — <?= e($pokoj['NazwaTypu']) ?>, <?= (int)$ilosc_osob ?> os.</p>
                    <p><?= e($dane['data_od']) ?> – <?= e($dane['data_do']) ?></p>
                    <p>Do zapłaty: <strong><?= e(number_format($kwota, 2, ',', ' ')) ?> zł</strong></p>
                    <form action="rezerwacja.php" method="post">
                        <?= csrf_pole() ?>
                        <input type="hidden" name="token_rezerwacji" value="<?= e($token) ?>">
                        <?php foreach ($dane as $nazwa => $wartosc): ?><input type="hidden" name="<?= e($nazwa) ?>" value="<?= e($wartosc) ?>"><?php endforeach; ?>
                        <button class="booking-button" type="submit">Potwierdź i zapisz rezerwację</button>
                    </form>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
