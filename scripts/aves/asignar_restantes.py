import requests, json, time, re
from io import BytesIO
from PIL import Image

UA = 'AvesNativasChilenas/1.0 (https://avesnativaschilenas.cl)'
HEADERS = {'User-Agent': UA}
AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'
MAX_DIM = 1200

TOPIC_MAP = {
    'Chercan': ['Chercan Troglodytes aedon', 'House wren bird'],
    'Cuervillo': ['Cuervillo Theristicus caudatus', 'Buff-necked ibis'],
    'Cometocino': ['Cometocino Phrygilus patagonicus', 'Patagonian sierra finch'],
    'Turca': ['Turca Pteroptochos megapodius', 'Chilean turca bird'],
    'Colilarga': ['Colilarga Des Murs wiretail', 'Sylviorthorhynchus desmurii'],
    'Churrin': ['Churrin Scytalopus bird', 'Churrin del norte Chile'],
    'Cernicalo': ['Cernicalo chileno Falco sparverius', 'American kestrel Chile'],
    'Pajaro Azul': ['Small blue bird', 'Blue bird perching'],
    'carroña': ['Vulture bird feeding', 'Buitre carronero'],
    'Lechuza': ['Lechuza Tyto alba', 'Barn owl'],
    'Bandurria': ['Bandurria Theristicus caudatus', 'Buff-necked ibis bird'],
    'Picaflor': ['Picaflor chileno Sephanoides sephaniodes', 'Juan Fernandez firecrown'],
    'Pajaro Amarillo': ['Small yellow bird', 'Yellow bird goldfinch'],
    'Pajaro': ['Bird wildlife nature', 'Small bird perching branch'],
    'Albatros': ['Albatros Diomedea', 'Wandering albatross'],
    'Condor': ['Condor andino Vultur gryphus', 'Andean condor'],
    'Halcón': ['Halcon peregrino Falco peregrinus', 'Peregrine falcon'],
    'jaula': ['Bird cage parrot', 'Jaula para aves'],
    'jaula loro': ['Bird cage parrot', 'Jaula loro grande'],
    'jaula accesorios': ['Bird cage accessories', 'Accesorios jaula aves'],
    'jaula pequena': ['Small bird cage canary', 'Jaula pequena pajaros'],
    'jaula decorativa': ['Bird cage decorative vintage', 'Jaula madera aves'],
    'jaula paloma': ['Pigeon cage dove', 'Jaula palomas transporte'],
    'jaula grande': ['Large aviary bird cage', 'Aviario grande jaula'],
    'jaula portatil': ['Portable bird cage travel', 'Jaula portatil aves'],
    'nido ceramica': ['Bird nest ceramic', 'Nido ceramica aves'],
    'casa pajaro': ['Bird house wooden', 'Casa pajaros madera'],
    'comedero': ['Bird feeder window', 'Comedero aves exterior'],
    'malla proteccion': ['Bird netting fruit tree', 'Malla pajaros frutales'],
    'percha loro': ['Bird perch natural wood', 'Percha loro madera'],
}

def search_wm(query):
    r = requests.get('https://commons.wikimedia.org/w/api.php', params={
        'action': 'query', 'format': 'json', 'list': 'search',
        'srsearch': query, 'srnamespace': 6, 'srlimit': 4, 'srprop': 'size'
    }, headers=HEADERS, timeout=20)
    if r.status_code != 200: return []
    return [x['title'] for x in r.json().get('query',{}).get('search',[])
            if 'File:' in x['title'] and x['title'].lower().rsplit('.',1)[-1] in ('jpg','jpeg','png','gif','webp')]

def get_img_url(title):
    r = requests.get('https://commons.wikimedia.org/w/api.php', params={
        'action': 'query', 'format': 'json', 'titles': title,
        'prop': 'imageinfo', 'iiprop': 'url|size', 'iiurlwidth': 1920
    }, headers=HEADERS, timeout=20)
    if r.status_code != 200: return None
    for pg in r.json().get('query',{}).get('pages',{}).values():
        if 'imageinfo' in pg:
            return pg['imageinfo'][0].get('url') or pg['imageinfo'][0].get('thumburl')
    return None

