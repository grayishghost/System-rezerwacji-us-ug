<?php
function gah_poprawna_data($value): bool
{
    if (!is_string($value) || !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D',$value)) { return false; }
    $date=DateTimeImmutable::createFromFormat('!Y-m-d',$value);
    return $date && $date->format('Y-m-d')===$value && $date->format('Y')>='1000' && $date->format('Y')<='9998';
}
function gah_nakladanie(string $oldFrom,string $oldTo,string $newFrom,string $newTo): bool
{
    return $oldFrom<$newTo && $oldTo>$newFrom;
}
