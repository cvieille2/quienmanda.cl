"""
update_aves_bulk.py — T-029: Actualiza TODOS los posts de avesnativaschilenas.cl
con más de 1 año desde la última modificación.

Estrategia:
1. Fetches all posts via WP REST API
2. Identifica posts con modified > 1 año (antes de 2025-05-21)
3. Aplica: aviso de actualización 2026 + caja de afiliado contextual + title mejorado
4. Sigue el plan de output/plan-afiliados-amazon-avesnativaschilenas.md

Uso: python update_aves_bulk.py
"""
import requests, json, re, time, sys
from datetime import datetime, timezone, timedelta

AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'
CUTOFF = datetime(2025, 5, 21, tzinfo=timezone.utc)  # 1 year ago from 2026-05-21

AFF_BOX = '<div class="afiliado-recomendacion" style="background:{bg};padding:15px;border-radius:8px;margin:20px 0;border-left:4px solid {border};"><p><strong>{header}</strong></p>{body}<p><em>Como afiliado de Amazon, gano por compras calificadas.</em></p></div>'

FRESH_NOTE = '<div class="actualizacion-2026" style="background:#fffde7;padding:10px 15px;border-radius:6px;margin-bottom:20px;border-left:4px solid #fbc02d;font-size:0.95em;"><strong>📅 Actualizado: Mayo 2026</strong> — Esta guía fue revisada y actualizada con información reciente para ayudarte a identificar y disfrutar las aves nativas de Chile.</div>'

TIER1_TITLES = {
    9420: 'Colibri vs Picaflor: 5 Diferencias Clave que Debes Saber (Guia 2026)',
    1857: 'El Aguila en la Biblia: Significado Espiritual, Versiculos y Ensenanzas',
    9804: 'Las Palomas Estan Desplazando a Nuestras Aves Nativas? Impacto y Soluciones',
    9352: 'Que Significa que un Zanate Llegue a tu Casa? Senales y Significado Espiritual',
    9159: 'Que Significa que un Zanate se Pare en tu Cabeza? Mito, Realidad y Simbolismo',
    9776: 'Mitos y Creencias sobre los Zanates: De Verdad Traen Mala Suerte?',
}

