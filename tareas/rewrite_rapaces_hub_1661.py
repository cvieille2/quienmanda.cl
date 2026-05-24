import json
import requests

AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'


def tag_id(slug):
    r = requests.get(BASE + 'tags', auth=AUTH, params={'slug': slug}, timeout=30)
    if r.status_code == 200 and r.json():
        return r.json()[0]['id']
    return None


tag_ids = []
for slug in ['aves-rapaces', 'aves-nativas', 'fauna-silvestre', 'rapaces-santiago', 'aves-cordillera-de-los-andes', 'aves-del-norte', 'aves-sur']:
    tid = tag_id(slug)
    if tid and tid not in tag_ids:
        tag_ids.append(tid)

faqs = [
    ('Que incluye la categoria rapaces?', 'Incluye aves rapaces diurnas y nocturnas: aguilas, halcones, lechuzas, buhos, jotes, cernicalos y otras especies cercanas.'),
    ('Cual es la mejor pagina para aguilas?', 'La pagina especializada de aguilas en Chile es la mejor para empezar, porque ordena especies, habitat y diferencias con otras rapaces.'),
    ('El peuco es una aguila?', 'No. Es una rapaz distinta, aunque mucha gente la confunde con una aguila por su tamano y presencia.'),
    ('Las rapaces son peligrosas para las personas?', 'No en condiciones normales. Son fauna silvestre que debe observarse a distancia y sin molestar nidos ni polluelos.'),
    ('Puedo usar un aguila para ahuyentar palomas?', 'No como estrategia real de control urbano. La mejor solucion para palomas sigue siendo manejo del alimento, limpieza y barreras fisicas.'),
    ('Donde veo rapaces en Santiago?', 'En cerros, bordes urbanos, parques grandes y sectores abiertos de la Region Metropolitana.'),
]

