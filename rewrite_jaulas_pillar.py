import json
import base64
import urllib.request

CREDS = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
AUTH = 'Basic ' + base64.b64encode(f'{CREDS[0]}:{CREDS[1]}'.encode()).decode()
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2'
PAGE_ID = 6492
PAGE_URL = 'https://avesnativaschilenas.cl/jaulas/'
AMAZON_URL = 'https://www.amazon.es/s?k=jaulas+para+aves&tag=avesnativas-21'
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

faq_items = [
    ('¿Qué tamaño de jaula necesita un canario?', 'Como mínimo, una jaula alargada donde pueda saltar de percha a percha. Para un solo canario, una medida aproximada de 40 x 20 x 30 cm puede servir, pero si puedes subir el tamaño, mejor.'),
    ('¿Qué jaula conviene para loros?', 'Para loros convienen jaulas espaciosas, robustas y con cierres bloqueables. Si el loro es mediano o grande, prioriza el diámetro de barrotes, la estabilidad y el espacio interior.'),
    ('¿Cuál es la diferencia entre una jaula portátil y una fija?', 'La portátil está pensada para viaje, veterinario o traslados; la fija es para uso diario en casa. Si tu ave duerme y vive ahí, busca una jaula más grande y estable.'),
    ('¿Qué material es mejor para una jaula de aves?', 'El metal o acero con recubrimiento resistente es la opción más práctica por higiene y durabilidad. La madera puede ser decorativa, pero exige más mantención.'),
    ('¿Por qué una bandeja extraíble importa tanto?', 'Porque reduce el tiempo de limpieza y mejora la higiene general de la jaula. Es una de las características más útiles en el uso diario.'),
    ('¿Puedo comprar jaulas por marca?', 'Sí. Si comparas Ferplast, Vision, Yaheetech, Voltrega, IMAC o VIVOHOME, el punto clave es el equilibrio entre tamaño, accesorios, seguridad y precio.'),
]

faq_json = []
for q, a in faq_items:
    faq_json.append({'@type': 'Question', 'name': q, 'acceptedAnswer': {'@type': 'Answer', 'text': a}})

faq_script = '<script type="application/ld+json">' + json.dumps({'@context': 'https://schema.org', '@type': 'FAQPage', 'mainEntity': faq_json}, ensure_ascii=False) + '</script>'

