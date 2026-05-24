import requests, json, time, re, os
from io import BytesIO
from collections import defaultdict
from PIL import Image

UA = 'AvesNativasChilenas/1.0 (https://avesnativaschilenas.cl)'
HEADERS = {'User-Agent': UA}
AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'
MAX_DIM = 1200
SLEEP = 1.0

TOPIC_QUERIES = {
    'Loro chileno': ['Loro chileno Enicognathus leptorhynchus', 'Chilean parrot', 'Loro tricahue'],
    'Periquito': ['Periquito australiano Melopsittacus', 'Budgerigar'],
    'Cacatua': ['Cacatua Cacatua bird', 'Cockatoo'],
    'Paloma': ['Paloma domestica Columba livia', 'Rock dove pigeon'],
    'Pavo real': ['Pavo real Pavo cristatus', 'Peacock孔雀'],
    'Lechuza': ['Lechuza Tyto alba', 'Barn owl'],
    'Buho': ['Buho Bubo bubo', 'Eagle owl'],
    'Zorzal': ['Zorzal Turdus bird', 'Thrush bird'],
    'Cisne': ['Cisne Cygnus', 'Swan'],
    'Zanate': ['Great-tailed grackle Quiscalus mexicanus', 'Zanate cola grande'],
    'Gaviota': ['Kelp gull Larus dominicanus', 'Gaviota dominicana'],
    'Cuervo': ['Cuervo Corvus', 'Raven crow'],
    'Cuervillo': ['Cuervillo Theristicus caudatus', 'Buff-necked ibis'],
    'Condor': ['Condor andino Vultur gryphus', 'Andean condor'],
    'Aguila': ['Aguila real Aquila chrysaetos', 'Golden eagle'],
    'Falconidae': ['Falconidae falcon raptor', 'Peregrine falcon'],
    'rapaces': ['Ave rapaz halcon Falcon', 'Bird of prey raptor'],
    'carroña': ['Buitre carroñero Vulture', 'Vulture bird'],
    'Tordo': ['Tordo chileno Curaeus curaeus', 'Chilean blackbird'],
    'Tapaculo': ['Tapaculo Scelorchilus bird', 'Chilean tapaculo'],
    'Gallina criolla': ['Gallina criolla chilena', 'Chilean hen chicken'],
    'Piuquen': ['Piuquen Chloephaga melanoptera', 'Andean goose'],
    'Chercan': ['Chercan Troglodytes aedon', 'House wren'],
    'Cometocino': ['Cometocino Patagonico Phrygilus', 'Patagonian sierra finch'],
    'Turca': ['Turca Pteroptochos megapodius', 'Chilean turca'],
    'Colilarga': ['Colilarga Sylviorthorhynchus desmurii', 'Des Murs wiretail'],
    'Churrin': ['Churrin Scytalopus bird', 'TapaculoChurrin'],
    'Cernicalo': ['Cernicalo chileno Falco sparverius', 'American kestrel'],
    'Bandurria': ['Bandurria Theristicus caudatus', 'Buff-necked ibis'],
    'Colibri': ['Colibri chileno Sephanoides', 'Green-backed firecrown'],
    'Picaflor': ['Picaflor chileno Sephanoides sephaniodes', 'Hummingbird Chile'],
    'Piquero': ['Piquero peruano Sula variegata', 'Peruvian booby'],
    'Golondrina': ['Golondrina chilena Tachycineta leucopyga', 'Chilean swallow'],
    'Gorrion': ['Gorrion Passer domesticus', 'House sparrow'],
    'Canario': ['Canario Serinus canaria', 'Canary bird'],
    'Jilguero': ['Jilguero Spinus barbatus', 'Black-chinned siskin'],
    'Loica': ['Loica Sturnella loyca', 'Long-tailed meadowlark'],
    'Diuca': ['Diuca Diuca diuca', 'White-bridled finch'],
    'Chincol': ['Chincol Zonotrichia capensis', 'Rufous-collared sparrow'],
    'Garza': ['Garza Ardea alba', 'Great egret heron'],
    'Flamenco': ['Flamenco chileno Phoenicopterus', 'Chilean flamingo'],
    'Albatros': ['Albatros Diomedea', 'Wandering albatross'],
    'Petrel': ['Petrel gadfly Pterodroma', 'Petrel bird'],
    'Fardela': ['Fardela Puffinus', 'Shearwater bird'],
    'Pato': ['Pato Anas bird', 'Duck'],
    'Ganso': ['Ganso Anser', 'Goose'],
    'Avestruz': ['Avestruz Struthio camelus', 'Ostrich'],
    'Carpintero': ['Carpintero chileno Picidae', 'Woodpecker Chile'],
    'Martin pescador': ['Martin pescador Megaceryle torquata', 'Ringed kingfisher'],
    'Pajaro gris': ['Small gray bird perching', 'Gris bird'],
    'Pajaro azul': ['Blue bird small', 'Small blue bird'],
    'Pajaro negro': ['Black bird blackbird', 'Small black bird'],
    'Pajaro amarillo': ['Yellow bird goldfinch', 'Small yellow bird'],
    'Pajaro blanco': ['White bird white plumage', 'Small white bird'],
    'simbolismo aves': ['Birds flying sky spiritual', 'Bandada aves volando'],
    'aves biblia': ['Birds flying sky', 'Dove spiritual symbol'],
    'aves amenazadas': ['Ave amenazada especie protegida', 'Endangered bird species'],
    'aves mascota': ['Mascota ave domestica', 'Pet bird cage'],
    'aves volando': ['Birds flying sky', 'Bandada pajaros'],
    'nido aves': ['Bird nest eggs', 'Nido ave ramas'],
    'jaula aves': ['Bird cage perico', 'Jaula pajaros'],
    'alimentacion aves': ['Bird eating seed', 'Ave comiendo alpiste'],
    'plumas aves': ['Bird feathers plumage', 'Plumas coloridas ave'],
    'huevos aves': ['Bird nest eggs blue', 'Huevos nido ave'],
    'aves Chile': ['Aves chilenas nativas', 'Chilean birds nature'],
    'aves': ['Ave chilena nativa pajaro', 'Chilean bird wildlife'],
}


