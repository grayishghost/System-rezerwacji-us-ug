<?php
require_once __DIR__ . '/auth.php';

function pokoje_do_rezerwacji($pdo)
{
    return $pdo->query(
        "SELECT p.ID_Pokoju, p.NrPokoju, p.CenaDobowa, p.IloscOsob, p.Status,
                t.NazwaTypu, t.Opis
         FROM Pokoje p JOIN TypyPokoi t ON t.ID_Typu = p.ID_Typu
         WHERE p.Status <> 'Serwis' ORDER BY p.NrPokoju"
    )->fetchAll();
}

function pokoj_do_rezerwacji($pdo, $id, $zablokuj = false)
{
    $st = $pdo->prepare(
        "SELECT p.ID_Pokoju, p.NrPokoju, p.CenaDobowa, p.IloscOsob, p.Status,
                t.NazwaTypu, t.Opis
         FROM Pokoje p JOIN TypyPokoi t ON t.ID_Typu = p.ID_Typu
         WHERE p.ID_Pokoju = ?" . ($zablokuj ? ' FOR UPDATE' : '')
    );
    $st->execute([$id]);
    return $st->fetch();
}

function sprawdz_daty_pobytu($od, $do)
{
    $start = DateTimeImmutable::createFromFormat('!Y-m-d', $od);
    $koniec = DateTimeImmutable::createFromFormat('!Y-m-d', $do);
    if (!$start || !$koniec || $start->format('Y-m-d') !== $od || $koniec->format('Y-m-d') !== $do) {
        return 'Podaj poprawne daty pobytu.';
    }
    if ($start < new DateTimeImmutable('today')) {
        return 'Data zameldowania nie może być w przeszłości.';
    }
    if ($koniec <= $start) {
        return 'Data wymeldowania musi być późniejsza niż data zameldowania.';
    }
    return '';
}

function pokoj_ma_kolizje($pdo, $id, $od, $do)
{
    $st = $pdo->prepare(
        "SELECT 1 FROM Rezerwacje
         WHERE ID_Pokoju = ? AND Status IN ('Oczekująca', 'Potwierdzona')
           AND DataOd < ? AND DataDo > ? LIMIT 1"
    );
    $st->execute([$id, $do . ' 00:00:00', $od . ' 00:00:00']);
    return (bool)$st->fetchColumn();
}

function kwota_pobytu($pokoj, $od, $do)
{
    $dni = (new DateTimeImmutable($od))->diff(new DateTimeImmutable($do))->days;
    return $dni * (float)$pokoj['CenaDobowa'];
}