def get_img(url):
    r = requests.get(url, headers=HEADERS, timeout=60, stream=True)
    if r.status_code != 200 or 'image' not in r.headers.get('content-type',''): return None
    img = Image.open(BytesIO(r.content))
    w,h = img.size
    if w > MAX_DIM or h > MAX_DIM:
        r2 = min(MAX_DIM/w, MAX_DIM/h)
        img = img.resize((int(w*r2), int(h*r2)), Image.LANCZOS)
    b = BytesIO()
    if img.mode == 'RGBA': img = img.convert('RGB')
    img.save(b, format='JPEG', optimize=True)
    b.seek(0)
    return b

def assign(post_id, ptype, img_data, topic):
    ext = 'jpg'
    fn = f'aves-{topic}-{post_id}.{ext}'
    files = {'file': (fn, img_data, 'image/jpeg')}
    hdrs = {'Content-Disposition': f'attachment; filename="{fn}"'}
    r = requests.post(f'{BASE}media', auth=AUTH, files=files, headers=hdrs, timeout=120)
    if r.status_code != 201:
        print(f'  ERROR upload {post_id}: {r.status_code}')
        return False
    mid = r.json()['id']
    title_seo = topic.replace('-', ' ').title()
    alt_seo = title_seo + ' fotografia ave chilena'
    requests.post(f'{BASE}media/{mid}', auth=AUTH, json={
        'title': title_seo,
        'alt_text': alt_seo,
    }, timeout=15)
    r2 = requests.post(f'{BASE}{ptype}/{post_id}', auth=AUTH, json={'featured_media': mid}, timeout=30)
    if r2.status_code == 200:
        print(f'  OK ID {post_id} -> media {mid}')
        return True
    print(f'  ERROR assign {post_id}: {r2.status_code}')
    return False

def process(post_id, title, ptype):
    t = title.lower()
    # Determine topic
    if 'chercan' in t: topic = 'Chercan'
    elif 'cuervillo' in t: topic = 'Cuervillo'
    elif 'cometocino' in t: topic = 'Cometocino'
    elif 'turca' in t: topic = 'Turca'
    elif 'colilarga' in t: topic = 'Colilarga'
    elif 'churrin' in t or 'churrín' in t: topic = 'Churrin'
    elif 'cernicalo' in t or 'cernícalo' in t: topic = 'Cernicalo'
    elif 'pajaro azul' in t or 'pájaro azul' in t or 'p�jaro azul' in t: topic = 'Pajaro Azul'
    elif 'carron' in t or 'carroñ' in t: topic = 'carroña'
    elif 'lechuza' in t: topic = 'Lechuza'
    elif 'bandurria' in t: topic = 'Bandurria'
    elif 'picaflor' in t: topic = 'Picaflor'
    elif 'pajaro amarillo' in t or 'pájaro amarillo' in t or 'p�jaro amarillo' in t: topic = 'Pajaro Amarillo'
    elif 'albatros' in t: topic = 'Albatros'
    elif 'condor' in t: topic = 'Condor'
    elif 'halcon' in t or 'halcón' in t: topic = 'Halcón'
    elif 'jaula' in t and 'paloma' in t: topic = 'jaula paloma'
    elif 'jaula' in t and ('grande' in t or 'aviar' in t): topic = 'jaula grande'
    elif 'jaula' in t and ('decor' in t or 'vintage' in t or 'madera' in t): topic = 'jaula decorativa'
    elif 'jaula' in t and ('portatil' in t or 'portátil' in t or 'viaje' in t): topic = 'jaula portatil'
    elif 'jaula' in t and ('peque' in t or 'canario' in t or 'periquito' in t or 'ninfa' in t): topic = 'jaula pequena'
    elif 'jaula' in t and ('accesorio' in t): topic = 'jaula accesorios'
    elif 'jaula' in t and ('loro' in t): topic = 'jaula loro'
    elif 'jaula' in t: topic = 'jaula'
    elif 'nido' in t and 'ceram' in t: topic = 'nido ceramica'
    elif 'casa' in t and ('pajaro' in t or 'pájaro' in t or 'p�jaro' in t): topic = 'casa pajaro'
    elif 'comedero' in t: topic = 'comedero'
    elif 'malla' in t: topic = 'malla proteccion'
    elif 'percha' in t: topic = 'percha loro'
    else: topic = 'aves'

    queries = TOPIC_MAP.get(topic, [topic, topic + ' bird'])
    for q in queries:
        files = search_wm(q)
        if not files: continue
        for ft in files[:3]:
            url = get_img_url(ft)
            if not url: continue
            img = get_img(url)
            if img:
                return assign(post_id, ptype, img, topic.replace(' ','-'))
            time.sleep(0.5)
        time.sleep(1)
    print(f'  FAIL ID {post_id}: no image found')
    return False

