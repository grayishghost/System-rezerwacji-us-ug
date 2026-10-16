<?php
require_once __DIR__ . '/../../includes/auth.php';
wymagaj_roli('admin');       // tylko administrator

// Blokowanie / odblokowywanie kont (formularz POST + token CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_sprawdz();

    $id = filter_var(pole_tekstowe('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $nowy_stan = pole_tekstowe('aktywny');
    $aktywny = $nowy_stan === '1' ? 1 : 0;

    if (!$id || !in_array($nowy_stan, ['0', '1'], true)) {
        ustaw_komunikat('error', 'Nieprawidłowe dane konta.');
    } elseif ($id === (int)$_SESSION['user_id']) {
        ustaw_komunikat('error', 'Nie możesz zablokować własnego konta.');
    } else {
        $st = $pdo->prepare('UPDATE Uzytkownicy SET Aktywny = ? WHERE ID_Uzytkownika = ?');
        $st->execute([$aktywny, $id]);
        if ($st->rowCount() === 0) {
            ustaw_komunikat('error', 'Nie znaleziono konta lub jego stan nie wymagał zmiany.');
        } else {
            ustaw_komunikat('success', $aktywny ? 'Konto zostało odblokowane.' : 'Konto zostało zablokowane.');
        }
    }
    przekieruj('/public/admin/index.php');
}

$uzytkownicy = $pdo->query(
    'SELECT ID_Uzytkownika, Imie, Nazwisko, Email, Telefon, Rola, Aktywny, DataUtworzenia
     FROM Uzytkownicy ORDER BY ID_Uzytkownika'
)->fetchAll();

$tytul = 'Panel administratora';
require __DIR__ . '/../../includes/header.php';
?>

<main class="panel-page">
    <h1>Panel administratora</h1>
    <?php pokaz_komunikat(); ?>

    <section class="panel-box">
        <h2>Użytkownicy</h2>
        <div class="table-wrap">
            <table class="panel-table">
                <thead>
                    <tr>
                        <th>ID</th><th>Imię i nazwisko</th><th>E-mail</th><th>Telefon</th>
                        <th>Rola</th><th>Status</th><th>Akcja</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($uzytkownicy as $u): ?>
                    <tr>
                        <td><?= (int)$u['ID_Uzytkownika'] ?></td>
                        <td><?= e($u['Imie'] . ' ' . $u['Nazwisko']) ?></td>
                        <td><?= e($u['Email']) ?></td>
                        <td><?= e($u['Telefon']) ?></td>
                        <td><?= e($u['Rola']) ?></td>
                        <td>
                            <?php if ($u['Aktywny']): ?>
                                <span class="badge badge-ok">aktywne</span>
                            <?php else: ?>
                                <span class="badge badge-off">zablokowane</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ((int)$u['ID_Uzytkownika'] !== (int)$_SESSION['user_id']): ?>
                                <form action="index.php" method="post">
                                    <?= csrf_pole() ?>
                                    <input type="hidden" name="id" value="<?= (int)$u['ID_Uzytkownika'] ?>">
                                    <input type="hidden" name="aktywny" value="<?= $u['Aktywny'] ? '0' : '1' ?>">
                                    <button class="booking-button small-button" type="submit">
                                        <?= $u['Aktywny'] ? 'Zablokuj' : 'Odblokuj' ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="field-hint">Pozostałe moduły panelu (kategorie, usługi, pracownicy, rezerwacje) powstaną w etapie 3.</p>
    </section>
</main>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