content = '''<div class="actualizacion-2026" style="background:#fffde7;padding:10px 15px;border-radius:6px;margin-bottom:20px;border-left:4px solid #fbc02d;font-size:0.95em;"><strong>Actualizado: Mayo 2026</strong> — Esta guia fue reordenada para separar mejor las rapaces diurnas y nocturnas, destacar el nuevo silo de aguilas y dejar una ruta clara para explorar la categoria completa.</div>

<p>Las aves rapaces son mucho mas que cazadoras. Son depredadoras tope o cercanas a la cima, regulan poblaciones de presas y suelen ser uno de los mejores indicadores del estado de un ecosistema. En Chile, este grupo incluye desde las aguilas y halcones hasta lechuzas, buhos, jotes y otras especies que la gente suele buscar por separado.</p>

<p>Si llegaste aqui buscando una especie concreta, este hub te ayuda a elegir la ruta correcta sin perder tiempo. Para <strong>aguilas en Chile</strong>, revisa la guia nueva de <a href="https://avesnativaschilenas.cl/aguilas/">aguilas</a>. Para aves nocturnas, entra a <a href="https://avesnativaschilenas.cl/rapaces/lechuza/">lechuzas y buhos</a>. Y si buscas rapaces de Santiago, la mejor puerta de entrada es <a href="https://avesnativaschilenas.cl/rapaces/rapaces-santiago/">rapaces de Santiago</a>.</p>

<div style="background:#f8f9fa;border:1px solid #dfe3e8;border-radius:12px;padding:18px;margin:20px 0;">
<p><strong>Ruta rapida por la categoria</strong></p>
<ul>
<li><a href="https://avesnativaschilenas.cl/aguilas/">Aguilas en Chile</a></li>
<li><a href="https://avesnativaschilenas.cl/rapaces/lechuza/">Lechuzas y buhos</a></li>
<li><a href="https://avesnativaschilenas.cl/rapaces/halcon/">Halcones</a></li>
<li><a href="https://avesnativaschilenas.cl/rapaces/condor/">Condor andino</a></li>
<li><a href="https://avesnativaschilenas.cl/rapaces/jote/">Jotes</a></li>
<li><a href="https://avesnativaschilenas.cl/rapaces/rapaces-santiago/">Rapaces de Santiago</a></li>
</ul>
</div>

<h2>Rapaces diurnas y nocturnas en Chile</h2>
<p>La categoria se divide en dos grandes grupos. Las rapaces <strong>diurnas</strong> cazan de dia, usando una vista aguda, garras fuertes y vuelo eficiente. Las rapaces <strong>nocturnas</strong> cazan en la oscuridad con oido afinado, vuelo silencioso y ojos adaptados a poca luz.</p>

<table>
<thead><tr><th>Grupo</th><th>Ejemplos</th><th>Clave visual</th><th>Donde buscarlas</th></tr></thead>
<tbody>
<tr><td>Diurnas</td><td>Aguilas, halcones, jotes, cernicalos</td><td>Vuelo planeado o rapido</td><td>Cerros, valles, campos y costa</td></tr>
<tr><td>Nocturnas</td><td>Lechuzas, buhos, nucos</td><td>Vuelo silencioso y ojos frontales</td><td>Bosques, campos y sectores tranquilos</td></tr>
</tbody>
</table>

<h2>Aguilas en Chile: la ruta que mas busca la gente</h2>
<p>Si tu interes principal son las aguilas, la pagina nueva de <a href="https://avesnativaschilenas.cl/aguilas/">aguilas en Chile</a> te deja el panorama limpio: cuales aparecen realmente, donde verlas, como distinguirlas de peucos y aguiluchos, y que papel cumplen en el ecosistema.</p>

<p>Dentro de ese nuevo silo tambien puedes revisar especies y temas relacionados como <a href="https://avesnativaschilenas.cl/aguilas/aguila-peregrina/">aguila peregrina</a>, <a href="https://avesnativaschilenas.cl/aguilas/aguila-crestada/">aguila crestada</a>, <a href="https://avesnativaschilenas.cl/aguilas/aguila-arpia/">aguila arpia</a>, <a href="https://avesnativaschilenas.cl/aguilas/peuco/">peuco</a> y <a href="https://avesnativaschilenas.cl/aguilas/traro/">traro chileno</a>.</p>

<h2>Especies y paginas principales</h2>
<div class="content-cluster">
<article class="article-loop asap-columns-4"><a href="https://avesnativaschilenas.cl/aguilas/" rel="bookmark"><span class="entry-title">Aguilas en Chile</span></a></article>
<article class="article-loop asap-columns-4"><a href="https://avesnativaschilenas.cl/rapaces/lechuza/" rel="bookmark"><span class="entry-title">Lechuzas</span></a></article>
<article class="article-loop asap-columns-4"><a href="https://avesnativaschilenas.cl/rapaces/halcon/" rel="bookmark"><span class="entry-title">Halcones</span></a></article>
<article class="article-loop asap-columns-4"><a href="https://avesnativaschilenas.cl/rapaces/condor/" rel="bookmark"><span class="entry-title">Condor andino</span></a></article>
<article class="article-loop asap-columns-4"><a href="https://avesnativaschilenas.cl/rapaces/jote/" rel="bookmark"><span class="entry-title">Jotes</span></a></article>
<article class="article-loop asap-columns-4"><a href="https://avesnativaschilenas.cl/aguilas/peuco/" rel="bookmark"><span class="entry-title">Peuco</span></a></article>
</div>

<h2>Que hace especiales a las rapaces</h2>
<ul>
<li><strong>Pico curvo y fuerte:</strong> ideal para desgarrar alimento.</li>
<li><strong>Garras potentes:</strong> permiten capturar y sujetar presas.</li>
<li><strong>Vision aguda:</strong> clave para detectar movimientos a distancia.</li>
<li><strong>Vuelo eficiente:</strong> planeo, maniobras o picados rapidos segun la especie.</li>
</ul>

<h2>Importancia ecologica</h2>
<p>Las rapaces regulan poblaciones de roedores, aves pequenas e insectos grandes. Tambien limpian carroña en algunas especies y sirven como bioindicadores del estado ambiental. Cuando una rapaz desaparece, normalmente el problema no es solo la especie: es el habitat completo.</p>

<h2>Amenazas principales</h2>
<ul>
<li>Perdida de habitat por urbanizacion y agricultura.</li>
<li>Envenenamiento secundario por rodenticidas.</li>
<li>Electrocucion en tendidos y colisiones.</li>
<li>Persecucion por mitos o desinformacion.</li>
<li>Reduccion de presas y perturbacion de nidos.</li>
</ul>

<h2>Como observar rapaces sin molestarlas</h2>
<ul>
<li>Usa binoculares y mantente a distancia.</li>
<li>No te acerques a nidos ni uses drones sobre areas de cria.</li>
<li>Evita alimentarlas o intentar atraerlas.</li>
<li>Si estas fotografiando, prioriza el comportamiento del ave, no la invasion del sitio.</li>
</ul>

<h2>Lecturas recomendadas</h2>
<ul>
<li><a href="https://avesnativaschilenas.cl/rapaces/principales-amenazas-para-las-aves-rapaces-en-chile/">Principales amenazas para las aves rapaces en Chile</a></li>
<li><a href="https://avesnativaschilenas.cl/rapaces/como-las-aves-carroneras-limpian-el-ecosistema-en-chile/">Como las aves carroñeras limpian el ecosistema</a></li>
<li><a href="https://avesnativaschilenas.cl/rapaces/que-tan-monogamas-son-las-aves-rapaces-en-chile/">Monogamia en aves rapaces</a></li>
<li><a href="https://avesnativaschilenas.cl/rapaces/">Hub principal de rapaces</a></li>
</ul>

<h2>Preguntas frecuentes</h2>
<h3>Que incluye la categoria rapaces?</h3>
<p>Incluye aguilas, halcones, lechuzas, buhos, jotes, cernicalos y otras aves de presa.</p>
<h3>Cual es la mejor pagina para aguilas?</h3>
<p>La guia nueva de <a href="https://avesnativaschilenas.cl/aguilas/">aguilas en Chile</a> porque separa especies reales, habitat y confusiones comunes.</p>
<h3>El peuco es una aguila?</h3>
<p>No. Es una rapaz distinta, aunque mucha gente la confunde con una aguila.</p>
<h3>Las rapaces son peligrosas?</h3>
<p>No en condiciones normales. Deben observarse a distancia, sin molestar nidos ni polluelos.</p>
<h3>Puedo usar un aguila para ahuyentar palomas?</h3>
<p>No como estrategia real. Para palomas funciona mejor higiene, barreras y manejo de alimento.</p>
<h3>Donde veo rapaces en Santiago?</h3>
<p>En cerros, bordes urbanos, parques grandes y sectores abiertos de la Region Metropolitana.</p>

<script type="application/ld+json">''' + json.dumps({'@context': 'https://schema.org', '@type': 'FAQPage', 'mainEntity': [{'@type': 'Question', 'name': q, 'acceptedAnswer': {'@type': 'Answer', 'text': a}} for q, a in faqs]}, ensure_ascii=False) + '''</script>'''

payload = {
    'title': 'Aves de Rapina Chilenas: guia completa de identificacion y observacion',
    'excerpt': 'Hub actualizado de rapaces chilenas con rutas claras hacia aguilas, lechuzas, buhos, halcones, jotes y especies relacionadas.',
    'content': content,
    'tags': tag_ids,
}

resp = requests.post(BASE + 'pages/1661', auth=AUTH, json=payload, timeout=30)
print(resp.status_code)