def extract_topic(title):
    t = title.lower()
    birds = [
        'loro', 'loros', 'perico', 'periquito', 'cacatua', 'cacatúa',
        'paloma', 'palomas',
        'pavo real',
        'lechuza', 'lechuzas',
        'buho', 'búho', 'buhos',
        'zorzal',
        'cisne',
        'zanate', 'zanates',
        'gaviota', 'gaviotas',
        'gaviotin', 'gaviotín',
        'cuervo',
        'cuervillo',
        'condor', 'cóndor',
        'aguila', 'águila', 'aguilas',
        'halcon', 'halcón',
        'cernicalo', 'cernícalo',
        'falconidae',
        'tordo',
        'tapaculo',
        'gallina criolla', 'gallina',
        'piuquen', 'piuquén',
        'chercan', 'chercán',
        'cometocino',
        'turca',
        'colilarga',
        'churrin', 'churrín',
        'colibri', 'colibrí',
        'picaflor',
        'piquero',
        'gorrion', 'gorrión',
        'bandurria',
        'cisne trompetero',
        'pinguino', 'pingüino', 'pinguinos',
        'pajaro gris', 'pájaro gris',
        'pajaro azul', 'pájaro azul',
        'pajaro amarillo', 'pájaro amarillo',
        'pajaro blanco', 'pájaro blanco',
        'pajaro negro', 'pájaro negro',
        'avestruz',
        'carpintero',
        'flamenco',
        'garza',
        'grulla',
        'martin pescador', 'martín pescador',
        'urraca',
        'golondrina',
        'loica',
        'diuca',
        'chincol',
        'jilguero',
        'canario',
        'fardela',
        'albatros',
        'petrel',
        'pato',
        'ganso',
        'rapaz', 'rapaces',
        'carroña', 'carroñero', 'carroñeras',
    ]

    for bird in birds:
        pattern = r'\b' + re.escape(bird) + r'\b'
        if re.search(pattern, t):
            if bird == 'gallina criolla':
                return 'Gallina criolla'
            elif bird == 'gallina':
                return 'Gallina criolla'
            elif bird == 'cisne trompetero':
                return 'Cisne'
            elif bird == 'pinguino':
                return 'aves Chile'
            elif bird == 'rapaz' or bird == 'rapaces':
                return 'rapaces'
            elif bird in ('carroña', 'carroñero', 'carroñeras'):
                return 'carroña'
            else:
                name = bird.replace('á', 'a').replace('é', 'e').replace('í', 'i').replace('ó', 'o').replace('ú', 'u').replace('ü', 'u').replace('ñ', 'n').title()
                return name

    if any(w in t for w in ['alimentacion', 'alimentación', 'dieta', 'comen', 'alimentan', 'dar de comer', 'comida', 'alimento']):
        return 'alimentacion aves'
    if any(w in t for w in ['nido', 'nidos', 'anidar']):
        return 'nido aves'
    if any(w in t for w in ['jaula', 'jaulas']):
        return 'jaula aves'
    if any(w in t for w in ['huevo', 'huevos']):
        return 'huevos aves'
    if any(w in t for w in ['pluma', 'plumas', 'plumaje']):
        return 'plumas aves'
    if any(w in t for w in ['vuelo', 'volar', 'volando']):
        return 'aves volando'
    if 'significado' in t or 'espiritual' in t or 'simbolismo' in t or 'sueño' in t or 'soñar' in t:
        return 'simbolismo aves'
    if 'biblia' in t or 'dios' in t or 'cristiana' in t:
        return 'aves biblia'
    if 'extincion' in t or 'extinción' in t or 'amenazada' in t or 'proteger' in t:
        return 'aves amenazadas'
    if 'mascota' in t or 'domestico' in t or 'doméstico' in t:
        return 'aves mascota'
    if 'region' in t or 'chile' in t or 'chilena' in t or 'chileno' in t:
        return 'aves Chile'
    return 'aves'


