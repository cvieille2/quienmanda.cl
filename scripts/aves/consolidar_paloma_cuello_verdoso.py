import requests, json, time

AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'
REDIRECT_BASE = 'https://avesnativaschilenas.cl/wp-json/redirection/v1/'

BANNER = '<div class="actualizacion-2026" style="background:#fffde7;padding:10px 15px;border-radius:6px;margin-bottom:20px;border-left:4px solid #fbc02d;font-size:0.95em;"><strong>Actualizado: Mayo 2026</strong> — Esta guía fue revisada y actualizada con información reciente para ayudarte a identificar y conocer a las palomas de cuello verdoso.</div>'

content = BANNER

content += '''
<p>La <strong>paloma de cuello verdoso</strong> es una de las especies de colúmbidas más llamativas por su plumaje iridiscente que brilla con tonos verdes, azules y púrpuras en la región del cuello. Aunque el término «paloma de cuello verdoso» se usa para describir varias especies del género <em>Columba</em> y <em>Patagioenas</em> que comparten esta característica, en esta guía nos centramos en las especies más representativas, su identificación, hábitat, alimentación y comportamiento.</p>
<p>Estas aves se distribuyen desde América del Sur hasta el sudeste asiático, dependiendo de la especie. Su característico brillo verdoso no es producido por pigmentos, sino por la estructura microscópica de las plumas que refracta la luz (coloración estructural), similar a lo que ocurre con las plumas del pavo real.</p>
'''

content += '''
<h2>Cómo identificar una paloma de cuello verdoso</h2>
<p>La característica más distintiva de estas palomas es el <strong>brillo iridiscente verde y púrpura</strong> que presentan en la nuca y los lados del cuello. Este brillo es más intenso en los machos durante la época de apareamiento. El resto del cuerpo suele ser gris parduzco, con tonalidades más claras en el pecho y el vientre. Miden entre 30 y 40 cm de longitud, con una envergadura alar de 50 a 65 cm. Sus ojos son anaranjados o rojizos, rodeados por un anillo orbital delgado, y el pico es oscuro con una cera blanquecina en la base.</p>
<p>Entre las especies más conocidas con cuello verdoso se encuentran:</p>
<ul>
<li><strong>Paloma de collar</strong> (<em>Columba palumbus</em>): común en Europa, tiene una mancha blanca distintiva en el cuello además del brillo verdoso.</li>
<li><strong>Paloma montera</strong> (<em>Patagioenas fasciata</em>): presente en América, con cuello iridiscente verde y púrpura.</li>
<li><strong>Paloma de cuello verdoso asiática</strong> (<em>Columba punicea</em>): plumaje castaño con cuello verde brillante, del sudeste asiático.</li>
<li><strong>Paloma bronceada</strong> (<em>Columba delegorguei</em>): nativa del África subsahariana, con cuello verde y bronce.</li>
</ul>
'''

content += '''
<h2>Hábitat y distribución</h2>
<p>Las palomas de cuello verdoso habitan en una amplia variedad de ecosistemas. Prefieren bosques templados y tropicales, sabanas arboladas, áreas de cultivo con vegetación densa y, cada vez más, zonas urbanas con parques y jardines. Se adaptan bien a entornos modificados por el ser humano siempre que haya árboles para anidar y fuentes de alimento.</p>
<p>En América del Sur, especies como la <em>Patagioenas fasciata</em> se encuentran desde Colombia hasta el norte de Argentina, incluyendo Chile en la zona precordillerana. Habitan principalmente en bosques montanos entre los 500 y 3000 m de altitud. Construyen nidos plataforma con ramas en árboles y arbustos densos, generalmente a 3-10 m del suelo.</p>
'''

content += '''
<h2>Comportamiento y alimentación</h2>
<p>Son aves gregarias que forman bandadas fuera de la temporada de cría. Se alimentan principalmente de semillas, granos, frutos caídos y brotes tiernos. A diferencia de otras palomas, buscan alimento tanto en el suelo como en árboles y arbustos. Su alimentación incluye:</p>
<ul>
<li>Semillas de gramíneas y malezas</li>
<li>Bayas y frutos pequeños (higueras, arrayanes)</li>
<li>Granos cultivados (maíz, trigo, avena)</li>
<li>Brotes y hojas tiernas</li>
</ul>
<p>Durante la época reproductiva, los machos realizan vuelos de exhibición: ascienden en espiral y descienden con las alas en V, mostrando el cuello iridiscente. La hembra pone 1-2 huevos blancos en un nido de ramas, incubando durante 17-19 días. Los polluelos abandonan el nido a los 25-30 días.</p>
'''

content += '''
<h2>Estado de conservación</h2>
<p>La mayoría de las especies de palomas con cuello verdoso no están globalmente amenazadas. La <em>Patagioenas fasciata</em> está clasificada como «Preocupación Menor» por la UICN. Sin embargo, algunas poblaciones locales enfrentan amenazas por deforestación, caza y competencia con especies introducidas. En Chile, las palomas de cuello verdoso no están clasificadas como especie amenazada, pero al ser fauna silvestre están protegidas por la Ley de Caza 19.473.</p>
<p>Las principales amenazas incluyen la pérdida de hábitat por expansión agrícola y urbana, la caza para consumo en zonas rurales, y la depredación por gatos asilvestrados. Para su conservación, se recomienda mantener parches de bosque nativo, instalar comederos con semillas variadas y evitar el uso de pesticidas en áreas donde habitan.</p>
'''

