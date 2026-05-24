"""
add_home_links_aves.py — Anade enlace a la homepage de avesnativaschilenas.cl
en todas las paginas/posts del sitio con variedad semantica de anchor text.

Estrategia:
- Cada post/page recibe un enlace contextual a la home si no tiene ya uno.
- Se usan ~15 variantes de anchor text, rotando para maximizar diversidad.
- El enlace se coloca al final del primer parrafo o en una transicion natural.
- Salta la homepage y paginas que ya enlazan a la home.
"""

import requests, re, random, time, sys

AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'
HOME_URL = 'https://avesnativaschilenas.cl/'

ANCHOR_VARIANTS = [
    "Aves nativas chilenas",
    "nuestro portal de aves chilenas",
    "la guía principal de aves de Chile",
    "avesnativaschilenas.cl",
    "el sitio de aves nativas de Chile",
    "nuestra página principal",
    "el portal ornitológico chileno",
    "la guía completa de aves chilenas",
    "nuestro sitio de aves nativas",
    "el directorio de aves de Chile",
    "la enciclopedia de aves chilenas",
    "nuestra guía de aves nativas",
    "aves de Chile",
    "el catálogo de aves chilenas",
    "nuestra plataforma de aves nativas",
]

HOME_PATTERNS = [
    r'href=["\']https?://(?:www\.)?avesnativaschilenas\.cl/?["\']',
]

def already_has_home_link(content):
    for pat in HOME_PATTERNS:
        if re.search(pat, content, re.IGNORECASE):
            return True
    return False

def pick_anchor(used_anchors):
    available = [a for a in ANCHOR_VARIANTS if a not in used_anchors]
    if not available:
        available = ANCHOR_VARIANTS[:]
    choice = random.choice(available)
    used_anchors.append(choice)
    if len(used_anchors) > len(ANCHOR_VARIANTS) * 2:
        used_anchors.clear()
    return choice

def add_home_link_to_content(content, anchor_text):
    home_link = f'<a href="{HOME_URL}">{anchor_text}</a>'
    p_end = content.find('</p>')
    if p_end != -1 and p_end < 500:
        insert_at = p_end + 4
        insert_html = f' Visita {home_link} para descubrir más especies.'
        return content[:insert_at] + insert_html + content[insert_at:]
    body_end = content.find('<!--')
    if body_end == -1 or body_end > 2000:
        body_end = len(content)
    insert_html = f'\n\n<p>Explora {home_link} para conocer todas las especies de nuestro país.</p>'
    return content[:body_end] + insert_html + content[body_end:]

def fetch_all_items(endpoint, post_type):
    all_items = []
    page = 1
    while True:
        url = f'{BASE}{endpoint}?per_page=100&page={page}&status=publish'
        resp = requests.get(url, auth=AUTH, timeout=30)
        if resp.status_code != 200:
            break
        items = resp.json()
        if not items:
            break
        all_items.extend(items)
        page += 1
    print(f'  {post_type}: {len(all_items)} publicados')
    return all_items

def process_item(item, ptype, stats, used_anchors):
    pid = item['id']
    link = item.get('link', '')
    content = item['content']['rendered'] or ''
    title = item['title']['rendered'] or '(sin titulo)'

    if pid in stats.get('skip_ids', set()):
        stats['skipped_known'] += 1
        return
    if not content.strip():
        stats['empty'] += 1
        return
    if already_has_home_link(content):
        stats['already_has_link'] += 1
        print(f'  SKIP (ya enlaza home): {link}')
        return

    anchor = pick_anchor(used_anchors)
    new_content = add_home_link_to_content(content, anchor)

    if new_content == content:
        stats['no_change'] += 1
        return

    data = {'content': new_content}
    resp = requests.post(f'{BASE}{ptype}/{pid}', auth=AUTH, json=data, timeout=30)
    if resp.status_code == 200:
        stats['updated'] += 1
        print(f'  OK [{ptype}/{pid}] {link} -> anchor: "{anchor}"')
    else:
        stats['errors'] += 1
        print(f'  ERROR [{ptype}/{pid}] {resp.status_code}: {resp.text[:150]}')

def main():
    print('=' * 60)
    print('add_home_links_aves.py — Enlace a home con variedad semantica')
    print(f'Target: {HOME_URL}')
    print('=' * 60)

    stats = {
        'updated': 0, 'already_has_link': 0, 'empty': 0,
        'no_change': 0, 'skipped_known': 0, 'errors': 0,
        'skip_ids': {14226},
    }
    used_anchors = []

    print('\n[1/2] Cargando posts...')
    posts = fetch_all_items('posts', 'posts')
    print(f'  Posts cargados: {len(posts)}')

    print('\n[2/2] Cargando paginas...')
    pages = fetch_all_items('pages', 'pages')
    print(f'  Paginas cargadas: {len(pages)}')

    all_items = [(p, 'posts') for p in posts] + [(pg, 'pages') for pg in pages]
    random.shuffle(all_items)

    print(f'\nProcesando {len(all_items)} items...')
    for item, ptype in all_items:
        process_item(item, ptype, stats, used_anchors)
        time.sleep(0.3)

    print('\n' + '=' * 60)
    print('RESUMEN')
    print('=' * 60)
    print(f'  Actualizados (enlace anadido): {stats["updated"]}')
    print(f'  Ya tenian enlace a home:       {stats["already_has_link"]}')
    print(f'  Saltados (contenido vacio):    {stats["empty"]}')
    print(f'  Sin cambios:                   {stats["no_change"]}')
    print(f'  Saltados (conocidos):          {stats["skipped_known"]}')
    print(f'  Errores:                       {stats["errors"]}')
    print(f'  Total procesados:              {len(all_items)}')
    print(f'  Anchors unicos usados:         {len(set(used_anchors))}')
    print(f'\nOK: {stats["updated"]} paginas ahora enlazan a la homepage con anchor variado.')

if __name__ == '__main__':
    main()