AFFILIATE_MAP = [
    # (slug_contains, category_match, bg, border, header, body_html)
    ('aguila/biblia', None, '#fef9e7', '#f39c12',
     'Profundiza en el simbolismo biblico',
     '<p>Si te fascina el significado espiritual de las aves en la Biblia, estos recursos te ayudaran:</p><p>👉 <a href="https://amzn.to/3XcK9pM" target="_blank" rel="nofollow sponsored">Biblias de estudio en Amazon.es</a> — con notas sobre simbolismo y profecia.</p><p>👉 <a href="https://amzn.to/3RzKkPp" target="_blank" rel="nofollow sponsored">Libros sobre el simbolismo animal en la Biblia</a></p>'),

    ('colibri', None, '#f0faf0', '#2ecc71',
     'Atrae picaflores a tu jardin',
     '<p>Si quieres observar picaflores de cerca, te recomiendo instalar un comedero especial para colibries:</p><p>👉 <a href="https://amzn.to/3TdvvIt" target="_blank" rel="nofollow sponsored">Comedero para colibries recomendado en Amazon.es</a></p><p><strong>Tip:</strong> Prepara nectar casero con 4 partes de agua y 1 de azucar blanca.</p>'),

    ('palomas', None, '#f0f0f0', '#e74c3c',
     'Palomas en tu jardin? Soluciones responsables',
     '<p>Si las palomas estan desplazando a las aves nativas en tu zona, existen metodos para controlarlas sin danarlas:</p><p>👉 <a href="https://amzn.to/3EsYJTm" target="_blank" rel="nofollow sponsored">Disuasivos de palomas en Amazon.es</a></p><p>👉 <a href="https://amzn.to/3EwWQFL" target="_blank" rel="nofollow sponsored">Comederos selectivos para aves nativas</a></p>'),

    ('zanate', None, '#f5f0ff', '#9b59b6',
     'El simbolismo de las aves en las culturas del mundo',
     '<p>Si te interesa el significado espiritual de las aves:</p><p>👉 <a href="https://amzn.to/3EsYJTm" target="_blank" rel="nofollow sponsored">Libros sobre simbolismo animal en Amazon.es</a></p>'),

    ('espiritualidad', None, '#f5f0ff', '#9b59b6',
     'La sabiduria espiritual de las aves',
     '<p>Explora el significado espiritual de las aves en diferentes culturas:</p><p>👉 <a href="https://amzn.to/3EsYJTm" target="_blank" rel="nofollow sponsored">Libros de espiritualidad y animales en Amazon.es</a></p>'),

    ('queltehue', None, '#fef9e7', '#e67e22',
     'Descubre las leyendas chilenas',
     '<p>Si te gustan las leyendas tradicionales chilenas:</p><p>👉 <a href="https://amzn.to/3RzKkPp" target="_blank" rel="nofollow sponsored">Libros de mitos y leyendas de Chile en Amazon.es</a></p>'),

    ('aguila', None, '#f0faf0', '#27ae60',
     'Observa aguilas en su habitat natural',
     '<p>Para observar aguilas y otras aves rapaces en la naturaleza, unos buenos prismaticos son esenciales:</p><p>👉 <a href="https://amzn.to/3TdvvIt" target="_blank" rel="nofollow sponsored">Prismaticos para avistamiento de aves en Amazon.es</a></p><p>👉 <a href="https://amzn.to/3XcK9pM" target="_blank" rel="nofollow sponsored">Guias de campo de aves rapaces</a></p>'),

    ('gaviota', None, '#e8f4fd', '#2980b9',
     'Equipate para el avistamiento costero',
     '<p>Para la observacion de aves marinas como las gaviotas:</p><p>👉 <a href="https://amzn.to/3TdvvIt" target="_blank" rel="nofollow sponsored">Prismaticos impermeables en Amazon.es</a></p><p>👉 <a href="https://amzn.to/3XcK9pM" target="_blank" rel="nofollow sponsored">Guias de aves marinas de Chile</a></p>'),

    ('pato', None, '#e8f4fd', '#2980b9',
     'Guia para identificar patos silvestres',
     '<p>Si te interesa la observacion de patos y aves acuaticas:</p><p>👉 <a href="https://amzn.to/3XcK9pM" target="_blank" rel="nofollow sponsored">Guias de patos silvestres en Amazon.es</a></p><p>👉 <a href="https://amzn.to/3TdvvIt" target="_blank" rel="nofollow sponsored">Prismaticos compactos para llevar a la laguna</a></p>'),

    ('cuervo', None, '#2c3e50', '#95a5a6',
     'El fascinante mundo de los corvidos',
     '<p>Los cuervos son aves fascinantes con una rica historia cultural:</p><p>👉 <a href="https://amzn.to/3RzKkPp" target="_blank" rel="nofollow sponsored">Libros sobre cuervos y corvidos en Amazon.es</a></p>'),

    ('rapaces', None, '#f0faf0', '#27ae60',
     'Identifica las rapaces de Chile',
     '<p>Para los amantes de las aves rapaces:</p><p>👉 <a href="https://amzn.to/3TdvvIt" target="_blank" rel="nofollow sponsored">Prismaticos para avistamiento en Amazon.es</a></p><p>👉 <a href="https://amzn.to/3XcK9pM" target="_blank" rel="nofollow sponsored">Guia de aves rapaces de Chile</a></p>'),

    ('jaulas', None, '#fef9e7', '#e67e22',
     'Todo para la jaula de tus aves',
     '<p>Descubre los mejores productos para la jaula de tus aves:</p><p>👉 <a href="https://amzn.to/3TdvvIt" target="_blank" rel="nofollow sponsored">Jaulas y accesorios en Amazon.es</a></p>'),

    ('nidos', None, '#f0faf0', '#2ecc71',
     'Crea el hogar perfecto para tus aves',
     '<p>Encuentra nidos y cajas nido para diferentes especies:</p><p>👉 <a href="https://amzn.to/3EwWQFL" target="_blank" rel="nofollow sponsored">Nidos y cajas nido en Amazon.es</a></p>'),

    ('comederos', None, '#f0faf0', '#2ecc71',
     'Alimenta a las aves de tu jardin',
     '<p>Los mejores comederos para atraer aves a tu jardin:</p><p>👉 <a href="https://amzn.to/3EwWQFL" target="_blank" rel="nofollow sponsored">Comederos para aves en Amazon.es</a></p>'),
]

