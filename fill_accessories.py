import json
import base64
import urllib.request

CREDS = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
AUTH = 'Basic ' + base64.b64encode(f'{CREDS[0]}:{CREDS[1]}'.encode()).decode()
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2'
ACC_CAT_ID = 366
ACC_URL = 'https://avesnativaschilenas.cl/jaulas/accesorios/'

TARGETS = [
    'transforma-la-jaula-de-tus-pajaros-con-yarnow-descubre-las-3-piezas-que-cambiaran-todo',
    'descubre-como-la-yarnow-protegera-a-tus-loros-de-polvo-y-amenazas-no-te-lo-pierdas',
    'transforma-la-vida-de-tus-aves-con-la-jaula-yardwe-proteccion-y-estilo-en-5-piezas',
    'descubre-la-asombrosa-proteccion-que-solo-yardwe-ofrece-para-tus-aves-la-jaula-de-sus-suenos',
    'descubre-por-que-la-bandeja-yarnow-para-pajaros-transformara-el-cuidado-de-tu-mascota',
    'descubre-las-irresistibles-fundas-yarnow-para-jaulas-de-pajaros-que-transformaran-tu-hogar',
    'descubre-la-increible-cubierta-yarnow-para-jaulas-de-pajaros-y-transforma-su-hogar-hoy',
    'transforma-la-vida-de-tu-loro-con-yarnow-3-accesorios-esenciales-para-su-jaula',
    'descubre-el-secreto-para-viajar-con-tus-aves-como-un-experto-el-accesorio-yarnow-que-no-puedes-ignorar',
    'transforma-tu-hogar-con-la-asombrosa-cubierta-yarnow-para-jaulas-de-pajaros',
    'transforma-la-vida-de-tu-loro-con-yarnow-descubre-las-4-increibles-piezas-que-toda-jaula-necesita',
    'transforma-la-vida-de-tus-loros-descubre-los-imprescindibles-accesorios-de-jaula-yarnow',
    'transforma-la-vida-de-tus-aves-con-la-funda-universal-yardwe-para-jaulas-descubre-como',
    'descubre-como-la-yarnow-transforma-la-vida-de-tus-pajaros-con-estas-increibles-cubiertas',
    'descubre-la-increible-yarnow-5-piezas-que-transformaran-la-vida-de-tus-aves-al-instante',
    'descubre-la-increible-vision-rejilla-delantera-para-jaulas-m11-m12-transforma-tu-experiencia',
    'descubre-la-magia-de-yarnow-5-piezas-para-que-tus-aves-vivan-como-reyes-y-saludables',
    'transforma-la-jaula-de-tu-loro-descubre-las-300-hojas-yarnow-para-una-limpieza-facil-y-rapida',
    'descubre-las-puertas-de-resistencia-happyyami-instalacion-facil-y-seguridad-para-tus-aves',
    'descubre-el-secreto-para-que-tu-loro-sea-el-mas-feliz-accesorios-yarnow-que-encantan',
    'descubre-la-increible-vision-rejilla-lateral-para-jaulas-l11-l12-transforma-tu-espacio-hoy',
    'transforma-la-jaula-de-tus-aves-descubre-la-increible-cupula-omem-de-36-cm-que-las-hara-volar-felices',
    'descubre-la-increible-cubierta-jryxds-que-transformara-la-vida-de-tus-pajaros-%f0%9f%8c%9f',
    'transforma-tu-jaula-con-la-vision-rejilla-trasera-l01-l02-descubre-su-magia-ahora',
    'descubre-el-voltrega-j-94057-el-pie-perfecto-para-tu-jaula-736-transforma-tu-espacio-hoy',
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
    if ACC_CAT_ID not in cats:
        cats.append(ACC_CAT_ID)
        api('PUT', f'/posts/{post["id"]}', {'categories': cats})
        print(f'Added accessories category: {slug}')
    else:
        print(f'Already accessories: {slug}')

    raw = post.get('content', {}).get('raw', '')
    if ACC_URL not in raw:
        link_p = '<p>👉 Si quieres ver más piezas, cubiertas y accesorios para jaulas, visita nuestra <a href="' + ACC_URL + '">guía de accesorios para jaulas</a>.</p>'
        raw = raw.rstrip() + '\n\n' + link_p
        api('PUT', f'/posts/{post["id"]}', {'content': raw})
        print(f'Added accessories link: {slug}')

print('Done')
