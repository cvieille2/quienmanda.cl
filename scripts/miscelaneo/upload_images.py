import requests, json, time, re
from io import BytesIO
from collections import defaultdict
from PIL import Image

UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
H = {'User-Agent': UA}
AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'
MAX = 1200

TOPIC_IMG = {
    'lechuza': 'Tyto alba barn owl',
    'paloma': 'Columba livia rock dove',
    'aguila': 'Aquila chrysaetos golden eagle',
    'colibri': 'Sephanoides sephaniodes firecrown',
    'loro': 'Chilean parrot Enicognathus',
    'periquito': 'Melopsittacus undulatus budgerigar',
    'zanate': 'Quiscalus mexicanus grackle',
    'gaviota': 'Larus dominicanus kelp gull',
    'cuervo': 'Corvus corax raven',
    'condor': 'Vultur gryphus condor andean',
    'halcon': 'Falco peregrinus peregrine',
    'cernicalo': 'Falco sparverius kestrel',
    'tordo': 'Tordo chileno Curaeus',
    'tapaculo': 'Scelorchilus albicollis tapaculo',
    'piuquen': 'Chloephaga melanoptera goose',
    'chercan': 'Troglodytes aedon wren',
    'pavo real': 'Pavo cristatus peacock',
    'zorzal': 'Turdus falcklandii thrush',
    'cisne': 'Cygnus melancoryphus black necked',
    'gorrion': 'Passer domesticus sparrow',
    'golondrina': 'Tachycineta leucopyga swallow',
    'canario': 'Serinus canaria canary',
    'jilguero': 'Spinus barbatus siskin',
    'garza': 'Ardea alba egret',
    'flamenco': 'Phoenicopterus chilensis flamingo',
    'albatros': 'Diomedea exulans albatross',
    'piquero': 'Sula variegata booby',
    'fardela': 'Puffinus creatopus shearwater',
    'avestruz': 'Struthio camelus ostrich',
    'carpintero': 'Veniliornis lignarius woodpecker',
    'gallina': 'Gallus gallus hen chicken',
    'cometocino': 'Phrygilus patagonicus finch',
    'turca': 'Pteroptochos megapodius',
    'colilarga': 'Sylviorthorhynchus desmurii wiretail',
    'churrin': 'Scytalopus magellanicus tapaculo',
    'cuervillo': 'Theristicus caudatus ibis',
    'rapaz': 'Buteo polyosoma hawk',
    'carroña': 'Coragyps atratus vulture',
    'nido': 'bird nest eggs tree',
    'jaula': 'bird cage parrot',
    'alimentacion': 'bird eating seed feeder',
    'extincion': 'endangered bird species',
    'biblia': 'dove flying sky light',
    'simbolismo': 'birds flying flock sky',
    'aves': 'bird wildlife nature small',
}

def wiki_search(query):
    r = requests.get('https://commons.wikimedia.org/w/api.php', params={
        'action':'query','format':'json','list':'search','srsearch':query,
        'srnamespace':6,'srlimit':5
    }, headers=H, timeout=20)
    if r.status_code != 200: return []
    return [x['title'] for x in r.json().get('query',{}).get('search',[])
            if 'File:' in x['title'] and x['title'].lower().rsplit('.',1)[-1] in ('jpg','jpeg','png','webp')]

def wiki_img_url(file_title):
    r = requests.get('https://commons.wikimedia.org/w/api.php', params={
        'action':'query','format':'json','titles':file_title,
        'prop':'imageinfo','iiprop':'url|size','iiurlwidth':1920
    }, headers=H, timeout=20)
    if r.status_code != 200: return None
    for pg in r.json().get('query',{}).get('pages',{}).values():
        if 'imageinfo' in pg:
            return pg['imageinfo'][0].get('url')

def download_resize(url):
    r = requests.get(url, headers=H, timeout=120, stream=True)
    if r.status_code != 200 or 'image' not in r.headers.get('content-type',''): return None
    img = Image.open(BytesIO(r.content))
    w, h = img.size
    if w > MAX or h > MAX:
        ratio = min(MAX/w, MAX/h)
        img = img.resize((int(w*ratio), int(h*ratio)), Image.LANCZOS)
    b = BytesIO()
    if img.mode == 'RGBA': img = img.convert('RGB')
    img.save(b, format='JPEG', optimize=True)
    b.seek(0)
    return b

