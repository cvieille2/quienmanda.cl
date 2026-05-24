import requests, json, time, re
from io import BytesIO
from collections import defaultdict
from PIL import Image

UA = 'AvesNativasChilenas/1.0 (https://avesnativaschilenas.cl)'
H = {'User-Agent': UA}
AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'
MAX = 1200

BIRD_QUERIES = {
    'loro': ['Loro chileno', 'Chilean parrot'],
    'paloma': ['Paloma domestica', 'Rock dove pigeon'],
    'pavo real': ['Pavo real', 'Peacock'],
    'lechuza': ['Lechuza Tyto alba', 'Barn owl'],
    'buho': ['Buho Bubo bubo', 'Eagle owl'],
    'zorzal': ['Zorzal Turdus', 'Thrush bird'],
    'cisne': ['Cisne Cygnus', 'Swan mute'],
    'zanate': ['Great-tailed grackle', 'Zanate cola grande'],
    'gaviota': ['Kelp gull', 'Gaviota dominicana'],
    'cuervo': ['Cuervo Corvus', 'Raven crow'],
    'cuervillo': ['Buff-necked ibis', 'Theristicus caudatus'],
    'condor': ['Condor andino', 'Andean condor'],
    'aguila': ['Aguila real', 'Golden eagle'],
    'halcon': ['Peregrine falcon', 'Halcon peregrino'],
    'cernicalo': ['American kestrel', 'Cernicalo chileno'],
    'falconidae': ['Falcon bird raptor', 'Peregrine falcon'],
    'tordo': ['Tordo chileno', 'Chilean blackbird'],
    'tapaculo': ['Tapaculo bird', 'Scelorchilus'],
    'gallina': ['Chilean hen', 'Gallina criolla'],
    'piuquen': ['Andean goose', 'Piuquen'],
    'chercan': ['House wren', 'Chercan Troglodytes'],
    'cometocino': ['Patagonian sierra finch', 'Cometocino'],
    'turca': ['Turca bird Chile', 'Pteroptochos megapodius'],
    'colilarga': ['Des Murs wiretail', 'Colilarga'],
    'churrin': ['Churrin Scytalopus', 'Tapaculo'],
    'colibri': ['Green-backed firecrown', 'Sephanoides sephaniodes'],
    'picaflor': ['Juan Fernandez firecrown', 'Sephanoides fernandensis'],
    'piquero': ['Peruvian booby', 'Piquero Sula variegata'],
    'gorrion': ['House sparrow', 'Gorrion Passer'],
    'canario': ['Canary bird', 'Canario Serinus'],
    'jilguero': ['Black-chinned siskin', 'Jilguero Spinus'],
    'golondrina': ['Chilean swallow', 'Golondrina Tachycineta'],
    'loica': ['Long-tailed meadowlark', 'Loica Sturnella'],
    'diuca': ['White-bridled finch', 'Diuca'],
    'chincol': ['Rufous-collared sparrow', 'Chincol Zonotrichia'],
    'garza': ['Great egret', 'Garza Ardea'],
    'flamenco': ['Chilean flamingo', 'Flamenco chileno'],
    'albatros': ['Wandering albatross', 'Albatros Diomedea'],
    'petrel': ['Petrel bird', 'Petrel Pterodroma'],
    'fardela': ['Shearwater bird', 'Pardela Puffinus'],
    'pato': ['Duck mallard', 'Pato Anas'],
    'ganso': ['Goose Anser', 'Ganso'],
    'avestruz': ['Ostrich', 'Avestruz Struthio'],
    'carpintero': ['Woodpecker', 'Carpintero chileno'],
    'urraca': ['Magpie', 'Urraca'],
}

