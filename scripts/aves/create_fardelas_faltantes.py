import requests, time

auth = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
base = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'

def create_post(title, content, slug, tags=[], categories=[65]):
    data = {'title': title, 'content': content, 'slug': slug, 'status': 'publish', 'tags': tags, 'categories': categories}
    resp = requests.post(base + 'posts', auth=auth, json=data)
    if resp.status_code == 201:
        d = resp.json()
        print(f"OK {d['id']}: {title[:60]}")
        return d['id']
    else:
        print(f"FAIL {title[:40]}: {resp.status_code} ({resp.text[:120]})")
        return None

r = requests.get(base + 'tags', auth=auth, params={'per_page': 100})
TAGS = {}
if r.status_code == 200:
    for t in r.json():
        TAGS[t['slug']] = t['id']

def tag_id(slug):
    if slug in TAGS:
        return TAGS[slug]
    r = requests.post(base + 'tags', auth=auth, json={'name': slug.replace('-', ' ').title(), 'slug': slug})
    if r.status_code == 201:
        TAGS[slug] = r.json()['id']
        return r.json()['id']
    return None

AFF = '<p><em>Como afiliado de Amazon, gano por compras calificadas.</em></p>'

banner = '<div class="actualizacion-2026" style="background:#fffde7;padding:10px 15px;border-radius:6px;margin-bottom:20px;border-left:4px solid #fbc02d;font-size:0.95em;"><strong>📅 Actualizado: Mayo 2026</strong> — Esta guía fue revisada y actualizada con información reciente para ayudarte a identificar y disfrutar las aves nativas de Chile.</div>'

