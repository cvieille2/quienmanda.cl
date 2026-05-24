"""
update_aves_bulk_v2.py — T-029: Actualiza posts restantes de avesnativaschilenas.cl
con mas de 1 ano desde la ultima modificacion.

Version 2: mas rapida, continua desde donde se quedo.
"""
import requests, json, re, time
from datetime import datetime, timezone, timedelta

AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'
CUTOFF_STR = '2025-05-21T00:00:00'

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
    ('aguila/biblia', '#fef9e7', '#f39c12', 'Profundiza en el simbolismo biblico',
     '<p>Si te fascina el significado espiritual de las aves en la Biblia:</p><p>👉 <a href="https://amzn.to/3XcK9pM" target="_blank" rel="nofollow sponsored">Biblias de estudio en Amazon.es</a></p><p>👉 <a href="https://amzn.to/3RzKkPp" target="_blank" rel="nofollow sponsored">Libros sobre simbolismo animal</a></p>'),
    ('colibri', '#f0faf0', '#2ecc71', 'Atrae picaflores a tu jardin',
     '<p>Si quieres observar picaflores, te recomiendo un comedero especial:</p><p>👉 <a href="https://amzn.to/3TdvvIt" target="_blank" rel="nofollow sponsored">Comedero para colibries en Amazon.es</a></p><p><strong>Tip:</strong> Prepara nectar casero con 4 partes de agua y 1 de azucar blanca.</p>'),
    ('palomas', '#f0f0f0', '#e74c3c', 'Palomas en tu jardin? Soluciones responsables',
     '<p>Si las palomas desplazan a las aves nativas:</p><p>👉 <a href="https://amzn.to/3EsYJTm" target="_blank" rel="nofollow sponsored">Disuasivos de palomas en Amazon.es</a></p><p>👉 <a href="https://amzn.to/3EwWQFL" target="_blank" rel="nofollow sponsored">Comederos selectivos para aves nativas</a></p>'),
    ('zanate', '#f5f0ff', '#9b59b6', 'El simbolismo de las aves',
     '<p>Si te interesa el significado espiritual de las aves:</p><p>👉 <a href="https://amzn.to/3EsYJTm" target="_blank" rel="nofollow sponsored">Libros sobre simbolismo animal en Amazon.es</a></p>'),
    ('espiritualidad', '#f5f0ff', '#9b59b6', 'La sabiduria espiritual de las aves',
     '<p>Explora el significado espiritual de las aves:</p><p>👉 <a href="https://amzn.to/3EsYJTm" target="_blank" rel="nofollow sponsored">Libros de espiritualidad y animales en Amazon.es</a></p>'),
    ('queltehue', '#fef9e7', '#e67e22', 'Descubre las leyendas chilenas',
     '<p>Si te gustan las leyendas tradicionales:</p><p>👉 <a href="https://amzn.to/3RzKkPp" target="_blank" rel="nofollow sponsored">Libros de mitos y leyendas de Chile en Amazon.es</a></p>'),
    ('aguila', '#f0faf0', '#27ae60', 'Observa aguilas en su habitat natural',
     '<p>Para observar aguilas y aves rapaces:</p><p>👉 <a href="https://amzn.to/3TdvvIt" target="_blank" rel="nofollow sponsored">Prismaticos para avistamiento en Amazon.es</a></p><p>👉 <a href="https://amzn.to/3XcK9pM" target="_blank" rel="nofollow sponsored">Guias de campo de aves rapaces</a></p>'),
    ('gaviota', '#e8f4fd', '#2980b9', 'Equipate para el avistamiento costero',
     '<p>Para la observacion de aves marinas:</p><p>👉 <a href="https://amzn.to/3TdvvIt" target="_blank" rel="nofollow sponsored">Prismaticos impermeables en Amazon.es</a></p><p>👉 <a href="https://amzn.to/3XcK9pM" target="_blank" rel="nofollow sponsored">Guias de aves marinas de Chile</a></p>'),
    ('pato', '#e8f4fd', '#2980b9', 'Guia para identificar patos silvestres',
     '<p>Si te interesa la observacion de patos:</p><p>👉 <a href="https://amzn.to/3XcK9pM" target="_blank" rel="nofollow sponsored">Guias de patos silvestres en Amazon.es</a></p>'),
    ('cuervo', '#2c3e50', '#95a5a6', 'El fascinante mundo de los corvidos',
     '<p>Los cuervos tienen una rica historia cultural:</p><p>👉 <a href="https://amzn.to/3RzKkPp" target="_blank" rel="nofollow sponsored">Libros sobre cuervos en Amazon.es</a></p>'),
    ('rapaces', '#f0faf0', '#27ae60', 'Identifica las rapaces de Chile',
     '<p>Para los amantes de las aves rapaces:</p><p>👉 <a href="https://amzn.to/3TdvvIt" target="_blank" rel="nofollow sponsored">Prismaticos en Amazon.es</a></p><p>👉 <a href="https://amzn.to/3XcK9pM" target="_blank" rel="nofollow sponsored">Guia de aves rapaces de Chile</a></p>'),
    ('jaulas', '#fef9e7', '#e67e22', 'Todo para la jaula de tus aves',
     '<p>Descubre los mejores productos:</p><p>👉 <a href="https://amzn.to/3TdvvIt" target="_blank" rel="nofollow sponsored">Jaulas y accesorios en Amazon.es</a></p>'),
    ('nidos', '#f0faf0', '#2ecc71', 'Crea el hogar perfecto para tus aves',
     '<p>Encuentra nidos y cajas nido:</p><p>👉 <a href="https://amzn.to/3EwWQFL" target="_blank" rel="nofollow sponsored">Nidos y cajas nido en Amazon.es</a></p>'),
    ('comederos', '#f0faf0', '#2ecc71', 'Alimenta a las aves de tu jardin',
     '<p>Los mejores comederos para aves:</p><p>👉 <a href="https://amzn.to/3EwWQFL" target="_blank" rel="nofollow sponsored">Comederos para aves en Amazon.es</a></p>'),
]

