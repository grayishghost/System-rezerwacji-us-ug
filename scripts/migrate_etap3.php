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
    foreach (['003_panel' => 'KategorieUslug','004_uzupelnienia' => 'WizytyUslug'] as $name => $sentinel) {
        $q = $pdo->prepare('SELECT COUNT(*) FROM Migracje WHERE Nazwa=?');
        $q->execute([$name]);
        if ($q->fetchColumn()) {
            echo "$name: już zainstalowana.\n";
            continue;
        }
        if ($pdo->query("SHOW TABLES LIKE '$sentinel'")->fetchColumn()) {
            throw new RuntimeException("Wykryto częściową migrację $name. Przywróć backup sprzed tej migracji.");
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
