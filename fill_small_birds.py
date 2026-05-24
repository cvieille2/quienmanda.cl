import json
import base64
import urllib.request

CREDS = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
AUTH = 'Basic ' + base64.b64encode(f'{CREDS[0]}:{CREDS[1]}'.encode()).decode()
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2'
SMALL_CAT_ID = 367
SMALL_URL = 'https://avesnativaschilenas.cl/jaulas/para-pajaros-pequenos/'

TARGETS = [
    'descubre-por-que-la-jaula-bucatstate-para-pajaros-es-el-hogar-perfecto-para-tus-pequenos-amigos-alados',
    'transforma-la-vida-de-tus-periquitos-con-la-jaula-yahee-tech-espaciosa-y-elegante',
    'descubre-la-jaula-perfecta-para-tus-canarios-comodidad-y-estilo-en-un-solo-espacio',
    'descubre-la-jaula-perfecta-para-tus-canarios-diseno-elegante-y-funcionalidad-inigualable',
    'transforma-tu-hogar-la-jaula-de-lujo-para-ninfas-que-tus-aves-adoraran',
    'descubre-la-jaula-perfecta-para-tu-periquito-todo-lo-que-necesitas-en-un-solo-lugar',
    'descubre-la-jaula-perfecta-para-tus-loros-y-periquitos-con-soporte-y-diseno-apilable',
    'transforma-la-vida-de-tu-canario-con-la-jaula-relaxdays-espacio-y-estilo-en-azul-verde',
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
    if SMALL_CAT_ID not in cats:
        cats.append(SMALL_CAT_ID)
        api('PUT', f'/posts/{post["id"]}', {'categories': cats})
        print(f'Added small-birds category: {slug}')
    else:
        print(f'Already small-birds: {slug}')

    raw = post.get('content', {}).get('raw', '')
    if SMALL_URL not in raw:
        link_p = '<p>👉 Si buscas más opciones para canarios, periquitos y ninfas, visita nuestra <a href="' + SMALL_URL + '">guía de jaulas para pájaros pequeños</a>.</p>'
        raw = raw.rstrip() + '\n\n' + link_p
        api('PUT', f'/posts/{post["id"]}', {'content': raw})
        print(f'Added small-birds link: {slug}')

print('Done')