GENERIC_QUERIES = {
    'aves': ['Small bird wildlife', 'Ave pajaro naturaleza'],
    'nido': ['Bird nest eggs', 'Nido ave ramas'],
    'jaula': ['Bird cage', 'Jaula pajaros'],
    'alimentacion': ['Bird eating seed', 'Ave comiendo'],
    'plumas': ['Bird feathers colorful', 'Plumas ave'],
    'simbolismo': ['Birds flying sky', 'Bandada aves'],
    'pajaro gris': ['Small gray bird', 'Gorrión pajaro gris'],
    'pajaro azul': ['Small blue bird', 'Bluebird'],
    'pajaro amarillo': ['Yellow bird goldfinch', 'Small yellow bird'],
    'pajaro blanco': ['White bird', 'Dove white'],
    'pajaro negro': ['Black bird', 'Blackbird'],
    'biblia': ['Dove sky', 'Birds flying'],
    'extincion': ['Endangered bird', 'Ave protegida'],
    'mascota': ['Pet bird cage', 'Ave mascota'],
    'region': ['Chilean bird landscape', 'Aves Chile'],
}

def get_posts_sin_imagen():
    sin = []
    for ptype in ['posts', 'pages']:
        page = 1
        while True:
            r = requests.get(f'{BASE}{ptype}?per_page=100&page={page}', auth=AUTH, timeout=60)
            if r.status_code != 200 or not r.json(): break
            for p in r.json():
                if not p.get('featured_media') or p['featured_media'] == 0:
                    sin.append({'id': p['id'], 'title': p['title']['rendered'], 'type': ptype})
            if len(r.json()) < 100: break
            page += 1
            time.sleep(1)
    return sin

def topic_from_title(t):
    t = t.lower()
    for bird, qs in BIRD_QUERIES.items():
        if re.search(r'\b' + re.escape(bird) + r'\b', t):
            return bird
    if any(w in t for w in ['nido','nidos','anidar']): return 'nido'
    if any(w in t for w in ['jaula','jaulas']): return 'jaula'
    if any(w in t for w in ['aliment','dieta','comen','comida']): return 'alimentacion'
    if any(w in t for w in ['pluma','plumaje']): return 'plumas'
    if any(w in t for w in ['significado','espiritual','simbolismo','sueñ','soñ']): return 'simbolismo'
    if 'biblia' in t: return 'biblia'
    if any(w in t for w in ['extincion','amenaz','proteger']): return 'extincion'
    if any(w in t for w in ['mascota','domestico']): return 'mascota'
    if any(w in t for w in ['region','region','chile','chilen']): return 'region'
    if 'gris' in t: return 'pajaro gris'
    if 'azul' in t: return 'pajaro azul'
    if 'amarill' in t: return 'pajaro amarillo'
    if 'blanco' in t: return 'pajaro blanco'
    if 'negro' in t: return 'pajaro negro'
    return 'aves'

def search_get_download(topic_key):
    queries = BIRD_QUERIES.get(topic_key, GENERIC_QUERIES.get(topic_key, [topic_key]))
    for q in queries:
        r = requests.get('https://commons.wikimedia.org/w/api.php', params={
            'action':'query','format':'json','list':'search','srsearch':q,'srnamespace':6,'srlimit':5
        }, headers=H, timeout=20)
        if r.status_code != 200: continue
        for x in r.json().get('query',{}).get('search',[]):
            ft = x['title']
            if 'File:' not in ft: continue
            ext = ft.lower().rsplit('.',1)[-1]
            if ext not in ('jpg','jpeg','png','gif','webp'): continue
            r2 = requests.get('https://commons.wikimedia.org/w/api.php', params={
                'action':'query','format':'json','titles':ft,'prop':'imageinfo','iiprop':'url|size','iiurlwidth':1920
            }, headers=H, timeout=20)
            if r2.status_code != 200: continue
            for pg in r2.json().get('query',{}).get('pages',{}).values():
                url = (pg.get('imageinfo') or [{}])[0].get('url')
                if not url: continue
                r3 = requests.get(url, headers=H, timeout=60, stream=True)
                if r3.status_code == 200 and 'image' in r3.headers.get('content-type',''):
                    img = Image.open(BytesIO(r3.content))
                    w,h = img.size
                    if w>MAX or h>MAX:
                        r4 = min(MAX/w, MAX/h)
                        img = img.resize((int(w*r4), int(h*r4)), Image.LANCZOS)
                    b = BytesIO()
                    if img.mode == 'RGBA': img = img.convert('RGB')
                    img.save(b, format='JPEG', optimize=True)
                    b.seek(0)
                    return b, img.size, ext
            time.sleep(0.5)
        time.sleep(1)
    return None, None, None

