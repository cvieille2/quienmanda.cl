import json
import base64
import urllib.request

CREDS = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
AUTH = 'Basic ' + base64.b64encode(f'{CREDS[0]}:{CREDS[1]}'.encode()).decode()
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2'
PARENT_ID = 163

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
        batch = api('GET', f'/posts?categories={PARENT_ID}&per_page=100&page={page}&_fields=id,slug,title,categories')
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

cats = api('GET', f'/categories?parent={PARENT_ID}&per_page=100&_fields=id,slug,name,count')
cat_by_slug = {c['slug']: c for c in cats}

posts = get_all_posts()
print(f'Total posts: {len(posts)}')

brand_keywords = [
    'yaheetech', 'vision', 'ferplast', 'arquivet', 'bps', 'buena pet shop',
    'flamingo', 'prevue', 'hendryx', 'vivohome', 'imac', 'voltrega',
    'yarnow', 'yardwe', 'kuandarm', 'bucatstate', 'feihai', 'chal',
    'happyyami', 'happinary', 'liuands', 'kerbl', 'relaxdays', 'colorday',
    'guinealoft', 'spacio', 'angelluck', 'hsthe', 'omem', 'alamber', 'm11',
    'm12', 'l11', 'l12', 'l01', 'l02', 'ranypet', 'besportble', 'aideegrowth',
    'bucolique', 'blumelhuber', 'wpsagek', 'jryxds', 'fvtvhev'
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

updated = 0
missing = []
for post in posts:
    child_slug = classify(post)
    child_id = cat_by_slug[child_slug]['id']
    current = post.get('categories', []) or []
    if child_id in current:
        continue
    new_cats = [c for c in current if c != PARENT_ID]
    new_cats.append(PARENT_ID)
    new_cats.append(child_id)
    api('PUT', f'/posts/{post["id"]}', {'categories': new_cats})
    updated += 1
    missing.append((post['slug'], child_slug))
    print(f'Updated {updated}: {post["slug"]} -> {child_slug}')

print(f'\nUpdated posts: {updated}')
for slug, group in missing:
    print(f'{slug} => {group}')

print('\nVerification counts:')
for slug, _ in desired:
    cid = cat_by_slug[slug]['id']
    batch = api('GET', f'/posts?categories={cid}&per_page=100&_fields=id')
    print(f'{slug}: {len(batch)}')