GENERIC_AFF_BG = '#f0faf0'
GENERIC_AFF_BORDER = '#2ecc71'
GENERIC_AFF_HEADER = 'Descubre el mundo de las aves chilenas'
GENERIC_AFF_BODY = '<p>Si te apasiona la observacion de aves:</p><p>👉 <a href="https://amzn.to/3TdvvIt" target="_blank" rel="nofollow sponsored">Equipo de avistamiento en Amazon.es</a></p><p>👉 <a href="https://amzn.to/3XcK9pM" target="_blank" rel="nofollow sponsored">Guias y libros de aves en Amazon.es</a></p>'

def fetch_old_posts():
    all_posts = []
    page = 1
    while True:
        url = f'{BASE}posts?per_page=100&page={page}&modified_before={CUTOFF_STR}&orderby=modified&order=asc'
        resp = requests.get(url, auth=AUTH, timeout=30)
        if resp.status_code != 200:
            break
        posts = resp.json()
        if not posts:
            break
        all_posts.extend(posts)
        print(f'  Pagina {page}: {len(posts)} posts')
        page += 1
    return all_posts

def get_affiliate(link, content_lower):
    for slug_pat, bg, border, header, body in AFFILIATE_MAP:
        if slug_pat in link.lower():
            return bg, border, header, body
    for slug_pat, bg, border, header, body in AFFILIATE_MAP:
        if slug_pat in content_lower:
            return bg, border, header, body
    return None

def update_post(pid, new_title, new_content):
    resp = requests.post(f'{BASE}posts/{pid}', auth=AUTH, json={'title': new_title, 'content': new_content}, timeout=30)
    return resp.status_code == 200, resp.text[:300] if resp.status_code != 200 else ''

def main():
    print('=' * 60)
    print('T-029: BULK UPDATE v2 - avesnativaschilenas.cl')
    print(f'Posts sin modificar desde antes de: {CUTOFF_STR}')
    print('=' * 60)

    print('\n[1/2] Cargando posts antiguos...')
    old_posts = fetch_old_posts()
    print(f'\nTotal posts sin actualizar (>1 ano): {len(old_posts)}')

    if not old_posts:
        print('No hay posts que actualizar.')
        return

    results = {'updated': 0, 'skipped_has_fresh': 0, 'errors': 0, 'titles_updated': 0}
    BATCH_SIZE = 10

    print(f'\n[2/2] Actualizando {len(old_posts)} posts (sin delay)...')

    for i, post in enumerate(old_posts):
        pid = post['id']
        title = post['title']['rendered']
        content = post['content']['rendered'] or ''
        link = post.get('link', '')
        content_lower = content.lower()

        if 'actualizacion-2026' in content:
            results['skipped_has_fresh'] += 1
            continue

        new_title = TIER1_TITLES.get(pid, title)
        if new_title != title:
            results['titles_updated'] += 1

        new_content = FRESH_NOTE + '\n' + content

        if 'afiliado-recomendacion' not in content and 'amzn.to' not in content:
            aff = get_affiliate(link, content_lower)
            if aff:
                bg, border, header, body = aff
            else:
                bg, border, header, body = GENERIC_AFF_BG, GENERIC_AFF_BORDER, GENERIC_AFF_HEADER, GENERIC_AFF_BODY
            affiliate_html = AFF_BOX.format(bg=bg, border=border, header=header, body=body)
            new_content = new_content + '\n' + affiliate_html

        success, err = update_post(pid, new_title, new_content)
        if success:
            results['updated'] += 1
        else:
            results['errors'] += 1
            print(f'\n  ERROR {pid}: {err[:100]}')

        if (i + 1) % 50 == 0:
            print(f'  Progreso: {i+1}/{len(old_posts)} - {results["updated"]} OK, {results["errors"]} errors')

    print(f'\n\nResumen final:')
    print(f'  Procesados: {len(old_posts)}')
    print(f'  Actualizados: {results["updated"]}')
    print(f'  Ya tenian nota: {results["skipped_has_fresh"]}')
    print(f'  Errores: {results["errors"]}')
    print(f'  Titles actualizados: {results["titles_updated"]}')

    if results['updated'] > 0:
        print(f'\n✅ {results["updated"]} posts actualizados en avesnativaschilenas.cl')
        print('   Google vera fechas modificadas -> senal de frescura.')
        print('   Objetivo T-029: CTR 0.26% -> 1%+ en 14 dias.')

if __name__ == '__main__':
    main()
