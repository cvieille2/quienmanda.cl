#!/usr/bin/env python3
"""Add Amazon affiliate links to posts"""

import requests, json

AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'
TAG = 'avesnativas-21'

def upd(post_id, content):
    r = requests.put(f'{BASE}posts/{post_id}', auth=AUTH, json={'content': content}, timeout=120)
    ok = r.status_code == 200
    print(f'  ID {post_id}: {"OK" if ok else "FAIL"} ({r.status_code})')
    return ok

def aff_link(kw, text):
    return f'<a href="https://www.amazon.es/s?k={kw}&tag={TAG}" target="_blank" rel="nofollow sponsored">{text}</a>'

# ============================================================
# Post 15082 - Importancia de las aves en Chile
# ============================================================
print('=== 15082: Importancia de las aves ===')

r = requests.get(f'{BASE}posts/15082', auth=AUTH, timeout=30)
c = r.json()['content']['rendered']

# Add binoculars link after "Control biologico de plagas" section
link1 = aff_link('binoculares+observacion+aves', 'Binoculares para observacion de aves en Amazon.es')
c = c.replace(
    '<p>Las aves insectivoras',
    f'<p>{link1}</p><p>Las aves insectivoras'
)

# Add bird guide book link before "Como puedes ayudar" section
link2 = aff_link('guia+aves+chile+libro', 'Guias de aves de Chile en Amazon.es')
c = c.replace(
    '<h2>Como puedes ayudar a conservar las aves chilenas</h2>',
    f'<p>{link2}</p><h2>Como puedes ayudar a conservar las aves chilenas</h2>'
)

upd(15082, c)

# ============================================================
# Post 15094 - Palomas urbanas problema salud publica
# ============================================================
print('=== 15094: Palomas urbanas ===')

r = requests.get(f'{BASE}posts/15094', auth=AUTH, timeout=30)
c = r.json()['content']['rendered']

# Add pigeon deterrent link in the "Exclusion fisica" section
link3 = aff_link('disuasivo+palomas+pinchos+redes', 'Pinchos y redes antipalomas en Amazon.es')
c = c.replace(
    '<h3>Exclusion fisica</h3>',
    f'<h3>Exclusion fisica</h3><p>{link3}</p>'
)

# Add cleaning safety link in the "que hacer" section
link4 = aff_link('mascarilla+N95+guantes+limpieza', 'Equipo de proteccion para limpieza en Amazon.es')
c = c.replace(
    '<h2>¿Que hacer si tienes un problema de palomas en tu propiedad?</h2>',
    f'<p>{link4}</p><h2>¿Que hacer si tienes un problema de palomas en tu propiedad?</h2>'
)

upd(15094, c)

# ============================================================
# Post 15121 - Lechuzas buena suerte
# ============================================================
print('=== 15121: Lechuzas suerte ===')

r = requests.get(f'{BASE}posts/15121', auth=AUTH, timeout=30)
c = r.json()['content']['rendered']

# Add owl figurine link after "cultura japonesa" section
link5 = aff_link('figura+buho+amuletos+suerte', 'Figuras de buhos para la buena suerte en Amazon.es')
c = c.replace(
    '<h2>En la cultura japonesa: simbolo de buena suerte</h2>',
    f'<p>{link5}</p><h2>En la cultura japonesa: simbolo de buena suerte</h2>'
)

# Add owl box link in "ciencia" section  
link6 = aff_link('caja+nido+lechuza+control+plagas', 'Cajas nido para lechuzas en Amazon.es')
c = c.replace(
    '<p>Por eso, cada vez mas agricultores en Chile instalan',
    f'<p>{link6}</p><p>Por eso, cada vez mas agricultores en Chile instalan'
)

upd(15121, c)

print()
print('Done')
