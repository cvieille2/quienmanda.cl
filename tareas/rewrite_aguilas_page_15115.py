import json
import requests

AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'


def tag_id(slug):
    r = requests.get(BASE + 'tags', auth=AUTH, params={'slug': slug}, timeout=30)
    if r.status_code == 200 and r.json():
        return r.json()[0]['id']
    return None


tag_slugs = [
    'aves-rapaces',
    'aves-nativas',
    'aves-cordillera-de-los-andes',
    'aves-del-norte',
    'aves-sur',
    'rapaces-santiago',
    'fauna-silvestre',
]
tag_ids = []
for slug in tag_slugs:
    tid = tag_id(slug)
    if tid and tid not in tag_ids:
        tag_ids.append(tid)

faq_items = [
    {
        '@type': 'Question',
        'name': 'Cual es el aguila mas comun en Chile?',
        'acceptedAnswer': {
            '@type': 'Answer',
            'text': 'La mas facil de asociar con Chile es el aguila mora, sobre todo en cordillera, precordillera y sectores abiertos con buenas corrientes de aire.'
        }
    },
    {
        '@type': 'Question',
        'name': 'Donde se pueden ver aguilas en Chile?',
        'acceptedAnswer': {
            '@type': 'Answer',
            'text': 'En cerros, valles abiertos, laderas andinas, humedales costeros y zonas rurales amplias donde puedan planear y cazar sin mucha perturbacion.'
        }
    },
    {
        '@type': 'Question',
        'name': 'El peuco es un aguila?',
        'acceptedAnswer': {
            '@type': 'Answer',
            'text': 'No. El peuco es una rapaz que mucha gente confunde con una aguila por su tamano y presencia, pero no pertenece al mismo grupo que el aguila mora.'
        }
    },
    {
        '@type': 'Question',
        'name': 'Se puede usar un aguila para ahuyentar palomas?',
        'acceptedAnswer': {
            '@type': 'Answer',
            'text': 'No como metodo de control urbano. Las aves rapaces son fauna silvestre y la mejor solucion para palomas sigue siendo limpiar, bloquear alimento y usar barreras fisicas.'
        }
    },
    {
        '@type': 'Question',
        'name': 'Que hago si encuentro un aguila herida?',
        'acceptedAnswer': {
            '@type': 'Answer',
            'text': 'No la manipules mas de lo necesario. Mantente a distancia y contacta a un centro de rescate, al SAG o a un veterinario de fauna silvestre.'
        }
    },
    {
        '@type': 'Question',
        'name': 'Que equipo sirve para observar aguilas sin molestarlas?',
        'acceptedAnswer': {
            '@type': 'Answer',
            'text': 'Unos binoculares livianos, una guia de campo y ropa de colores neutros son suficientes para observarlas con menos perturbacion.'
        }
    },
]

