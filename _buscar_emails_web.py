import csv
import json
import sys
import io
import requests
import urllib.parse
import time
import re

sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

# Load existing contacts
try:
    with open('entregables/fichas-con-contactos.json', encoding='utf-8') as f:
        all_results = json.load(f)
except:
    all_results = []

# Already found emails
found_slugs = {r.get('slug','') for r in all_results if r.get('email') and 'infomoteles.cl' not in r['email']}
print(f'Emails ya encontrados: {len(found_slugs)}')

if len(found_slugs) > 0:
    for r in all_results:
        if r.get('slug','') in found_slugs and r.get('email'):
            e = r['email']
            print(f"  {r.get('impresiones',''):>5} | {e:<35} | {r.get('slug','')}")

with open('entregables/todas-las-fichas.csv', encoding='utf-8-sig') as f:
    fichas = list(csv.DictReader(f))

# We need to search web for these. Let me check if there's a web search API available.
# For now, let me just compile what we have and prepare the email templates
# based on what contacts exist.

# Compile clean contact list
contact_list = []
for f in fichas:
    slug = f.get('slug','').strip().strip('/')
    clics = f.get('clics','0')
    imp = f.get('impresiones','0')
    ctr = f.get('ctr','0')
    pos = f.get('posicion','0')
    
    # Check if we have contact info
    existing = next((r for r in all_results if r.get('slug','') == slug), None)
    email = existing.get('email','') if existing else ''
    phone = existing.get('telefono','') if existing else ''
    
    contact_list.append({
        'slug': slug,
        'email': email,
        'telefono': phone,
        'clics': clics,
        'impresiones': imp,
        'ctr': ctr,
        'posicion': pos
    })

contact_list.sort(key=lambda r: int(r.get('impresiones','0') or 0), reverse=True)
with open('entregables/contactos-compilados.json', 'w', encoding='utf-8') as f:
    json.dump(contact_list, f, ensure_ascii=False, indent=2)

with_email = [c for c in contact_list if c['email'] and 'infomoteles.cl' not in c['email']]
with_phone = [c for c in contact_list if c['telefono'] and c['telefono'] != '912506424']
no_contact = [c for c in contact_list if (not c['email'] or 'infomoteles.cl' in c['email']) and (not c['telefono'] or c['telefono'] == '912506424')]

print(f'\n=== COMPILACION FINAL ===')
print(f'Total fichas: {len(contact_list)}')
print(f'Con email propio: {len(with_email)}')
print(f'Con telefono real: {len(with_phone)}')
print(f'Sin contacto util: {len(no_contact)}')

print(f'\nMOTELES CON EMAIL ({len(with_email)}):')
for c in with_email:
    print(f"  {c['impresiones']:>5} | {c['email']:<35} | {c['slug']}")

print(f'\nTOP 20 SIN EMAIL (potencial busqueda web):')
for c in no_contact[:20]:
    print(f"  {c['impresiones']:>5} | tel:{c['telefono']:<15} | {c['slug']}")