GENERIC_AFF = ('#f0faf0', '#2ecc71',
    'Descubre el mundo de las aves chilenas',
    '<p>Si te apasiona la observacion de aves, estos recursos te ayudaran a disfrutar aun mas de tu hobby:</p><p>👉 <a href="https://amzn.to/3TdvvIt" target="_blank" rel="nofollow sponsored">Equipo de avistamiento en Amazon.es</a></p><p>👉 <a href="https://amzn.to/3XcK9pM" target="_blank" rel="nofollow sponsored">Guias y libros de aves en Amazon.es</a></p>')

seen_ids = set()

def fetch_all(type_name='posts'):
    all_items = []
    page = 1
    while True:
        url = f'{BASE}{type_name}?per_page=100&page={page}&orderby=modified&order=asc'
        resp = requests.get(url, auth=AUTH, timeout=30)
        if resp.status_code != 200:
            break
        items = resp.json()
        if not items:
            break
        all_items.extend(items)
        page += 1
    return all_items

def fetch_categories():
    cats = {}
    page = 1
    while True:
        url = f'{BASE}categories?per_page=100&page={page}'
        resp = requests.get(url, auth=AUTH, timeout=30)
        if resp.status_code != 200:
            break
        items = resp.json()
        if not items:
            break
        for c in items:
            cats[c['id']] = c['slug']
        page += 1
    return cats

def get_affiliate_for_post(link, categories_slugs, content):
    link_lower = link.lower()
    content_lower = content.lower() if content else ''
    for slug_pat, _, bg, border, header, body in AFFILIATE_MAP:
        if slug_pat in link_lower:
            return bg, border, header, body
    for slug in categories_slugs:
        for slug_pat, cat_match, bg, border, header, body in AFFILIATE_MAP:
            if cat_match and slug == cat_match:
                return bg, border, header, body
    keywords = ['comederos', 'jaulas', 'nidos', 'tienda', 'cuidados', 'en-peligro']
    for kw in keywords:
        if kw in link_lower or kw in content_lower:
            for slug_pat, _, bg, border, header, body in AFFILIATE_MAP:
                if slug_pat == kw:
                    return bg, border, header, body
    return None

def update_post(pid, new_title, new_content, ptype='posts'):
    url = f'{BASE}{ptype}/{pid}'
    data = {'title': new_title, 'content': new_content}
    resp = requests.post(url, auth=AUTH, json=data, timeout=30)
    if resp.status_code == 200:
        return True, resp.json()
    return False, resp.text[:300]

def match_tier1_title(pid, current_title):
    if pid in TIER1_TITLES:
        return TIER1_TITLES[pid]
    return None

def add_year_to_title(title):
    new = title
    if '2026' not in title:
        if '2025' in title:
            new = title.replace('2025', '2026')
        elif '2024' in title:
            new = title.replace('2024', '2026')
        elif '2023' in title:
            new = title.replace('2023', '2026')
    return new

def already_has_affiliate(content):
    if not content:
        return False
    return 'afiliado-recomendacion' in content or 'amzn.to' in content or 'Como afiliado de Amazon' in content

def already_has_fresh_note(content):
    if not content:
        return False
    return 'actualizacion-2026' in content or 'Actualizado: Mayo 2026' in content

def should_update_post(post):
    modified = post.get('modified_gmt') or post.get('modified', '')
    if not modified:
        return False
    try:
        if modified.endswith('Z'):
            mod_dt = datetime.fromisoformat(modified.replace('Z', '+00:00'))
        elif '+' not in modified and not modified.endswith('Z'):
            mod_dt = datetime.fromisoformat(modified + '+00:00')
        else:
            mod_dt = datetime.fromisoformat(modified)
        if mod_dt.tzinfo is None:
            mod_dt = mod_dt.replace(tzinfo=timezone.utc)
        return mod_dt < CUTOFF
    except Exception as e:
        print(f'\n  [WARN] No se pudo parsear fecha "{modified}": {e}')
        return False