POSTS = [
    # 1. Fardela negra de Juan Fernández
    {
        'title': 'Fardela negra de Juan Fernández: características, hábitat y conservación',
        'body': f'''{banner}
<p>La fardela negra de Juan Fernández (<em>Pterodroma neglecta</em>), también conocida como petrel de Kermadec o kakápu en rapanui, es un ave marina del género Pterodroma que habita en el Pacífico Sur. Su elegante vuelo y su capacidad para recorrer miles de kilómetros sobre el océano la convierten en una de las especies más fascinantes de la avifauna chilena. En Chile anida en el archipiélago de Juan Fernández, en las islas Desventuradas y en Isla de Pascua, siendo un verdadero tesoro de nuestras aguas oceánicas.</p>
<p>Esta fardela presenta un notable polimorfismo en su plumaje: existen fases oscuras, claras e intermedias, lo que hace que cada individuo sea visualmente único. La fase oscura es la más común, con tonos grisáceos a negro pardusco, mientras que la fase clara muestra cabeza, cuello y partes inferiores blanquecinas. Mide unos 39 cm de largo, con una envergadura de 92 cm y un peso aproximado de 500 gramos. Su pico es negro y robusto, típico de los petreles del género Pterodroma.</p>
<p>Se reconocen dos subespecies en aguas chilenas: <em>Pterodroma neglecta neglecta</em>, que se distribuye desde Australia hasta Isla de Pascua, y <em>Pterodroma neglecta juana</em>, que anida en Juan Fernández y las islas Desventuradas. Esta última debe su nombre al archipiélago donde se reproduce.</p>
<h2>Hábitat y distribución de la fardela negra de Juan Fernández</h2>
<p>La fardela negra de Juan Fernández es un ave altamente pelágica, lo que significa que pasa la mayor parte de su vida en alta mar y solo se acerca a tierra firme para reproducirse. Sus colonias de nidificación se encuentran en riscos o laderas con vegetación en islas oceánicas remotas: el archipiélago de Juan Fernández, las islas Desventuradas (San Félix y San Ambrosio), la isla Salas y Gómez, e Isla de Pascua (Rapa Nui).</p>
<p>Fuera de la temporada de cría, su rango se extiende sobre gran parte del Pacífico tropical y subtropical. Se le ha registrado desde Australia y Nueva Zelanda hasta las costas de Japón y Estados Unidos como visitante ocasional. Sin embargo, rara vez se acerca al continente, prefiriendo las aguas oceánicas abiertas.</p>
<h2>Alimentación y comportamiento</h2>
<p>La dieta de la fardela negra de Juan Fernández se compone principalmente de calamares y crustáceos, que captura tanto por inmersión como en la superficie del agua. Es una ave de vuelo ágil y resistente, capaz de planear durante horas aprovechando las corrientes de viento sobre el océano. Durante la noche es más activa en sus colonias, visitando sus nidos después del anochecer para evitar depredadores.</p>
<h2>Reproducción</h2>
<p>La temporada de nidificación es variable según la localidad. Nidifica en riscos o lomas con algo de vegetación, siempre en islas de alta mar. La postura es de un solo huevo blanco. Curiosamente, en las islas Kermadec se ha observado que distintas colonias se turnan la temporada de cría en una misma localidad, un comportamiento poco común entre las aves marinas.</p>
<h2>Estado de conservación</h2>
<p>A nivel global, la UICN clasifica a la fardela negra de Juan Fernández como Preocupación Menor (LC). Sin embargo, la situación es muy distinta en Chile: el Ministerio del Medio Ambiente la considera En Peligro (EN) según la "Nómina de Especies según Estado de Conservación" vigente. Las principales amenazas incluyen la depredación por especies invasoras como gatos y ratas en sus colonias de nidificación, la contaminación lumínica que desorienta a los ejemplares jóvenes, y el impacto de la pesca incidental.</p>
<h2>Dónde observar la fardela negra de Juan Fernández en Chile</h2>
<p>Si eres amante del birdwatching, tu mejor opción para observar esta especie es realizar un tour pelágico en Isla de Pascua, específicamente hacia los motus (islotes) frente a la costa de Rapa Nui. En Motu Nui es posible verlas durante todo el año, aunque la mejor época es durante el verano austral (diciembre a marzo). También hay registros ocasionales frente a las costas de Arica y el norte de Chile durante eventos de surgencia o corrientes oceánicas particulares.</p>''',
        'extra_tags': ['aves-marinas', 'aves-nativas']
    },
    # 2. Fardela de Pascua
    {
        'title': 'Fardela de Pascua: conoce a la Kima de Rapa Nui',
        'body': f'''{banner}
<p>La fardela de Pascua (<em>Puffinus nativitatis</em>), conocida como Kima o Kumá en lengua rapanui, es un ave marina de tamaño mediano que habita en el Pacífico tropical. Su nombre científico hace referencia a la isla de Navidad (Kiritimati), donde fue descrita originalmente. Para Chile, esta especie tiene un valor especial ya que anida exclusivamente en Rapa Nui y en la isla Salas y Gómez, siendo un emblema de la avifauna oceánica del país.</p>
<p>De plumaje completamente pardo oscuro, la fardela de Pascua mide entre 36 y 38 cm de largo, con una envergadura de 71 a 81 cm y un peso de aproximadamente 354 gramos. Sus alas son oscuras tanto por encima como por debajo, aunque las rémiges presentan un tono ligeramente más claro. El pico y las patas son negros. Su aspecto esbelto y su vuelo rápido la distinguen de otras aves marinas del Pacífico.</p>
<p>En Rapa Nui, la Kima forma parte importante de la tradición cultural. Los antiguos rapanui conocían bien a estas aves y las aprovechaban como recurso alimenticio. Hoy en día, es una especie protegida y su observación se ha convertido en un atractivo para el turismo de naturaleza en la isla.</p>
<h2>Hábitat y distribución de la fardela de Pascua</h2>
<p>La fardela de Pascua se distribuye por las aguas tropicales y subtropicales del océano Pacífico central, desde Hawái por el norte hasta Kiribati por el suroeste. En Chile, nidifica en Motu Nui (un pequeño islote frente a Rapa Nui) y en la isla Salas y Gómez, ambos territorios insulares chilenos en el Pacífico suroriental.</p>
<p>Se trata de un ave altamente pelágica que pasa la mayor parte de su vida en mar abierto. Fuera de la temporada reproductiva, se desplaza ampliamente por el Pacífico tropical. La población global se estima en unos 150,000 individuos, considerada estable según los estudios más recientes.</p>
<h2>Alimentación</h2>
<p>La dieta de la fardela de Pascua se compone de calamares, peces y crustáceos, que captura en la superficie o cerca de ella. Su técnica de alimentación implica buceos superficiales y persecuciones activas bajo el agua. Se alimenta frecuentemente sobre bancos de atunes y otros depredadores que empujan a las presas hacia la superficie, lo que facilita su captura.</p>
<p>Es una especie que se alimenta tanto de día como de noche, aunque muestra mayor actividad al amanecer y al atardecer. Los estudios en Hawái y Kiritimati han revelado que los calamares constituyen una parte importante de su dieta, seguidos de peces voladores y peces cabra.</p>
<h2>Reproducción</h2>
<p>La temporada de reproducción en las islas chilenas se extiende de noviembre a marzo. Las colonias son más activas durante el atardecer y la primera mitad de la noche. Construyen sus nidos entre rocas, en vegetación densa o en acantilados. La postura consiste en un solo huevo blanco, de aproximadamente 57 x 39 mm. En otras islas del Pacífico la temporada puede variar, adaptándose a las condiciones locales.</p>
<h2>Estado de conservación</h2>
<p>La UICN clasifica a la fardela de Pascua como Preocupación Menor (LC), aunque en Chile el Ministerio del Medio Ambiente la considera Vulnerable (VU). Las principales amenazas son la introducción de especies invasoras (ratas, gatos) en sus sitios de nidificación, la degradación del hábitat y la contaminación lumínica que desorienta a los polluelos durante sus primeros vuelos nocturnos. En el pasado, la especie fue extirpada de varias islas del Pacífico occidental como Ogasawara y Wake.</p>
<h2>Dónde ver la fardela de Pascua</h2>
<p>Para observar a la Kima en su hábitat natural, la mejor opción es realizar un paseo en bote desde Hanga Roa hacia los motus de Rapa Nui, especialmente Motu Nui. Los tours de observación de aves pelágicas suelen zarpar durante la tarde, cuando las aves regresan del océano a sus sitios de nidificación. La temporada ideal es entre noviembre y marzo, coincidiendo con la época reproductiva. También se han registrado avistamientos en la isla Salas y Gómez, aunque esta es de acceso restringido por su condición de santuario de la naturaleza.</p>''',
        'extra_tags': ['aves-marinas', 'aves-nativas']
    },
    # 3. Fardela heráldica
    {
        'title': 'Fardela heráldica: el petrel de tres colores de Rapa Nui',
        'body': f'''{banner}
<p>La fardela heráldica (<em>Pterodroma heraldica</em>), conocida también como petrel heráldico, petrel del Herald o kakápa en rapanui, es una pequeña ave marina que habita en el Pacífico tropical. Debe su nombre al HMS Herald, buque británico que transportaba al naturalista John MacGillivray, quien recolectó los primeros especímenes de esta especie en el siglo XIX. Es una de las aves marinas más elegantes de las que visitan las costas chilenas, y su presencia en Rapa Nui la convierte en una especie de gran interés para la ornitología nacional.</p>
<p>Mide entre 36 y 40 cm de largo, con una envergadura de 95 a 103 cm y un peso que oscila entre 287 y 460 gramos. Una de sus características más llamativas es su polimorfismo: presenta tres fases de color (clara, intermedia y oscura), lo que durante décadas generó confusión taxonómica. Anteriormente se consideraba una subespecie de <em>Pterodroma arminjoniana</em> (petrel de Trindade), pero hoy la mayoría de los expertos la tratan como una especie separada.</p>
<p>La fase clara tiene el dorso gris oscuro, vientre blanco y una distintiva máscara facial negra. La fase oscura es prácticamente negra en su totalidad, mientras que la fase intermedia combina elementos de ambas. Esta variabilidad la hace especialmente fascinante para los observadores de aves.</p>
<h2>Hábitat y distribución de la fardela heráldica</h2>
<p>La fardela heráldica se distribuye por el Pacífico tropical y subtropical, desde la isla Raine (frente al noreste de Australia) hacia el este hasta Rapa Nui (Isla de Pascua). Las mayores poblaciones se encuentran en el archipiélago de Pitcairn, pero también nidifica en Tonga, Tuamotu, las Marquesas y Gambier. En Chile, su único territorio de nidificación confirmado es la Isla de Pascua, específicamente en los islotes Motu Nui y Motu Iti.</p>
<p>Se trata de un ave solitaria y altamente pelágica que rara vez sigue a los barcos, a diferencia de otras aves marinas. Fuera de la temporada de cría, se dispersa ampliamente por el Pacífico central, con registros hasta Hawái por el norte y las costas de Sudamérica por el este.</p>
<h2>Alimentación</h2>
<p>La alimentación de la fardela heráldica ha sido poco estudiada en adultos, pero los análisis de muestras de polluelos indican que se alimenta principalmente de calamares, complementados con peces, crustáceos e insectos. Captura sus presas en la superficie del agua o mediante buceos superficiales, utilizando su pico robusto y curvado típico de los petreles del género Pterodroma.</p>
<h2>Reproducción</h2>
<p>Nidifica en colonias sueltas, construyendo sus nidos en grietas de rocas, plataformas en riscos, bajo vegetación densa o en pequeños túneles naturales. La postura consiste en un solo huevo de color blanco. La temporada reproductiva varía según la localidad, pero en Rapa Nui se concentra durante el verano austral. Es un ave nocturna en sus colonias, visitando el nido solo después del anochecer para evitar la depredación.</p>
<h2>Estado de conservación</h2>
<p>La UICN clasifica a la fardela heráldica como Preocupación Menor (LC). Sin embargo, enfrenta amenazas significativas en sus sitios de reproducción: la depredación por gatos asilvestrados y ratas introducidas, la degradación del hábitat y la perturbación humana. En Rapa Nui, el acceso controlado a los motus ha permitido que las colonias se mantengan estables, pero la vigilancia constante es necesaria para garantizar su protección.</p>
<h2>Dónde observar la fardela heráldica</h2>
<p>Al igual que otras fardelas de Rapa Nui, la mejor oportunidad de observar a la fardela heráldica es mediante tours pelágicos alrededor de los motus de Isla de Pascua. Los guías locales conocen bien los horarios y las mejores épocas para el avistamiento. La experiencia de ver estas aves planeando sobre las olas del Pacífico, con el paisaje volcánico de Rapa Nui de fondo, es sencillamente inolvidable para cualquier amante de la naturaleza.</p>''',
        'extra_tags': ['aves-marinas', 'aves-nativas']
    },
    # 4. Fardela de Phoenix
    {
        'title': 'Fardela de Phoenix: el petrel blanquinegro en peligro de extinción',
        'body': f'''{banner}
<p>La fardela de Phoenix (<em>Pterodroma alba</em>), también conocida como petrel de Phoenix o petrel de las islas Fénix, es una de las aves marinas más amenazadas del Pacífico suroriental. Debe su nombre a las islas Phoenix (República de Kiribati), donde fueron descubiertas las primeras colonias. Para Chile, esta especie tiene una relevancia particular ya que solo ha sido observada en Isla de Pascua (Rapa Nui), donde se ha confirmado su nidificación en los pequeños islotes frente a la costa.</p>
<p>Esta ave se distingue por su plumaje contrastante: cabeza, cuello y dorso de color pardo oscuro, con una característica mancha blanca en la garganta que se extiende hasta las partes inferiores, que son completamente blancas. Mide aproximadamente 35 cm de largo, con una envergadura de 83 cm y un peso de entre 220 y 340 gramos. Sus alas son puntiagudas, delgadas y largas, de tono pardo oscuro por encima y más claras por debajo. El pico es negro y las patas son de color rosado.</p>
<p>En rapanui, esta especie comparte el nombre genérico de kakápa con otras fardelas del género Pterodroma, reflejando la estrecha relación que la cultura ancestral de la isla tenía con estas aves marinas.</p>
<h2>Hábitat y distribución de la fardela de Phoenix</h2>
<p>La fardela de Phoenix se distribuye por las aguas tropicales y subtropicales del océano Pacífico central. Nidifica en colonias en atolones de coral e islas volcánicas bajas, incluyendo Kanton (islas Phoenix), Kiritimati (islas Line), las Marquesas, Tuamotu y Pitcairn. Se dispersa hasta Hawái por el norte, las islas Kermadec por el oeste y las islas Galápagos por el este. En Chile, solo ha sido registrada en Isla de Pascua, donde se confirmó su reproducción a fines de la década de 1990.</p>
<p>Su llegada a Rapa Nui como especie nidificante es relativamente reciente, posiblemente como parte de cambios en la distribución de las aves marinas del Pacífico asociados a fenómenos oceánicos y climáticos globales.</p>
<h2>Alimentación</h2>
<p>La fardela de Phoenix se alimenta principalmente de calamares, complementados con peces y crustáceos. Captura sus presas buceando o arrebatándolas de la superficie del agua. Es un ave pelágica que se alimenta en mar abierto, a menudo en asociación con bancos de atunes y otros depredadores marinos que concentran a las presas cerca de la superficie.</p>
<h2>Reproducción</h2>
<p>A diferencia de muchos petreles que excavan madrigueras, la fardela de Phoenix nida en la superficie del suelo, bajo vegetación densa o en grietas de rocas. Esta adaptación es posible por la ausencia de depredadores aéreos en las islas tropicales donde habita. La temporada reproductiva en Rapa Nui se concentra durante el verano austral, con una postura de un solo huevo. Es principalmente nocturna en sus colonias para evitar la depredación.</p>
<h2>Estado de conservación</h2>
<p>La UICN clasifica a la fardela de Phoenix como En Peligro (EN), y la población global se estima en declive. En Chile también se considera En Peligro según el Ministerio del Medio Ambiente. Las principales amenazas son la depredación por ratas negras y gatos asilvestrados, la pérdida de hábitat reproductivo y la perturbación humana. En Kiritimati, donde se concentra gran parte de la población mundial, la llegada de la rata negra representa una amenaza crítica que podría llevar a una reducción muy rápida de la población. La mayoría de las colonias sobreviven únicamente en islas libres de depredadores introducidos.</p>
<h2>Dónde observar la fardela de Phoenix</h2>
<p>Avistar la fardela de Phoenix es un verdadero privilegio para cualquier observador de aves. En Chile, la única opción es realizar un tour pelágico en Rapa Nui, navegando hacia los motus (islotes) donde anida. Los mejores meses son entre diciembre y marzo, aunque hay registros durante todo el año. Debido a su estado de conservación, es importante elegir operadores turísticos responsables que respeten las distancias mínimas y no perturben las colonias.</p>''',
        'extra_tags': ['aves-marinas', 'aves-nativas', 'en-peligro-de-extincion']
    },
]

print(f'Creating {len(POSTS)} posts (fardelas faltantes)...')
for i, p in enumerate(POSTS):
    title = p['title']
    slug = title.lower().replace(' ', '-').replace(':', '').replace(',', '').replace('á', 'a').replace('é', 'e').replace('í', 'i').replace('ó', 'o').replace('ú', 'u').replace('ñ', 'n').replace('?', '').replace('/', '')[:80]
    tags = [tag_id('fardela')]
    for et in p['extra_tags']:
        tid = tag_id(et)
        if tid:
            tags.append(tid)
    tags = list(set(t for t in tags if t))
    content = f"{p['body']}{AFF}"
    create_post(title, content, slug, tags=tags)
    if i % 2 == 0:
        time.sleep(1)

print('Done')