content = '''<div class="actualizacion-2026" style="background:#fffde7;padding:10px 15px;border-radius:6px;margin-bottom:20px;border-left:4px solid #fbc02d;font-size:0.95em;"><strong>Actualizado: Mayo 2026</strong> — Esta guia fue revisada para ordenar las especies de aguilas que la gente busca en Chile y separar las verdaderas aguilas de las rapaces que suelen confundirse con ellas.</div>

<p>En Chile, cuando alguien busca <strong>aguilas</strong>, a veces piensa en una especie concreta y otras veces en cualquier rapaz grande que planea sobre la cordillera. Esta pagina ordena ese caos con una idea simple: que si vas a observarlas, sepas <strong>cuales si aparecen en Chile</strong>, donde verlas y como distinguirlas sin confundirte con otras aves rapaces.</p>

<p>La mas representativa es el <strong>aguila mora</strong>, muy ligada a la cordillera y a los espacios abiertos. Tambien pueden aparecer visitantes como el <strong>aguila pescadora</strong> en humedales y costa. Y para completar el panorama, al final te dejo lecturas relacionadas sobre otras rapaces del sitio que mucha gente busca junto con la palabra aguila.</p>

<div style="background:#f0f0f0;padding:15px;border-radius:8px;margin:20px 0;border-left:4px solid #2c3e50;">
<p><strong>Equipo recomendado para observar aguilas</strong></p>
<p>Si quieres verlas sin acercarte de mas, estos binoculares y una guia de campo ayudan bastante:</p>
<p><a href="https://www.amazon.es/s?k=prismaticos+observacion+aves&tag=avesnativas-21" target="_blank" rel="nofollow sponsored">Prismaticos para observacion de aves</a></p>
<p><a href="https://www.amazon.es/s?k=guia+aves+rapaces+chile&tag=avesnativas-21" target="_blank" rel="nofollow sponsored">Guia de aves rapaces de Chile</a></p>
<p><em>Como afiliado de Amazon, gano por compras calificadas.</em></p>
</div>

<figure class="wp-block-image">
<img decoding="async" loading="lazy" src="https://avesnativaschilenas.cl/wp-content/uploads/2026/05/cuales-son-las-caracteristicas-rapaces-de-un-aguila-scaled-1.webp" alt="aguila en Chile" />
<figcaption>Aguila y rapaces grandes que se observan en paisajes abiertos de Chile.</figcaption>
</figure>

<p><a href="#que_aguilas_se_ven_realmente_en_chile">Ver especies</a> · <a href="#donde_ver_aguilas_en_chile">Donde verlas</a> · <a href="#diferencias_con_peuco_y_aguilucho">Diferencias</a> · <a href="#conservacion_y_amenazas">Conservacion</a> · <a href="#preguntas_frecuentes">FAQ</a></p>

<h2 id="que_aguilas_se_ven_realmente_en_chile">Que aguilas se ven realmente en Chile?</h2>
<p>Si hablamos de observacion real y no de nombres usados de forma amplia, la referencia principal es el <strong>aguila mora</strong>. Es la rapaz grande que mas facilmente se asocia con los cerros y la cordillera. En sectores costeros y humedales, en cambio, puede aparecer el <strong>aguila pescadora</strong>, que no vive todo el año en el mismo lugar pero si se deja ver en temporada o como visitante.</p>

<table>
<thead>
<tr><th>Nombre</th><th>Presencia en Chile</th><th>Donde verla</th><th>Clave rapida</th></tr>
</thead>
<tbody>
<tr><td>Aguila mora</td><td>Si, residente</td><td>Cordillera, precordillera, valles abiertos</td><td>Grande, planeadora y ligada a cerros</td></tr>
<tr><td>Aguila pescadora</td><td>Si, visitante o estacional</td><td>Humedales, lagunas, estuarios y costa</td><td>Especialista en peces</td></tr>
<tr><td>Aguila arpia</td><td>No como especie comun en Chile</td><td>Lectura relacionada</td><td>Gigante tropical de America del Sur</td></tr>
<tr><td>Aguila crestada</td><td>No como especie comun en Chile</td><td>Lectura relacionada</td><td>Rapaz grande, muy buscada por lectores</td></tr>
</tbody>
</table>

<p>Si quieres ampliar el contexto, puedes leer tambien la guia de <a href="https://avesnativaschilenas.cl/aguilas/aguila-arpia/">aguila arpia</a>, la de <a href="https://avesnativaschilenas.cl/aguilas/aguila-crestada/">aguila crestada</a> y la de <a href="https://avesnativaschilenas.cl/aguilas/aguila-peregrina/">aguila peregrina</a>.</p>

<h2 id="donde_ver_aguilas_en_chile">Donde ver aguilas en Chile</h2>
<p>El mejor sitio para ver una aguila no es la ciudad, sino los ambientes donde puede usar corrientes de aire y encontrar presas. En Chile eso suele significar:</p>
<ul>
<li>La cordillera y la precordillera del norte y centro.</li>
<li>Laderas abiertas, quebradas y valles con poca vegetacion cerrada.</li>
<li>Humedales y lagunas costeras para visitantes como la aguila pescadora.</li>
<li>Areas rurales amplias y parques naturales con baja perturbacion humana.</li>
</ul>

<p>La mejor hora suele ser cuando el aire se calienta y aparecen las corrientes termicas: mitad del dia y primeras horas de la tarde. Ahí es cuando mas se ven los planeos amplios y los giros en altura.</p>

<h2>Como distinguir una aguila de otras rapaces</h2>
<p>Muchos lectores llaman aguila a cualquier rapaz grande. El problema es que en Chile tambien hay <strong>peucos</strong>, <strong>aguiluchos</strong> y otras aves que a simple vista parecen similares. La diferencia suele estar en la silueta, el tipo de vuelo y el habitat.</p>

<table>
<thead>
<tr><th>Grupo</th><th>Como se ve</th><th>Donde suele aparecer</th><th>Error comun</th></tr>
</thead>
<tbody>
<tr><td>Aguila mora</td><td>Grande, alas anchas, planeo estable</td><td>Cerros y cordillera</td><td>Confundirla con un aguilucho grande</td></tr>
<tr><td>Aguila pescadora</td><td>Mas ligada al agua, vuelos en busca de peces</td><td>Lagunas, estuarios y costa</td><td>Creer que siempre vive en el mismo lugar</td></tr>
<tr><td>Peuco</td><td>Mas compacto y maniobrable</td><td>Sectores abiertos y rurales</td><td>Llamarlo aguila por tamano</td></tr>
<tr><td>Aguilucho</td><td>Rapaz media, planeo diferente</td><td>Campos, bordes de valle y quebradas</td><td>Tomarlo como aguila por ser grande</td></tr>
</tbody>
</table>

<p>Si te interesa esa confusion comun, revisa tambien la guia de <a href="https://avesnativaschilenas.cl/aguilas/peuco/">peuco</a> y la pagina de <a href="https://avesnativaschilenas.cl/rapaces/">aves rapaces chilenas</a>.</p>

<h2>Alimentacion y rol ecologico</h2>
<p>Las aguilas no estan ahi para decorar el cielo. Cumplen un rol importante como depredadoras tope o casi tope, controlando poblaciones de pequenos vertebrados y ayudando al equilibrio del ecosistema. Segun la especie, su dieta puede incluir roedores, reptiles, aves pequenas, peces o incluso carroña ocasional.</p>

<ul>
<li>El aguila mora caza en espacios abiertos y prefiere presas terrestres pequenas o medianas.</li>
<li>El aguila pescadora se especializa en peces y depende mucho de cuerpos de agua limpios y productivos.</li>
<li>Otras rapaces similares ayudan a mantener la cadena alimentaria equilibrada.</li>
</ul>

<p>Para ampliar este enfoque ecologico, puedes leer <a href="https://avesnativaschilenas.cl/rapaces/principales-amenazas-para-las-aves-rapaces-en-chile/">las principales amenazas de las aves rapaces en Chile</a> y <a href="https://avesnativaschilenas.cl/rapaces/cual-es-el-ave-de-rapina-mas-grande-del-mundo/">cual es el ave de rapina mas grande del mundo</a>.</p>

<h2 id="conservacion_y_amenazas">Conservacion y amenazas</h2>
<p>Las principales amenazas para estas aves son la perdida de habitat, la persecucion humana, la electrocucion en tendidos, el envenenamiento secundario por rodenticidas y la reduccion de presas. En algunas zonas, el problema no es solo la especie, sino el paisaje completo que deja de ofrecer refugio y alimento.</p>

<p>Por eso, la mejor forma de ayudar no es perseguirlas ni usarlas como herramienta de control urbano. Las aguilas son fauna silvestre y deben observarse con distancia. Si aparecen en zonas urbanas, lo correcto es manejar el entorno, no intervenir el ave.</p>

<h2>Como observarlas sin molestarlas</h2>
<ul>
<li>Usa binoculares y mantente lejos del nido.</li>
<li>No reproduzcas llamadas ni intentes atraerlas con comida.</li>
<li>Evita perseguirlas con drones o acercarte a fotografia extrema.</li>
<li>Si una pareja esta nidificando, cambia de punto de observacion.</li>
<li>Toma notas de vuelo, silueta y habitat para identificarla mejor despues.</li>
</ul>

<h2>Lecturas relacionadas</h2>
<ul>
<li><a href="https://avesnativaschilenas.cl/aguilas/cuanto-mide-un-aguila-con-las-alas-abiertas/">Cuanto mide un aguila con las alas abiertas</a></li>
<li><a href="https://avesnativaschilenas.cl/aguilas/cuanto-mide-un-aguila-real-con-las-alas-abiertas/">Cuanto mide un aguila real con las alas abiertas</a></li>
<li><a href="https://avesnativaschilenas.cl/aguilas/el-aguila-que-tipo-de-consumidor-es/">Que tipo de consumidor es el aguila</a></li>
<li><a href="https://avesnativaschilenas.cl/blog/aves-de-gran-tamano/">Aves de gran tamano que habitan en Chile</a></li>
<li><a href="https://avesnativaschilenas.cl/rapaces/">Guia completa de aves rapaces en Chile</a></li>
</ul>

<h2 id="preguntas_frecuentes">Preguntas frecuentes</h2>
<h3>Cual es el aguila mas comun en Chile?</h3>
<p>La mas comun y facil de asociar con el pais es el aguila mora, sobre todo en cordillera y paisajes abiertos del centro y norte.</p>
<h3>Hay aguilas en Santiago?</h3>
<p>Si, sobre todo en la periferia, cerros, valles cercanos y sectores donde aun hay espacio abierto y poca perturbacion.</p>
<h3>El aguila pescadora vive todo el ano en Chile?</h3>
<p>No siempre. Puede aparecer de forma estacional o como visitante en zonas costeras y humedales.</p>
<h3>El peuco es una aguila?</h3>
<p>No. Es una rapaz distinta, aunque mucha gente la confunde con una aguila por su tamano y su presencia en espacios abiertos.</p>
<h3>Las aguilas sirven para ahuyentar palomas?</h3>
<p>No como estrategia urbana. La solucion real para palomas es higiene, barreras fisicas y control del alimento disponible.</p>
<h3>Que hago si veo un aguila herida?</h3>
<p>No la manipules mas de lo necesario. Mantente a distancia y contacta al SAG, un centro de rescate o un veterinario de fauna silvestre.</p>

<script type="application/ld+json">''' + json.dumps({
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    'mainEntity': faq_items,
}, ensure_ascii=False) + '''</script>'''

payload = {
    'title': 'Aguilas en Chile: especies, habitat y donde verlas [Guia 2026]',
    'excerpt': 'Guia actualizada de aguilas en Chile: especies mas buscadas, habitat, diferencias con otras rapaces y donde observarlas sin molestarlas.',
    'content': content,
    'tags': tag_ids,
}

resp = requests.post(BASE + 'pages/15115', auth=AUTH, json=payload, timeout=30)
print(resp.status_code)
