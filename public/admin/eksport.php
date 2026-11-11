<?php

require_once __DIR__ . '/../../includes/admin_rezerwacje.php';
$f = ar_filter();
if ($f['error']) {
    http_response_code(400);
    exit($f['error']);
}

$st = a_query(ar_select().ar_from().$f['sql'].' ORDER BY r.DataOd,r.ID_Rezerwacji', $f['params']);
a_log('Eksport CSV', 'rezerwacje', null, null, ['format' => 'CSV']);
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="rezerwacje_'.date('Y-m-d').'.csv"');
$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");
fputcsv($out, ['ID','Klient','Pokój','Opiekun','Od','Do','Kwota PLN','Status'], ';', '"', '');
while ($r = $st->fetch()) {
    $row = [$r['ID_Rezerwacji'],$r['Klient'],$r['NrPokoju'],$r['Pracownik'] ?? '',$r['DataOd'],$r['DataDo'],$r['Kwota'],$r['Status']];
    a_csv_row($out, $row);
}
fclose($out);
exit;
