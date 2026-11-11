<?php


if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/../includes/db.php';
$pdo->exec("CREATE TABLE IF NOT EXISTS Migracje (Nazwa VARCHAR(100) PRIMARY KEY, Czas DATETIME DEFAULT CURRENT_TIMESTAMP)");
if (!$pdo->query("SELECT GET_LOCK('gah_migracje',10)")->fetchColumn()) {
    fwrite(STDERR, "Inna migracja jest w toku.\n");
    exit(1);
}
try {
    foreach (['003_panel' => 'KategorieUslug','004_uzupelnienia' => 'WizytyUslug','005_pokoje'=>'BlokadyPokoi','006_integralnosc'=>'__kontrola_integralnosci_006__'] as $name => $sentinel) {
        $q = $pdo->prepare('SELECT COUNT(*) FROM Migracje WHERE Nazwa=?');
        $q->execute([$name]);
        if ($q->fetchColumn()) {
            echo "$name: już zainstalowana.\n";
            continue;
        }
        $partial=false;
        if ($name==='005_pokoje') {
            $partial=(bool)$pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND ((TABLE_NAME='Pokoje' AND COLUMN_NAME IN ('Aktywny','Wersja')) OR (TABLE_NAME='Rezerwacje' AND COLUMN_NAME IN ('LiczbaOsob','Wersja')))")->fetchColumn();
        }
        if ($name==='006_integralnosc') {
            $partial=(bool)$pdo->query("SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND CONSTRAINT_NAME LIKE 'chk_i006_%'")->fetchColumn();
        }
        if ($partial || $pdo->query("SHOW TABLES LIKE '$sentinel'")->fetchColumn()) {
            throw new RuntimeException("Wykryto częściową migrację $name. Przywróć backup sprzed tej migracji.");
        }
        if ($name==='006_integralnosc') {
            $audit=file_get_contents(__DIR__.'/../database/PRZED_006_ODCZYT.sql');
            foreach (preg_split('/;\s*(?:\n|$)/',trim($audit)) as $statement) {
                if (trim($statement)==='') { continue; }
                $row=$pdo->query($statement)->fetch();
                if ((int)$row['Niepoprawne']>0) { throw new RuntimeException('Kontrola danych przed 006: '.$row['Regula'].'. Popraw dane po analizie, bez usuwania historii.'); }
            }
        }
        $sql = file_get_contents(__DIR__.'/../database/migrations/'.$name.'.sql');
        foreach (preg_split('/;\s*(?:\n|$)/', trim($sql)) as $statement) {
            if (trim($statement) !== '') {
                $pdo->exec($statement);
            }
        }
        $q = $pdo->prepare('INSERT INTO Migracje(Nazwa) VALUES(?)');
        $q->execute([$name]);
        echo "$name: OK.\n";
    }
} catch (Throwable $ex) {
    fwrite(STDERR, 'Błąd migracji: '.$ex->getMessage()."\nDDL nie jest atomowy. Przy częściowym wykonaniu przywróć backup, nie ponawiaj w ciemno.\n");
    exit(1);
} finally {
    $pdo->query("SELECT RELEASE_LOCK('gah_migracje')");
}
