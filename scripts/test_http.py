import argparse
import json
import sys
import urllib.request
import urllib.error
import urllib.parse

parser = argparse.ArgumentParser(description='Tylko odczyt publicznych stron aplikacji. Bez logowania i zapisow.')
parser.add_argument('--url', required=True, help='Adres bazowy, np. http://127.0.0.1:8091 lub https://domena/podkatalog')
args = parser.parse_args()
base = args.url.rstrip('/')
parsed = urllib.parse.urlsplit(base)
if parsed.scheme not in ('http', 'https') or not parsed.hostname or parsed.username or parsed.password or parsed.query or parsed.fragment:
    parser.error('Wymagany poprawny adres HTTP/HTTPS bez danych logowania, query i fragmentu.')
checks = []
for path in ['/public/index.php', '/public/uslugi.php', '/public/pokoje.php', '/public/logowanie.php', '/public/rejestracja.php']:
    try:
        with urllib.request.urlopen(base + path, timeout=15) as response:
            text = response.read().decode('utf-8', errors='replace')
            values = {
                'HTTP 200': response.status == 200,
                'HTML aplikacji': '<html lang="pl"' in text and 'Grand Access Hotel' in text,
                'nosniff': response.headers.get('X-Content-Type-Options') == 'nosniff',
                'CSP': "script-src 'self'" in response.headers.get('Content-Security-Policy', ''),
                'brak cache': 'no-store' in response.headers.get('Cache-Control', ''),
            }
            checks.extend({'test': path + ' ' + name, 'ok': ok} for name, ok in values.items())
    except (urllib.error.URLError, TimeoutError) as error:
        checks.append({'test': path + ' dostep', 'ok': False, 'error': str(error)})
print(json.dumps({'ok': all(row['ok'] for row in checks), 'checks': checks}, ensure_ascii=False, indent=2))
print('WYNIK ' + str(sum(row['ok'] for row in checks)) + '/' + str(len(checks)))
sys.exit(0 if checks and all(row['ok'] for row in checks) else 1)