def search_wikimedia(query, limit=4):
    resp = requests.get(
        'https://commons.wikimedia.org/w/api.php',
        params={
            'action': 'query', 'format': 'json', 'list': 'search',
            'srsearch': query, 'srnamespace': 6, 'srlimit': limit, 'srprop': 'size'
        },
        headers=HEADERS, timeout=20
    )
    if resp.status_code != 200:
        return []
    data = resp.json()
    return [
        r['title'] for r in data.get('query', {}).get('search', [])
        if 'File:' in r['title'] and r['title'].lower().rsplit('.', 1)[-1] in ('jpg', 'jpeg', 'png', 'gif', 'webp')
    ]


def get_image_url(file_title):
    resp = requests.get(
        'https://commons.wikimedia.org/w/api.php',
        params={
            'action': 'query', 'format': 'json', 'titles': file_title,
            'prop': 'imageinfo', 'iiprop': 'url|size|extmetadata', 'iiurlwidth': 1920
        },
        headers=HEADERS, timeout=20
    )
    if resp.status_code != 200:
        return None, None
    data = resp.json()
    for pid, page in data.get('query', {}).get('pages', {}).items():
        if 'imageinfo' in page:
            info = page['imageinfo'][0]
            url = info.get('url') or info.get('thumburl')
            lic = ''
            if 'extmetadata' in info and 'LicenseShortName' in info['extmetadata']:
                lic = info['extmetadata']['LicenseShortName']['value']
            return url, lic
    return None, None


def download_and_resize(url):
    resp = requests.get(url, headers=HEADERS, timeout=60, stream=True)
    if resp.status_code != 200 or 'image' not in resp.headers.get('content-type', ''):
        return None
    img_data = BytesIO(resp.content)
    img = Image.open(img_data)
    w, h = img.size
    if w > MAX_DIM or h > MAX_DIM:
        ratio = min(MAX_DIM / w, MAX_DIM / h)
        img = img.resize((int(w * ratio), int(h * ratio)), Image.LANCZOS)
    buf = BytesIO()
    if img.mode == 'RGBA':
        img = img.convert('RGB')
    img.save(buf, format='JPEG', quality_=85, optimize=True)
    buf.seek(0)
    return buf


