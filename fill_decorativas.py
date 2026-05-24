import json
import base64
import urllib.request

CREDS = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
AUTH = 'Basic ' + base64.b64encode(f'{CREDS[0]}:{CREDS[1]}'.encode()).decode()
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2'
DECOR_CAT_ID = 368
DECOR_URL = 'https://avesnativaschilenas.cl/jaulas/decorativas/'

TARGETS = [
    'transforma-tu-hogar-con-la-encantadora-jaula-vintage-blumelhuber-el-toque-shabby-chic-que-necesitas',
    'descubre-la-jaula-de-madera-perfecta-para-tus-pajaros-sorprendete-con-kerbl-82911',
    'descubre-la-encantadora-jaula-pagoda-para-pajaros-imac-consigue-el-hogar-perfecto-para-tus-aves',
    'descubre-la-jaula-perfecta-para-tus-aves-estilo-comodidad-y-diseno-unico',
    'descubre-la-jaula-perfecta-para-tus-canarios-comodidad-y-estilo-en-un-solo-espacio',
    'descubre-la-jaula-perfecta-para-tus-pajaros-comodidad-y-estilo-en-ferplast-ibiza-open',
    'descubre-la-jaula-perfecta-para-tus-pajaros-ferplast-regina-comodidad-y-estilo-en-un-solo-lugar',
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
    if DECOR_CAT_ID not in cats:
        cats.append(DECOR_CAT_ID)
        api('PUT', f'/posts/{post["id"]}', {'categories': cats})
        print(f'Added decorativas category: {slug}')
    else:
        print(f'Already decorativas: {slug}')

    raw = post.get('content', {}).get('raw', '')
    if DECOR_URL not in raw:
        link_p = '<p>👉 Si quieres ver más opciones de estilo, visita nuestra <a href="' + DECOR_URL + '">guía de jaulas decorativas</a>.</p>'
        raw = raw.rstrip() + '\n\n' + link_p
        api('PUT', f'/posts/{post["id"]}', {'content': raw})
        print(f'Added decorativas link: {slug}')

print('Done')
