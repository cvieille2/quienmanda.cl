import requests
import re
import csv
import json
import sys
import io
from concurrent.futures import ThreadPoolExecutor, as_completed

sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

with open('entregables/todas-las-fichas.csv', encoding='utf-8-sig') as f:
    fichas = list(csv.DictReader(f))

# Load existing results
try:
    with open('entregables/fichas-con-contactos.json', encoding='utf-8') as f:
        done_slugs = {r['slug']: r for r in json.load(f)}
except:
    done_slugs = {}

remaining = [f for f in fichas if f.get('slug','').strip().strip('/') not in done_slugs]
print(f'Ya procesadas: {len(done_slugs)}')
print(f'Restantes: {len(remaining)}')

def process_ficha(f):
    slug = f.get('slug', '').strip().strip('/')
    if not slug: return None
    
    url = f'https://infomoteles.cl/{slug}/'
    try:
        r = requests.get(url, timeout=8, headers={'User-Agent': 'Mozilla/5.0'})
        if r.status_code != 200:
            return {**f, 'email': '', 'telefono': '', 'whatsapp': '', 'direccion': ''}
        
        html = r.text
        emails = re.findall(r'[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}', html)
        real_emails = [e for e in emails if 'infomoteles.cl' not in e and not e.startswith('noreply')]
        phones = re.findall(r'\+56\s*\d{1,2}\s*\d{4}\s*\d{4}', html)
        if not phones:
            phones = re.findall(r'(?:^|[^0-9])(9\d{8})(?:$|[^0-9])', html)
        if not phones:
            phones = re.findall(r'(?:^|[^0-9])(2\d{7})(?:$|[^0-9])', html)
        whatsapp = re.findall(r'whatsapp[^\d]*(\d+)', html, re.IGNORECASE)
        
        return {**f, 'email': real_emails[0] if real_emails else '',
                'telefono': phones[0].strip() if phones else '',
                'whatsapp': whatsapp[0] if whatsapp else '',
                'direccion': ''}
    except:
        return {**f, 'email': '', 'telefono': '', 'whatsapp': '', 'direccion': ''}

new_results = []
with ThreadPoolExecutor(max_workers=5) as executor:
    futures = {executor.submit(process_ficha, f): f for f in remaining}
    for future in as_completed(futures):
        result = future.result()
        if result:
            new_results.append(result)
            slug = result.get('slug','')[:25]
            has = f"email={result['email'][:20]}" if result.get('email') else f"tel={result.get('telefono','')[:15]}"
            print(f'{slug:25s} | {has}')

# Merge with existing
existing = list(done_slugs.values())
all_results = existing + new_results

with open('entregables/fichas-con-contactos.json', 'w', encoding='utf-8') as f:
    json.dump(all_results, f, ensure_ascii=False, indent=2)

real_email = [r for r in all_results if r.get('email')]
real_phone = [r for r in all_results if r.get('telefono') and r.get('telefono') != '912506424']
default_phone = [r for r in all_results if r.get('telefono') == '912506424']
no_contact = [r for r in all_results if not r.get('email') and not r.get('telefono')]

print(f'\n=== RESUMEN FINAL ===')
print(f'Total: {len(all_results)}')
print(f'Con email real: {len(real_email)}')
print(f'Con telefono real: {len(real_phone)}')
print(f'Con telefono default: {len(default_phone)}')
print(f'Sin contacto: {len(no_contact)}')

print(f'\n=== TODOS CON EMAIL ===')
real_email.sort(key=lambda r: int(r.get('impresiones', 0) or 0), reverse=True)
for r in real_email:
    print(f"  {str(r.get('impresiones','')):>5} | {r['email']:<35} | {r.get('slug','')}")