# FAQ
faqs = [
    ("¿La paloma de cuello verdoso es una especie única?", "No existe una sola especie llamada «paloma de cuello verdoso». El término describe a varias especies de colúmbidas que presentan plumaje iridiscente verde en el cuello, como la paloma de collar (Columba palumbus), la paloma montera (Patagioenas fasciata) y otras."),
    ("¿Dónde vive la paloma de cuello verdoso en Chile?", "En Chile, especies con cuello verdoso como la paloma montera (Patagioenas fasciata) habitan en la zona precordillerana, desde la Región de Coquimbo hasta la Región de Los Lagos, en bosques montanos y áreas arboladas."),
    ("¿Qué come la paloma de cuello verdoso?", "Su dieta es principalmente granívora: semillas, granos y frutos. También consumen brotes tiernos, bayas y ocasionalmente pequeños invertebrados. En zonas urbanas se alimentan de alpiste, maíz partido y avena."),
    ("¿Cómo se reproduce la paloma de cuello verdoso?", "Construye un nido de ramas en árboles o arbustos densos. La hembra pone 1-2 huevos blancos, incuba durante 17-19 días, y los polluelos empluman a los 25-30 días. Pueden tener 2-3 nidadas por temporada."),
    ("¿Está en peligro de extinción la paloma de cuello verdoso?", "La mayoría de las especies no están en peligro global. Están clasificadas como «Preocupación Menor» por la UICN. Sin embargo, la deforestación y la caza local pueden amenazar poblaciones específicas."),
    ("¿Cómo diferenciar la paloma de cuello verdoso de otras palomas?", "Su característica principal es el brillo iridiscente verde y púrpura en el cuello, visible con luz solar directa. En reposo, el brillo puede no apreciarse. También se distinguen por su tamaño mediano (30-40 cm) y su comportamiento más arbóreo que las palomas urbanas.")
]

faq_items = []
faq_html = '<h2>Preguntas frecuentes sobre la paloma de cuello verdoso</h2>'
for q, a in faqs:
    faq_html += f'<h3>{q}</h3><p>{a}</p>'
    faq_items.append({'@type': 'Question', 'name': q, 'acceptedAnswer': {'@type': 'Answer', 'text': a}})

faq_schema = f'<script type="application/ld+json">{json.dumps({"@context": "https://schema.org", "@type": "FAQPage", "mainEntity": faq_items}, ensure_ascii=False)}</script>'

content += faq_html + faq_schema
content += '<p><em>Como afiliado de Amazon, gano por compras calificadas.</em></p>'

# Update post 8899
data = {
    'content': content,
    'tags': [66, 122, 115],
    'categories': [121],
    'featured_media': 14109,
    'title': 'Palomas de cuello verdoso: identificacion, habitat y cuidados',
    'slug': 'palomas-de-cuello-verdoso',
    'status': 'publish'
}

r = requests.put(f'{BASE}posts/8899', auth=AUTH, json=data)
print(f'8899 update: {r.status_code}')
if r.status_code != 200:
    print(r.text[:500])

# Create 301 redirects
redirects = [
    ('/migratorias/paloma/paloma-de-cuello-verdoso-identificacion-caracteristicas-y-fotos/', '/migratorias/paloma/palomas-de-cuello-verdoso/'),
    ('/migratorias/paloma/tipo-de-paloma-de-40-cm-y-cuello-verdoso/', '/migratorias/paloma/palomas-de-cuello-verdoso/'),
    ('/tortola/variedad-de-paloma-de-cuello-verdoso/', '/migratorias/paloma/palomas-de-cuello-verdoso/'),
]

for source, target in redirects:
    r = requests.post(f'{REDIRECT_BASE}redirect', auth=AUTH, json={
        'source': source,
        'target': target,
        'type': 301,
        'match_data': {'source': {'flag_source': 'urlonly', 'flag_case': False, 'flag_trailing': False, 'flag_query': 'exact'}},
        'title': 'Redirect: paloma cuello verdoso consolidation',
        'group_id': 1,
        'action_code': 301
    })
    print(f'Redirect {source} -> {target}: {r.status_code}')
    if r.status_code not in (200, 201):
        print(r.text[:300])
    time.sleep(0.5)

# Set other posts to draft
for pid in [9361, 9997, 11818]:
    r = requests.put(f'{BASE}posts/{pid}', auth=AUTH, json={
        'status': 'draft',
        'content': '<!-- Redirigido a /palomas-de-cuello-verdoso/ (ID 8899) --><p>Redirigido. Por favor visita <a href=\"/migratorias/paloma/palomas-de-cuello-verdoso/\">Palomas de cuello verdoso</a>.</p>'
    })
    print(f'Post {pid} set to draft: {r.status_code}')

print('Consolidacion completa')
