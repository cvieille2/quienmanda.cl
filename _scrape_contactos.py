import requests
import re
import csv
import json
import sys
import io

sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

# Fetch actual motel pages and extract contact info
with open('entregables/todas-las-fichas.csv', encoding='utf-8-sig') as f:
    fichas = list(csv.DictReader(f))

results = []
for i, f in enumerate(fichas):
    slug = f.get('slug', '').strip().strip('/')
    url = f'https://infomoteles.cl/{slug}/'
    
    try:
        r = requests.get(url, timeout=10, headers={'User-Agent': 'Mozilla/5.0'})
        if r.status_code != 200:
            results.append({**f, 'email': '', 'telefono': '', 'whatsapp': '', 'direccion': ''})
            continue
        
        html = r.text
        
        # Extract email
        emails = re.findall(r'[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}', html)
        # Filter out fake/info emails
        real_emails = [e for e in emails if e not in [
            'contacto@infomoteles.cl', 'info@infomoteles.cl',
            'admin@infomoteles.cl', 'webmaster@infomoteles.cl'
        ] and not e.startswith('noreply')]
        
        # Extract phone (Chilean format: +56 X XXXX XXXX or 9 XXXX XXXX)
        phones = re.findall(r'\+56\s*\d{1,2}\s*\d{4}\s*\d{4}', html)
        if not phones:
            phones = re.findall(r'(?:^|[^0-9])(9\d{8})(?:$|[^0-9])', html)
        if not phones:
            phones = re.findall(r'(?:^|[^0-9])(2\d{7})(?:$|[^0-9])', html)
        
        # Extract WhatsApp
        whatsapp = re.findall(r'whatsapp[^\d]*(\d+)', html, re.IGNORECASE)
        
        email = real_emails[0] if real_emails else ''
        phone = phones[0].strip() if phones else ''
        wa = whatsapp[0] if whatsapp else ''
        
        print(f'{i+1}/{len(fichas)} | {slug[:25]:25s} | email={email[:30]:30s} | tel={phone[:15]:15s}')
        
        results.append({**f, 'email': email, 'telefono': phone, 'whatsapp': wa, 'direccion': ''})
    except Exception as e:
        print(f'{i+1}/{len(fichas)} | {slug[:25]:25s} | ERROR: {str(e)[:50]}')
        results.append({**f, 'email': '', 'telefono': '', 'whatsapp': '', 'direccion': ''})

# Save
with open('entregables/fichas-con-contactos.json', 'w', encoding='utf-8') as f:
    json.dump(results, f, ensure_ascii=False)

# Summary
real_email = [r for r in results if r.get('email')]
real_phone = [r for r in results if r.get('telefono')]
real_wa = [r for r in results if r.get('whatsapp')]
print(f'\n=== RESUMEN ===')
print(f'Total: {len(results)}')
print(f'Con email: {len(real_email)}')
print(f'Con teléfono: {len(real_phone)}')
print(f'Con WhatsApp: {len(real_wa)}')

print(f'\n=== TOP 50 CON EMAIL (por impresiones) ===')
real_email.sort(key=lambda r: int(r.get('impresiones', 0) or 0), reverse=True)
for r in real_email[:50]:
    print(f"  {str(r.get('impresiones','')):>5} | {r['email']:<35} | {r.get('slug','')}")

print(f'\n=== SIN EMAIL (top 20 por impresiones) ===')
sin_email = [r for r in results if not r.get('email')]
sin_email.sort(key=lambda r: int(r.get('impresiones', 0) or 0), reverse=True)
for r in sin_email[:20]:
    print(f"  {str(r.get('impresiones','')):>5} | tel={r.get('telefono',''):<15} | {r.get('slug','')}")
