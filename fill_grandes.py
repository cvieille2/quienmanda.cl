import json
import base64
import urllib.request

CREDS = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
AUTH = 'Basic ' + base64.b64encode(f'{CREDS[0]}:{CREDS[1]}'.encode()).decode()
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2'
GRANDES_CAT_ID = 369
GRANDES_URL = 'https://avesnativaschilenas.cl/jaulas/grandes/'

TARGETS = [
    'descubre-la-increible-guinealoft-e-la-jaula-perfecta-para-tus-mascotas-que-necesitan-espacio',
    'descubre-la-elegante-jaula-liuands-el-hogar-perfecto-y-espacioso-para-la-salud-de-tus-aves',
    'descubre-la-lujosa-jaula-liuands-dale-a-tus-pajaros-el-hogar-espacioso-y-ventilado-que-merecen',
    'descubre-el-hogar-perfecto-para-tus-pajaros-ferplast-aviario-comodidad-y-estilo-en-uno',
    'descubre-la-jaula-perfecta-para-tus-aves-ferplast-aviario-viola-que-encantara-a-tus-loros-y-periquitos',
    'descubre-la-jaula-de-60-pulgadas-que-transformara-la-vida-de-tus-pajaros-y-sorprendera-a-todos',
]

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

for slug in TARGETS:
    post = api('GET', f'/posts?slug={slug}&_fields=id,slug,title,categories,content&context=edit')
    if not post:
        print(f'Not found: {slug}')
        continue
    post = post[0]
    cats = list(post.get('categories', []) or [])
    if GRANDES_CAT_ID not in cats:
        cats.append(GRANDES_CAT_ID)
        api('PUT', f'/posts/{post["id"]}', {'categories': cats})
        print(f'Added grandes category: {slug}')
    else:
        print(f'Already grandes: {slug}')

    raw = post.get('content', {}).get('raw', '')
    if GRANDES_URL not in raw:
        link_p = '<p>👉 Si quieres comparar modelos de gran formato, visita nuestra <a href="' + GRANDES_URL + '">guía de jaulas grandes y aviarios</a>.</p>'
        raw = raw.rstrip() + '\n\n' + link_p
        api('PUT', f'/posts/{post["id"]}', {'content': raw})
        print(f'Added grandes link: {slug}')

print('Done')