def upload_to_wp(data, topic_key):
    fn = f'aves-{topic_key}.jpg'
    files = {'file': (fn, data, 'image/jpeg')}
    hdrs = {'Content-Disposition': f'attachment; filename="{fn}"'}
    r = requests.post(f'{BASE}media', auth=AUTH, files=files, headers=hdrs, timeout=120)
    if r.status_code != 201: return None
    mid = r.json()['id']
    time.sleep(3)
    # Verify it persisted
    r2 = requests.get(f'{BASE}media/{mid}', auth=AUTH, timeout=20)
    if r2.status_code != 200: return None
    # Set SEO title/alt
    t = topic_key.replace('_',' ').title()
    requests.post(f'{BASE}media/{mid}', auth=AUTH, json={
        'title': t,
        'alt_text': f'{t} - Fotografia de ave chilena nativa'
    }, timeout=20)
    return mid

# Load posts
with open('sin_imagen.json', 'r', encoding='utf-8') as f:
    sin = json.load(f)

# Group by topic
groups = defaultdict(list)
for p in sin:
    t = p['title'].lower()
    topic = 'aves'
    for kw, tp in [
        ('paloma','paloma'),('palomas','paloma'),
        ('lechuza','lechuza'),('buho','lechuza'),('búho','lechuza'),
        ('aguila','aguila'),('águila','aguila'),
        ('colibri','colibri'),('colibrí','colibri'),('picaflor','colibri'),
        ('zanate','zanate'),('zanates','zanate'),
        ('gaviota','gaviota'),('gaviotas','gaviota'),
        ('cuervo','cuervo'),('cuervillo','cuervillo'),
        ('condor','condor'),('cóndor','condor'),
        ('halcon','halcon'),('halcón','halcon'),('cernicalo','cernicalo'),('cernícalo','cernicalo'),
        ('tordo','tordo'),('tapaculo','tapaculo'),
        ('piuquen','piuquen'),('chercan','chercan'),('chercán','chercan'),
        ('pavo real','pavo real'),
        ('zorzal','zorzal'),('cisne','cisne'),
        ('gorrion','gorrion'),('gorrión','gorrion'),
        ('golondrina','golondrina'),('canario','canario'),
        ('jilguero','jilguero'),('garza','garza'),
        ('flamenco','flamenco'),('albatros','albatros'),
        ('piquero','piquero'),('fardela','fardela'),
        ('avestruz','avestruz'),('carpintero','carpintero'),
        ('gallina','gallina'),('cometocino','cometocino'),
        ('turca','turca'),('colilarga','colilarga'),
        ('churrin','churrin'),('churrín','churrin'),
        ('rapaz','rapaz'),('rapaces','rapaz'),
        ('carroñ','carroña'),
        ('jaula','jaula'),('nido','nido'),('nidos','nido'),
        ('aliment','alimentacion'),
        ('extincion','extincion'),('extinción','extincion'),
        ('biblia','biblia'),
        ('significado espiritual','simbolismo'),('simbolismo','simbolismo'),
        ('loro','loro'),('loros','loro'),('perico','loro'),
        ('periquito','periquito'),
    ]:
        if kw in t:
            topic = tp
            break
    groups[topic].append(p)

results = []
for topic, posts in sorted(groups.items(), key=lambda x: -len(x[1])):
    print(f'\n--- {topic} ({len(posts)} posts) ---')

    # Filter to still-need-image
    pending = []
    for p in posts:
        r = requests.get(f'{BASE}{p["type"]}/{p["id"]}', auth=AUTH, timeout=20)
        if r.status_code == 200 and r.json().get('featured_media', 0) == 0:
            pending.append(p)
        time.sleep(0.3)

    if not pending:
        print('  Todos ya tienen imagen')
        results.append((topic, 'SKIP', 0))
        continue

    # Search image
    queries = [TOPIC_IMG.get(topic, topic)] + [topic + ' bird', topic + ' ave']
    img_data = None
    for q in queries:
        files = wiki_search(q)
        if not files: continue
        for ft in files[:3]:
            url = wiki_img_url(ft)
            if not url: continue
            img_data = download_resize(url)
            if img_data: break
            time.sleep(0.5)
        if img_data: break
        time.sleep(1)

    if not img_data:
        print(f'  FAIL: no image found')
        results.append((topic, 'FAIL-NOIMG', 0))
        continue

    mid = upload_to_wp(img_data, topic)
    if not mid:
        print(f'  FAIL: upload didnt persist')
        results.append((topic, 'FAIL-UPLOAD', 0))
        continue

    print(f'  Image uploaded: media {mid}')

    # Assign
    ok = 0
    for p in pending:
        r = requests.post(f'{BASE}{p["type"]}/{p["id"]}', auth=AUTH,
                         json={'featured_media': mid}, timeout=30)
        if r.status_code == 200 and r.json().get('featured_media') == mid:
            ok += 1
        time.sleep(0.5)

    print(f'  Assigned to {ok}/{len(pending)} posts')
    results.append((topic, 'OK', ok))
    time.sleep(4)

print('\n\nRESULT:')
total = sum(r[2] for r in results)
print(f'Total assigned: {total}')
for t, s, c in results:
    print(f'  {t}: {c} ({s})')