def upload_verify(data, topic_key):
    fn = f'aves-{topic_key}.jpg'
    files = {'file': (fn, data, 'image/jpeg')}
    hdrs = {'Content-Disposition': f'attachment; filename="{fn}"'}
    r = requests.post(f'{BASE}media', auth=AUTH, files=files, headers=hdrs, timeout=120)
    if r.status_code != 201: return None
    mid = r.json()['id']
    time.sleep(3)
    r2 = requests.get(f'{BASE}media/{mid}', auth=AUTH, timeout=20)
    if r2.status_code != 200: return None
    # Update title/alt
    title_seo = topic_key.replace('-',' ').title()
    requests.post(f'{BASE}media/{mid}', auth=AUTH, json={
        'title': title_seo + ' Ave',
        'alt_text': title_seo + ' fotografia de ave chilena'
    }, timeout=20)
    return mid

def assign_to_posts(mid, posts):
    ok = 0
    for p in posts:
        ptype = p['type']
        r = requests.post(f'{BASE}{ptype}/{p["id"]}', auth=AUTH, json={'featured_media': mid}, timeout=30)
        if r.status_code == 200 and r.json().get('featured_media') == mid:
            ok += 1
        time.sleep(0.5)
    return ok

# MAIN
print('Obteniendo posts sin imagen...')
sin = get_posts_sin_imagen()
print(f'Total: {len(sin)}')

# Group by topic
groups = defaultdict(list)
for p in sin:
    topic = topic_from_title(p['title'])
    groups[topic].append(p)

print(f'Temas unicos: {len(groups)}')
for t, ps in sorted(groups.items(), key=lambda x: -len(x[1])):
    print(f'  {t}: {len(ps)}')

print('\nProcesando temas...')
results = []
for topic_key, posts in sorted(groups.items(), key=lambda x: -len(x[1])):
    print(f'\n--- {topic_key} ({len(posts)} posts) ---')

    # Check if any already have images
    pending = []
    for p in posts:
        r = requests.get(f'{BASE}{p["type"]}/{p["id"]}', auth=AUTH, timeout=20)
        if r.status_code == 200 and r.json().get('featured_media', 0):
            pass  # already has image
        else:
            pending.append(p)
        time.sleep(0.3)

    if not pending:
        print('  Todos ya tienen imagen')
        results.append((topic_key, 'YA', 0))
        continue
    if len(pending) < len(posts):
        print(f'  {len(pending)}/{len(posts)} pendientes')

    img_data, size, ext = search_get_download(topic_key)
    if not img_data:
        print(f'  FAIL: no se encontro imagen')
        results.append((topic_key, 'FAIL-NOIMG', 0))
        continue

    mid = upload_verify(img_data, topic_key)
    if not mid:
        print(f'  FAIL: no persistio la subida')
        results.append((topic_key, 'FAIL-UPLOAD', 0))
        continue

    print(f'  Media {mid} subida y verificada ({size[0]}x{size[1]})')

    assigned = assign_to_posts(mid, pending)
    print(f'  Asignada a {assigned}/{len(pending)} posts')
    results.append((topic_key, 'OK', assigned))

    time.sleep(3)

print('\n\nRESUMEN:')
ok_total = sum(r[2] for r in results)
print(f'Posts con imagen asignada en esta ronda: {ok_total}')
for topic, status, count in results:
    print(f'  {topic}: {status} ({count})')