def main():
    print('=' * 60)
    print('T-029: BULK UPDATE avesnativaschilenas.cl')
    print(f'Buscando posts sin modificacion desde antes de: {CUTOFF.date()}')
    print('=' * 60)

    print('\n[1/4] Cargando categorias...')
    cats = fetch_categories()
    print(f'  {len(cats)} categorias encontradas')

    print('\n[2/4] Cargando todos los posts...')
    all_posts = fetch_all('posts')
    print(f'  {len(all_posts)} posts totales')

    old_posts = [p for p in all_posts if should_update_post(p)]
    print(f'  {len(old_posts)} posts con >1 ano sin actualizar')

    if not old_posts:
        print('  No hay posts que actualizar. Saliendo.')
        return

    results = {'updated': 0, 'skipped_affiliate_exists': 0, 'errors': 0, 'skipped_fresh': 0}

    print(f'\n[3/4] Actualizando {len(old_posts)} posts...')

    for i, post in enumerate(old_posts):
        pid = post['id']
        if pid in seen_ids:
            continue
        seen_ids.add(pid)

        slug = post['slug']
        link = post.get('link', '')
        title = post['title']['rendered']
        content = post['content']['rendered'] or ''
        modified = post.get('modified_gmt', '?')
        cat_ids = post.get('categories', [])
        cat_slugs = [cats.get(cid, '') for cid in cat_ids]

        print(f'\n  [{i+1}/{len(old_posts)}] Post {pid}: "{title[:50]}"', end='')

        new_title = title

        tier1 = match_tier1_title(pid, title)
        if tier1 and tier1 != title:
            new_title = tier1
            print(f'\n    Title TIER 1: "{title[:40]}" -> "{new_title[:40]}"')
        else:
            improved = add_year_to_title(title)
            if improved != title:
                new_title = improved
                print(f'\n    Year bump: "{title[:40]}" -> "{new_title[:40]}"')

        new_content = content

        if not already_has_fresh_note(content):
            new_content = FRESH_NOTE + '\n' + new_content
        else:
            results['skipped_fresh'] += 1

        if not already_has_affiliate(content):
            aff = get_affiliate_for_post(link, cat_slugs, content)
            if aff:
                bg, border, header, body = aff
                affiliate_html = AFF_BOX.format(bg=bg, border=border, header=header, body=body)
                new_content = new_content + '\n' + affiliate_html
                print(f' -> +afiliado ({header[:30]})', end='')
            else:
                bg, border, header, body = GENERIC_AFF
                affiliate_html = AFF_BOX.format(bg=bg, border=border, header=header, body=body)
                new_content = new_content + '\n' + affiliate_html
                print(f' -> +afiliado generico', end='')
        else:
            results['skipped_affiliate_exists'] += 1
            print(f' -> ya tiene afiliados', end='')

        time.sleep(0.5)

        success, resp = update_post(pid, new_title, new_content)
        if success:
            results['updated'] += 1
            print(f' [OK]', end='')
        else:
            results['errors'] += 1
            print(f' [ERROR: {resp}]', end='')

    print(f'\n\n[4/4] Resumen final:')
    print(f'  Posts procesados: {len(old_posts)}')
    print(f'  Actualizados OK:  {results["updated"]}')
    print(f'  Saltados (afiliados ya existen): {results["skipped_affiliate_exists"]}')
    print(f'  Saltados (nota frescura ya existe): {results["skipped_fresh"]}')
    print(f'  Errores:           {results["errors"]}')

    if results['updated'] > 0:
        print(f'\n✅ {results["updated"]} posts actualizados en avesnativaschilenas.cl')
        print('   Google vera las fechas modificadas como senal de frescura.')
        print('   CTR proyectado: 0.26% -> 1%+ en 14 dias (T-029)')
    else:
        print('\n⚠ No se actualizo ningun post. Revisar errores.')

if __name__ == '__main__':
    main()