# Remaining posts
posts = [
    (11824, 'posts'), (11292, 'posts'), (11170, 'posts'), (11165, 'posts'),
    (11131, 'posts'), (11126, 'posts'), (11038, 'posts'), (9995, 'posts'),
    (9760, 'posts'), (9758, 'posts'), (9488, 'posts'), (9449, 'posts'),
    (9435, 'posts'), (9394, 'posts'), (9389, 'posts'), (9388, 'posts'),
    (9386, 'posts'), (9384, 'posts'), (9377, 'posts'), (9157, 'posts'),
    (8664, 'posts'), (8649, 'posts'), (8647, 'posts'), (8641, 'posts'),
    (8517, 'posts'), (8228, 'posts'), (8220, 'posts'), (8218, 'posts'),
    (8214, 'posts'), (7937, 'posts'), (7894, 'posts'),
    (14860, 'pages'), (14671, 'pages'), (14670, 'pages'), (14668, 'pages'),
    (14667, 'pages'), (14665, 'pages'), (14664, 'pages'), (14663, 'pages'),
    (14661, 'pages'),
]

titles = {
    11824: 'Chercan', 11292: 'Cuervillo', 11170: 'Cometocino',
    11165: 'Turca', 11131: 'Colilarga', 11126: 'Churrin',
    11038: 'Cernicalo', 9995: 'Pajaro Azul', 9760: 'Carroneras',
    9758: 'Lechuzas', 9488: 'Bandurria', 9449: 'Picaflor J Fernandez',
    9435: 'Pajaro Amarillo', 9394: 'Extincion', 9389: 'Extincion',
    9388: 'Pajaro', 9386: 'Albatros', 9384: 'Condor',
    9377: 'Pajaro en Casa', 9157: 'Halcon Peregrino',
    8664: 'Jaula', 8649: 'Comedero', 8647: 'Percha',
    8641: 'Nido', 8517: 'Casa Pajaro', 8228: 'Malla',
    8220: 'Nido', 8218: 'Jaula', 8214: 'Comedero',
    7937: 'Nido', 7894: 'Jaula',
    14860: 'Jaulas Marcas', 14671: 'Modelos Jaulas', 14670: 'Jaulas Palomas',
    14668: 'Jaulas Grandes', 14667: 'Jaulas Decorativas', 14665: 'Jaulas Pequenas',
    14664: 'Accesorios Jaulas', 14663: 'Jaulas Loros', 14661: 'Jaulas Portatiles',
}

results = []
for pid, ptype in posts:
    ttl = titles.get(pid, '?')
    print(f'\nID {pid} ({ptype}): {ttl}')
    try:
        ok = process(pid, titles.get(pid, ''), ptype)
        results.append((pid, ok))
    except Exception as e:
        print(f'  ERROR: {e}')
        results.append((pid, False))
    time.sleep(2)

print(f'\n\nRESUMEN: {sum(1 for _,o in results if o)}/{len(results)} OK')
for pid, ok in results:
    print(f'  ID {pid}: {"OK" if ok else "FAIL"}')
