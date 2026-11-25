# Termin wizyty w prywatnym kalendarzu

Klient otwiera Moje konto → szczegóły własnej wizyty → Pobierz termin do kalendarza (.ics). Pracownik pobiera termin wyłącznie wizyty przypisanej do swojego aktywnego profilu. Administrator nadal ma dotychczasowe eksporty panelu; ten endpoint jest przeznaczony dla klienta i pracownika.

Plik zawiera usługę, pracownika, początek, koniec, bieżący status, stały identyfikator wizyty i jej wersję. Nie zawiera imienia i nazwiska klienta, telefonu, e-maila, uwag, ceny ani identyfikatora sesji. Sam termin i nazwa usługi też mogą być informacją prywatną: nie publikuj pliku ani nie wysyłaj go osobom postronnym.

## Jak użyć

1. Pobierz plik i otwórz go w aplikacji kalendarza, np. przez jej funkcję importowania pliku ICS.
2. Sprawdź dzień, godzinę i wybrany kalendarz przed zapisaniem.
3. Po przełożeniu wizyty usuń lub zaktualizuj poprzedni wpis. Samo ponowne importowanie może utworzyć duplikat — zależy to od programu.
4. Po anulowaniu usuń wpis albo zaktualizuj go przy użyciu nowego pliku. Eksport anulowanej wizyty ma STATUS:CANCELLED; nie wymusza zdalnego usunięcia wpisu.

To jednorazowy eksport, nie subskrypcja, automatyczna synchronizacja, e-mail ani przypomnienie. Nie udostępniamy publicznych adresów subskrypcji ani tokenów dostępu.

## Implementacja i testy

public/wizyta_kalendarz.php wymaga sesji, roli klient/pracownik, markera 008 oraz kontroli t_own. Cudzy lub niepoprawny identyfikator daje ogólny komunikat i HTTP 404. GET nie zmienia wizyty ani historii. Odpowiedź ma text/calendar, Content-Disposition: attachment i Cache-Control: no-store.

includes/kalendarz_ics.php generuje CRLF, escapuje przecinki, średniki, ukośniki i nowe linie oraz składa linie do maksymalnie 75 bajtów, bez przecinania UTF-8. Lokalny termin Europe/Warsaw zamienia na UTC z uwzględnieniem letniego/zimowego offsetu. Aplikacja nadal przechowuje lokalne DATETIME: nie jest wielostrefowym systemem rezerwacji i nie rozróżnia dwóch powtórzonych godzin podczas jesiennej zmiany czasu. UID jest stały dla ID w tej instalacji; SEQUENCE odpowiada Wersja. Eksport ma METHOD:PUBLISH.

Testy: php scripts/test_jednostkowe.php; dodatkowo kontrola dostępu, braku zapisu przy pobieraniu i pobranie załącznika przez Chromium w raporcie ETAP5_TESTY.md. Nie deklarujemy ręcznej weryfikacji importu w każdej wersji Outlooka/Google Calendar/Apple Calendar.