content = f'''<!-- wp:paragraph -->
<p>Elegir una <strong>jaula para aves</strong> no es solo comprar un accesorio: es definir el hogar de tu mascota. La jaula correcta mejora su seguridad, su descanso, su higiene y hasta su comportamiento. En esta guía reunimos los tipos más útiles, las marcas más buscadas y las reseñas que más ayudan a decidir antes de comprar.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Si quieres ir al grano, empieza por esta selección rápida: <a href="{PAGE_URL}portatiles/">portátiles</a>, <a href="{PAGE_URL}para-loros/">para loros</a>, <a href="{PAGE_URL}para-pajaros-pequenos/">para pájaros pequeños</a>, <a href="{PAGE_URL}grandes/">grandes y aviarios</a>, <a href="{PAGE_URL}decorativas/">decorativas</a>, <a href="{PAGE_URL}accesorios/">accesorios</a> y <a href="{PAGE_URL}marcas/">marcas</a>.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {{"width":100,"className":"cta"}} -->
<div class="wp-block-button has-custom-width wp-block-button__width-100 cta"><a class="wp-block-button__link wp-element-button" href="{AMAZON_URL}" target="_blank" rel="nofollow sponsored noopener">👉 Ver jaulas para aves en Amazon</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Tipos de jaulas para aves</h2>
<!-- /wp:heading -->

<!-- wp:table -->
<figure class="wp-block-table"><table><thead><tr><th>Tipo</th><th>Ideal para</th><th>Qué mirar</th></tr></thead><tbody>
<tr><td><a href="{PAGE_URL}portatiles/">Portátiles</a></td><td>Viajes y traslados</td><td>Ligereza, plegado y cierre seguro</td></tr>
<tr><td><a href="{PAGE_URL}para-loros/">Para loros</a></td><td>Cacatúas, conures y loros</td><td>Barrotes resistentes, tamaño y bloqueo</td></tr>
<tr><td><a href="{PAGE_URL}para-pajaros-pequenos/">Para pájaros pequeños</a></td><td>Canarios, periquitos y ninfas</td><td>Separación entre barrotes, accesorios y ventilación</td></tr>
<tr><td><a href="{PAGE_URL}grandes/">Grandes y aviarios</a></td><td>Aves grandes o varias aves</td><td>Estabilidad, ruedas, espacio y durabilidad</td></tr>
<tr><td><a href="{PAGE_URL}decorativas/">Decorativas</a></td><td>Hogar y estilo</td><td>Diseño, limpieza y materiales</td></tr>
<tr><td><a href="{PAGE_URL}accesorios/">Accesorios</a></td><td>Mejorar la jaula</td><td>Cubiertas, bandejas, rejillas y limpieza</td></tr>
</tbody></table></figure>
<!-- /wp:table -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Cómo elegir la jaula correcta</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Antes de comprar, revisa cinco puntos: <strong>tamaño</strong>, <strong>seguridad</strong>, <strong>material</strong>, <strong>facilidad de limpieza</strong> y <strong>uso real</strong>. Una jaula muy bonita pero incómoda termina siendo mala compra. Una jaula muy pequeña puede afectar el bienestar del ave incluso si “cabe” en el espacio disponible.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li><strong>Extraíble</strong>: clave para limpiar sin desmontar todo.</li>
<li><strong>Duradera</strong>: mejor metal tratado o acero resistente.</li>
<li><strong>Segura</strong>: cierres firmes y sin bordes peligrosos.</li>
<li><strong>Plegable / portátil</strong>: útil si viajas o llevas el ave al veterinario.</li>
<li><strong>Bloqueable</strong>: importante en loros y aves inteligentes.</li>
<li><strong>Espaciosa / estable</strong>: prioridad para aves activas o grandes.</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Marcas y modelos que más se buscan</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Si prefieres comprar por marca, hemos preparado una guía específica con las marcas más consultadas: <a href="{PAGE_URL}marcas/">Yaheetech, Vision, Ferplast, Arquivet, BPS Buena Pet Shop, Flamingo, PH Prevue Hendryx y VIVOHOME</a>. Muchas también venden accesorios y productos complementarios, no solo jaulas.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Reseñas destacadas</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Si quieres comparar antes de comprar, revisa nuestras <a href="{RESENAS_URL}">reseñas destacadas</a> con solo 5 recomendaciones seleccionadas por intención de búsqueda y utilidad comercial.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Guías por tipo de jaula</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul>
<li><a href="{PAGE_URL}portatiles/">Jaulas portátiles</a>: para viajar y mover con facilidad.</li>
<li><a href="{PAGE_URL}para-loros/">Jaulas para loros</a>: seguridad y espacio para aves inteligentes.</li>
<li><a href="{PAGE_URL}para-pajaros-pequenos/">Jaulas para pájaros pequeños</a>: canarios, periquitos y ninfas.</li>
<li><a href="{PAGE_URL}grandes/">Jaulas grandes y aviarios</a>: para aves grandes o múltiples mascotas.</li>
<li><a href="{PAGE_URL}decorativas/">Jaulas decorativas</a>: estilo sin sacrificar funcionalidad.</li>
<li><a href="{PAGE_URL}accesorios/">Accesorios para jaulas</a>: cubiertas, bandejas, rejillas y más.</li>
<li><a href="{PAGE_URL}para-palomas/">Jaulas para palomas</a>: transporte, crianza y cuidado.</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Preguntas frecuentes sobre jaulas para aves</h2>
<!-- /wp:heading -->

{faq_script}

''' + ''.join(f'<p><strong>{q}</strong><br>{a}</p>' for q, a in faq_items) + f'''

<!-- wp:paragraph -->
<p>¿Buscas empezar por una compra segura? Revisa primero las <a href="{PAGE_URL}modelos/">comparativas de modelos</a> y luego decide si necesitas una jaula por tipo de ave, por marca o por funcionalidad.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {{"width":100,"className":"cta"}} -->
<div class="wp-block-button has-custom-width wp-block-button__width-100 cta"><a class="wp-block-button__link wp-element-button" href="{AMAZON_URL}" target="_blank" rel="nofollow sponsored noopener">👉 Comparar jaulas para aves ahora</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
'''

page_data = {
    'title': 'Jaulas para aves en Chile: guía completa para elegir la mejor',
    'content': content,
    'excerpt': 'Guía completa de jaulas para aves en Chile: tipos, marcas, reseñas, accesorios y consejos para elegir la mejor opción.',
    'status': 'publish',
}

api('PUT', f'/pages/{PAGE_ID}', page_data)
print('Updated jaulas pillar')
