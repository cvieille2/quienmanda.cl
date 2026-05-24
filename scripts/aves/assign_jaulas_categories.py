import json
import base64
import re
import urllib.request

CREDS = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
AUTH = 'Basic ' + base64.b64encode(f'{CREDS[0]}:{CREDS[1]}'.encode()).decode()
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2'
PARENT_ID = 163
PILLAR_URL = 'https://avesnativaschilenas.cl/jaulas/'

def api(method, endpoint, data=None):
    last_exc = None
    for _ in range(3):
        try:
            req = urllib.request.Request(f'{BASE}{endpoint}', method=method)
            req.add_header('Authorization', AUTH)
            req.add_header('User-Agent', 'opencode/1.0')
            if data is not None:
                req.add_header('Content-Type', 'application/json')
                req.data = json.dumps(data).encode('utf-8')
            with urllib.request.urlopen(req, timeout=60) as resp:
                return json.loads(resp.read())
        except Exception as exc:
            last_exc = exc
    raise last_exc

def get_all_posts():
    posts = []
    page = 1
    while True:
        batch = api('GET', f'/posts?categories={PARENT_ID}&per_page=100&page={page}&_fields=id,slug,title,categories,content&context=edit')
        if not batch:
            break
        posts.extend(batch)
        page += 1
        if len(batch) < 100:
            break
    return posts

desired = [
    ('portatiles', 'Portátiles'),
    ('para-loros', 'Para loros'),
    ('accesorios', 'Accesorios'),
    ('para-pajaros-pequenos', 'Para pájaros pequeños'),
    ('decorativas', 'Decorativas'),
    ('grandes', 'Grandes'),
    ('para-palomas', 'Para palomas'),
    ('modelos', 'Modelos'),
    ('marcas', 'Marcas'),
]

existing = api('GET', f'/categories?parent={PARENT_ID}&per_page=100&_fields=id,slug,name')
cat_by_slug = {c['slug']: c for c in existing}

for slug, name in desired:
    if slug not in cat_by_slug:
        created = api('POST', '/categories', {
            'name': name,
            'slug': slug,
            'parent': PARENT_ID,
        })
        cat_by_slug[slug] = created
        print(f'Created category: {slug} ({created["id"]})')
    else:
        print(f'Exists category: {slug} ({cat_by_slug[slug]["id"]})')

# Brand-specific posts go to marcas.
brand_keywords = [
    'yaheetech', 'vision', 'ferplast', 'arquivet', 'bps', 'buena pet shop',
    'flamingo', 'prevue', 'hendryx', 'vivohome', 'imac', 'voltrega',
    'yarnow', 'yardwe', 'kuandarm', 'bucatstate', 'feihai', 'chal',
    'happyyami', 'happinary', 'liuands', 'kerbl', 'relaxdays', 'colorday',
    'guinealoft', 'spacio', 'angelluck', 'hsthe', 'omem', 'alamber', 'm11',
    'm12', 'l11', 'l12', 'l01', 'l02', 'ranypet', 'besportble', 'aideegrowth',
    'bucolique', 'blumelhuber', 'wpsagek', 'jryxds', 'fvtvhev', 'voli',
]

def classify(post):
    s = (post['slug'] + ' ' + post['title']['rendered']).lower()
    if any(k in s for k in brand_keywords):
        return 'marcas'
    if any(k in s for k in ['cubierta', 'funda', 'bandeja', 'rejilla', 'manija', 'manilla', 'cierre', 'gancho', 'puerta', 'cerradura', 'cepillo', 'limpieza', 'malla', 'protector', 'escudo', 'accesorio', 'accesorios', 'cupula', 'pie', 'red de proteccion']):
        return 'accesorios'
    if any(k in s for k in ['portatil', 'viaje', 'plegable', 'transporte', 'transportador', 'viajar', 'portabebes', 'aire libre']):
        return 'portatiles'
    if any(k in s for k in ['loro', 'cacatua', 'conure', 'ninfa']):
        return 'para-loros'
    if any(k in s for k in ['periquito', 'canario']):
        return 'para-pajaros-pequenos'
    if 'paloma' in s:
        return 'para-palomas'
    if any(k in s for k in ['vintage', 'decorativa', 'decoracion', 'madera', 'pagoda', 'shabby', 'bohemio', 'colgante']):
        return 'decorativas'
    if any(k in s for k in ['grande', 'aviario', 'jaulon', 'espacioso', 'lujo', 'guinealoft']):
        return 'grandes'
    return 'modelos'

posts = get_all_posts()
print(f'Total posts: {len(posts)}')

counts = {}
updated = 0
for post in posts:
    child_slug = classify(post)
    counts[child_slug] = counts.get(child_slug, 0) + 1
    child_id = cat_by_slug[child_slug]['id']
    current = post.get('categories', []) or []
    new_cats = [c for c in current if c != PARENT_ID]
    if child_id not in new_cats:
        new_cats.append(child_id)

    # Update categories
    api('PUT', f'/posts/{post["id"]}', {'categories': new_cats})

    # Ensure the internal link points to the right sub-pillar page.
    post_data = api('GET', f'/posts/{post["id"]}?context=edit')
    raw = post_data.get('content', {}).get('raw', '')
    target_url = f'{PILLAR_URL}{child_slug}/'
    if target_url not in raw:
        # Replace any existing jaulas sub-pillar URL with the new one.
        raw2 = re.sub(
            r'https://avesnativaschilenas\.cl/jaulas/(?:portatiles|para-loros|accesorios|para-pajaros-pequenos|decorativas|grandes|para-palomas|modelos|marcas)/',
            target_url,
            raw,
            count=1,
        )
        if raw2 == raw:
            raw2 = raw.rstrip() + f'\n\n<p>👉 Si quieres ver más opciones y comparar características, visita nuestra <a href="{target_url}">{child_slug.replace("-", " ")}</a>.</p>'
        api('PUT', f'/posts/{post["id"]}', {'content': raw2})

    updated += 1
    if updated % 20 == 0:
        print(f'Updated {updated} posts')

print('Counts by child category:')
for slug, total in sorted(counts.items()):
    print(f'  {slug}: {total}')

# Verification: count posts per child category from WP.
print('\nVerification:')
for slug, cat in desired:
    cid = cat_by_slug[slug]['id']
    batch = api('GET', f'/posts?categories={cid}&per_page=100&_fields=id')
    print(f'  {slug}: {len(batch)} posts')
