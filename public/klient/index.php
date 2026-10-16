<?php
require_once __DIR__ . '/../../includes/auth.php';
wymagaj_roli('klient');      // wchodzi tylko klient; reszta dostaje komunikat "Brak dostępu"

// Własne pobyty klienta; identyfikator zawsze pochodzi z sesji.
$st = $pdo->prepare(
    'SELECT r.ID_Rezerwacji, r.DataOd, r.DataDo, r.Kwota, r.Status, p.NrPokoju
     FROM Rezerwacje r JOIN Pokoje p ON p.ID_Pokoju = r.ID_Pokoju
     WHERE r.ID_Klienta = ? ORDER BY r.DataOd DESC'
);
$st->execute([$_SESSION['user_id']]);
$rezerwacje = $st->fetchAll();

$tytul = 'Panel klienta';
require __DIR__ . '/../../includes/header.php';
?>

<main class="panel-page">
    <h1>Panel klienta</h1>
    <?php pokaz_komunikat(); ?>

    <section class="panel-box">
        <h2>Witaj, <?= e($_SESSION['imie']) ?>!</h2>
        <p>Liczba Twoich rezerwacji pobytu: <strong><?= count($rezerwacje) ?></strong></p>
        <p>
            <a class="booking-button small-button" href="<?= e(BASE_URL) ?>/public/profil.php">Mój profil</a>
            <a class="booking-button small-button" href="<?= e(BASE_URL) ?>/public/pokoje.php">Zobacz pokoje</a>
        </p>
    </section>
    <section class="panel-box">
        <h2>Moje pobyty</h2>
        <?php if ($rezerwacje): ?>
            <div class="table-wrap"><table class="panel-table">
                <thead><tr><th>Numer</th><th>Pokój</th><th>Od</th><th>Do</th><th>Kwota</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($rezerwacje as $r): ?>
                    <tr>
                        <td><?= (int)$r['ID_Rezerwacji'] ?></td>
                        <td><?= e($r['NrPokoju']) ?></td>
                        <td><?= e(substr($r['DataOd'], 0, 10)) ?></td>
                        <td><?= e(substr($r['DataDo'], 0, 10)) ?></td>
                        <td><?= e(number_format((float)$r['Kwota'], 2, ',', ' ')) ?> zł</td>
                        <td><?= e($r['Status']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        <?php else: ?><p>Nie masz jeszcze rezerwacji pobytu.</p><?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