def upload_and_assign(image_data, post_ids, topic):
    ext = 'jpg'
    filename = f'aves-chilenas-{topic}-{post_ids[0]}.{ext}'
    files = {'file': (filename, image_data, 'image/jpeg')}
    headers = {'Content-Disposition': f'attachment; filename="{filename}"'}
    r = requests.post(BASE + 'media', auth=AUTH, files=files, headers=headers, timeout=120)
    if r.status_code != 201:
        return False, None
    mid = r.json()['id']

    # Set alt text and title
    title_seo = topic.replace('_', ' ').title()
    requests.post(f'{BASE}media/{mid}', auth=AUTH, json={
        'title': f'{title_seo} - Ave Chilena',
        'alt_text': f'{title_seo}, fotografia de ave nativa chilena',
        'caption': f'{title_seo} - Ave de Chile'
    }, timeout=20)

    success = 0
    for pid in post_ids:
        r2 = requests.post(f'{BASE}{pid["type"]}/{pid["id"]}', auth=AUTH, json={'featured_media': mid}, timeout=30)
        if r2.status_code == 200:
            success += 1
        time.sleep(0.3)
    return True, success


def find_image_for_topic(topic):
    queries = TOPIC_QUERIES.get(topic, [topic, topic + ' bird', topic + ' ave'])
    for query in queries:
        files = search_wikimedia(query)
        if not files:
            time.sleep(SLEEP)
            continue
        for ft in files[:4]:
            url, lic = get_image_url(ft)
            if not url:
                time.sleep(0.5)
                continue
            img_data = download_and_resize(url)
            if img_data:
                return img_data
            time.sleep(0.5)
        time.sleep(SLEEP)
    return None


# MAIN
with open('posts_sin_imagen.json', 'r', encoding='utf-8') as f:
    sin = json.load(f)

# Group by topic
topic_groups = defaultdict(list)
for p in sin:
    topic = extract_topic(p['title'])
    topic_groups[topic].append(p)

print(f'Posts total: {len(sin)}')
print(f'Temas unicos: {len(topic_groups)}')
print()

results = {}
for topic, posts in sorted(topic_groups.items(), key=lambda x: -len(x[1])):
    print(f'\n{"="*60}')
    print(f'TEMA: {topic} ({len(posts)} posts)')
    print(f'{"="*60}')

    # Skip if already assigned in a previous topic
    already = []
    remaining = []
    for p in posts:
        r = requests.get(f'{BASE}{p["type"]}/{p["id"]}', auth=AUTH, timeout=15)
        if r.status_code == 200 and r.json().get('featured_media', 0):
            already.append(p)
        else:
            remaining.append(p)
    posts = remaining
    if not posts:
        print('  Todos ya tienen imagen')
        results[topic] = f'Ya tienen ({len(already)})'
        continue
    if already:
        print(f'  {len(already)} ya tienen imagen, {len(posts)} pendientes')

    img_data = find_image_for_topic(topic)
    if not img_data:
        print(f'  NO SE ENCONTRO imagen para {topic}')
        results[topic] = 'FAIL'
        continue

    ok, count = upload_and_assign(img_data, posts, topic)
    if ok:
        print(f'  ASIGNADA a {count}/{len(posts)} posts (media subida)')
        results[topic] = f'OK ({count})'
    else:
        print(f'  ERROR subiendo imagen')
        results[topic] = 'FAIL'

    # Results per post
    for p in posts:
        r = requests.get(f'{BASE}{p["type"]}/{p["id"]}', auth=AUTH, timeout=15)
        fm = r.json().get('featured_media', 0) if r.status_code == 200 else 0
        status = 'OK' if fm else 'NO'
        print(f'    ID {p["id"]}: {status}')

    time.sleep(SLEEP * 2)

print(f'\n\n{"="*60}')
print('RESUMEN')
print(f'{"="*60}')
for topic, status in sorted(results.items()):
    print(f'  {topic}: {status}')
