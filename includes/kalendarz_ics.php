<?php
function gah_ics_text(string $text): string
{
    $text=str_replace(["\r\n","\r"],"\n",$text);
    return str_replace(["\\","\n",';',','],["\\\\","\\n",'\\;','\\,'],$text);
}
function gah_ics_fold(string $line): string
{
    $out='';$limit=75;
    while (strlen($line)>$limit) {
        $part=mb_strcut($line,0,$limit,'UTF-8');
        $out.=$part."\r\n ";$line=substr($line,strlen($part));$limit=74;
    }
    return $out.$line;
}
function gah_ics_utc(string $value): string
{
    if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/D',$value)) { throw new DomainException('Niepoprawny termin kalendarza.'); }
    $date=DateTimeImmutable::createFromFormat('!Y-m-d H:i:s',$value,new DateTimeZone('Europe/Warsaw'));
    if (!$date || $date->format('Y-m-d H:i:s')!==$value) { throw new DomainException('Niepoprawny termin kalendarza.'); }
    return $date->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z');
}
function gah_visit_ics(array $visit): string
{
    $status=match($visit['Status']) {'Anulowana'=>'CANCELLED','Oczekująca'=>'TENTATIVE',default=>'CONFIRMED'};
    $lines=['BEGIN:VCALENDAR','VERSION:2.0','PRODID:-//Grand Access Hotel//Rezerwacje//PL','CALSCALE:GREGORIAN','METHOD:PUBLISH','BEGIN:VEVENT','UID:gah-wizyta-'.(int)$visit['ID_Wizyty'].'@grand-access-hotel.invalid','SEQUENCE:'.(int)$visit['Wersja'],'DTSTAMP:'.gmdate('Ymd\THis\Z'),'DTSTART:'.gah_ics_utc($visit['DataOd']),'DTEND:'.gah_ics_utc($visit['DataDo']),'SUMMARY:'.gah_ics_text($visit['Nazwa_Uslugi']),'DESCRIPTION:'.gah_ics_text('Pracownik: '.$visit['Pracownik'].'; status w systemie: '.$visit['Status']),'STATUS:'.$status,'TRANSP:'.($visit['Status']==='Anulowana'?'TRANSPARENT':'OPAQUE'),'END:VEVENT','END:VCALENDAR'];
    return implode("\r\n",array_map('gah_ics_fold',$lines))."\r\n";
}
