import json
import base64
import urllib.request

CREDS = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
AUTH = 'Basic ' + base64.b64encode(f'{CREDS[0]}:{CREDS[1]}'.encode()).decode()
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2'
PARROT_CAT_ID = 365
PARROT_URL = 'https://avesnativaschilenas.cl/jaulas/para-loros/'

TARGETS = [
    'descubre-la-jaula-perfecta-para-tus-loros-elegante-y-portatil-conquista-a-tus-pajaros',
    'descubre-la-hermosa-jaula-metalica-para-loros-que-transformara-tu-hogar-y-encantara-a-tus-aves',
    'transforma-tu-hogar-la-jaula-de-lujo-para-ninfas-que-tus-aves-adoraran',
    'descubre-la-jaula-perfecta-para-tus-loros-y-periquitos-con-soporte-y-diseno-apilable',
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
    if PARROT_CAT_ID not in cats:
        cats.append(PARROT_CAT_ID)
        api('PUT', f'/posts/{post["id"]}', {'categories': cats})
        print(f'Added parrot category: {slug}')
    else:
        print(f'Already parrot: {slug}')

    raw = post.get('content', {}).get('raw', '')
    if PARROT_URL not in raw:
        link_p = '<p>👉 Si buscas más opciones para loros, cacatúas y ninfas, visita nuestra <a href="' + PARROT_URL + '">guía de jaulas para loros</a>.</p>'
        raw = raw.rstrip() + '\n\n' + link_p
        api('PUT', f'/posts/{post["id"]}', {'content': raw})
        print(f'Added parrot link: {slug}')

print('Done')
