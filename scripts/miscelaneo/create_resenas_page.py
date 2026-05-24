import json
import base64
import urllib.request

CREDS = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
AUTH = 'Basic ' + base64.b64encode(f'{CREDS[0]}:{CREDS[1]}'.encode()).decode()
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2'
PILLAR_ID = 6492
PILLAR_URL = 'https://avesnativaschilenas.cl/jaulas/'
RESENAS_URL = 'https://avesnativaschilenas.cl/resenas/'

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

featured = [
    ('Yaheetech: jaula espaciosa y elegante', 'https://avesnativaschilenas.cl/transforma-la-vida-de-tus-periquitos-con-la-jaula-yahee-tech-espaciosa-y-elegante/'),
    ('Ferplast Ibiza Open', 'https://avesnativaschilenas.cl/descubre-la-jaula-perfecta-para-tus-pajaros-comodidad-y-estilo-en-ferplast-ibiza-open/'),
    ('Ferplast Regina', 'https://avesnativaschilenas.cl/descubre-la-jaula-perfecta-para-tus-pajaros-ferplast-regina-comodidad-y-estilo-en-un-solo-lugar/'),
    ('Voltrega 672', 'https://avesnativaschilenas.cl/descubre-la-increible-voltrega-jaula-672-para-pajaros-tu-mascota-merecera-el-mejor-hogar/'),
    ('IMAC Wilma', 'https://avesnativaschilenas.cl/descubre-la-increible-imac-jaula-pajaros-wilma-el-hogar-perfecto-para-tu-ave-favorita/'),
]

items = []
for title, url in featured:
    items.append(f'<li><a href="{url}"><strong>{title}</strong></a></li>')

content = f'''<!-- wp:paragraph -->
<p>Estas son las 5 reseñas de jaulas que más sentido tienen para el menú principal: modelos con alta intención de compra, marcas reconocidas y buena demanda de búsqueda. Si quieres ir directo a las fichas más útiles, empieza por aquí.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Reseñas destacadas</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul>
{''.join(items)}
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading">¿Buscas más opciones?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Para ver el catálogo completo de tipos, marcas y accesorios, visita nuestra <a href="{PILLAR_URL}">guía principal de jaulas para aves</a>.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>También puedes explorar la sección de <a href="{PILLAR_URL}marcas/">marcas de jaulas</a> y la de <a href="{PILLAR_URL}accesorios/">accesorios para jaulas</a> si quieres comparar opciones por categoría.</p>
<!-- /wp:paragraph -->
'''

page_data = {
    'title': 'Reseñas',
    'slug': 'resenas',
    'status': 'publish',
    'parent': 0,
    'menu_order': 1,
    'excerpt': 'Las 5 reseñas de jaulas más buscadas y útiles para comparar modelos antes de comprar.',
    'content': content,
}

existing = api('GET', '/pages?slug=resenas&_fields=id')
if existing:
    res = api('PUT', f"/pages/{existing[0]['id']}", page_data)
    page_id = res['id']
    print(f'Updated /resenas/ (ID {page_id})')
else:
    res = api('POST', '/pages', page_data)
    page_id = res['id']
    print(f'Created /resenas/ (ID {page_id})')

# Add a visible mention on the pillar page.
pillar = api('GET', f'/pages/{PILLAR_ID}?context=edit')
raw = pillar.get('content', {}).get('raw', '')
if RESENAS_URL not in raw:
    insert = f'''<!-- wp:heading -->
<h2 class="wp-block-heading">Reseñas destacadas</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Si quieres ir directo a las fichas más buscadas, revisa nuestra <a href="{RESENAS_URL}">página de reseñas</a> con solo 5 recomendaciones seleccionadas.</p>
<!-- /wp:paragraph -->
'''
    if '<!-- wp:heading -->\n<h2 class="wp-block-heading">Tipos de jaulas para aves</h2>' in raw:
        raw = raw.replace('<!-- wp:separator -->', insert + '\n\n<!-- wp:separator -->', 1)
    else:
        raw = raw.rstrip() + '\n\n' + insert
    api('PUT', f'/pages/{PILLAR_ID}', {'content': raw})
    print('Updated /jaulas/ with reseñas link')
