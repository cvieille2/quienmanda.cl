import requests, time

auth = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
base = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'

def create_post(title, content, slug, tags=[], categories=[64]):
    data = {'title': title, 'content': content, 'slug': slug, 'status': 'publish', 'tags': tags, 'categories': categories}
    resp = requests.post(base + 'posts', auth=auth, json=data)
    if resp.status_code == 201:
        d = resp.json()
        print(f"OK {d['id']}: {title[:60]}")
        return d['id']
    else:
        print(f"FAIL {title[:40]}: {resp.status_code} ({resp.text[:80]})")
        return None

r = requests.get(base + 'tags', auth=auth, params={'per_page':100})
TAGS = {}
if r.status_code == 200:
    for t in r.json():
        TAGS[t['slug']] = t['id']

def tag_id(slug):
    if slug in TAGS:
        return TAGS[slug]
    r = requests.post(base + 'tags', auth=auth, json={'name': slug.replace('-',' ').title(), 'slug': slug})
    if r.status_code == 201:
        TAGS[slug] = r.json()['id']
        return r.json()['id']
    return None

AFF = 'Como afiliado de Amazon, gano por compras calificadas.'
TAG_AFF = f'<p><em>{AFF}</em></p>'

POSTS = [
    {
        'title': "Que Significa que un Aguila se Pare en tu Casa? Significado Espiritual",
        'body': "<p>La presencia de un aguila cerca de tu hogar puede tener un profundo significado espiritual. En muchas culturas el aguila es mensajera de los dioses y simbolo de proteccion. Analizamos las interpretaciones de este encuentro y su relevancia espiritual segun diferentes tradiciones.</p>",
        'extra_tags': ['significado-espiritual', 'relacionados-aguila']
    },
    {
        'title': "Cuanto Vive un Colibri? Esperanza de Vida y Curiosidades",
        'body': "<p>Los colibries o picaflores tienen una esperanza de vida que sorprende dado su pequeno tamano y metabolismo acelerado. En libertad pueden vivir entre 3 y 5 anos, aunque en cautiverio alcanzan hasta 10 anos. Descubre los factores que afectan su longevidad y curiosidades sobre estas fascinantes aves.</p>",
        'extra_tags': ['familia-trochilidae']
    },
    {
        'title': "Gallina Criolla Chilena: Caracteristicas, Crianza y Cuidados",
        'body': "<p>La gallina criolla chilena es una raza adaptada a las condiciones locales, criada por generaciones en campos de Chile. Son resistentes, se alimentan naturalmente y producen huevos de excelente calidad. Aprende sus caracteristicas, como criarlas y los cuidados basicos necesarios.</p>",
        'extra_tags': ['consejos', 'cuidados']
    },
    {
        'title': "Ave Gallinacea de Larga Cola: Identificacion y Especies",
        'body': "<p>Las aves gallinaceas de larga cola incluyen especies fascinantes como faisanes, pavos reales y ciertas gallinaceas selvaticas. Se caracterizan por su cola prolongada y plumaje vistoso. Esta guia te ayuda a identificar las principales especies y conocer sus habitats.</p>",
        'extra_tags': []
    },
    {
        'title': "Que Significa que Muchos Pajaros Vuelen en el Cielo? Explicacion y Simbolismo",
        'body': "<p>Ver muchos pajaros volando en el cielo puede tener explicaciones tanto cientificas como espirituales. Las bandadas pueden indicar migracion, cambios climaticos o simplemente comportamiento social. En el ambito espiritual se asocian con libertad, comunidad y mensajes del universo. Descubre ambas perspectivas.</p>",
        'extra_tags': ['significado-espiritual']
    },
    {
        'title': "Que Significa Ver un Buho Blanco en la Noche? Simbolismo y Mensaje",
        'body': "<p>La aparicion de un buho blanco en la noche es considerada un evento especial en muchas culturas. Asociado con la sabiduria, la pureza y la guia espiritual, este encuentro puede tener multiples interpretaciones. Analizamos el significado de ver un buho blanco segun diferentes tradiciones y el contexto natural de estas aves.</p>",
        'extra_tags': ['aves-nocturnas', 'significado-espiritual']
    },
    {
        'title': "Que Significa que te Ataque un Pajaro Negro? Interpretacion Espiritual",
        'body': "<p>Ser atacado por un pajaro negro puede ser una experiencia alarmante. En el plano espiritual, los pajaros negros como cuervos, zanates o tordos tienen diversos significados. Analizamos las posibles causas del comportamiento agresivo de estas aves y las interpretaciones espirituales en diferentes culturas.</p>",
        'extra_tags': ['significado-espiritual']
    },
    {
        'title': "Cuclillo de Plumaje Negro: Identificacion, Habitat y Curiosidades",
        'body': "<p>El cuclillo de plumaje negro es un ave fascinante de la familia Cuculidae. Conocido por su comportamiento reproductivo unico (parasitismo de nido), esta ave tiene un plumaje oscuro y un canto caracteristico. Descubre sus caracteristicas, habitat, alimentacion y curiosidades sobre esta esquiva especie.</p>",
        'extra_tags': []
    },
    {
        'title': "Ley de Caza 19473 en Chile: Especies Protegidas, Multas y Regulaciones 2026",
        'body': "<p>La Ley de Caza 19473 es la principal normativa que regula la proteccion de fauna silvestre en Chile. Esta ley establece las especies protegidas, las temporadas de caza, las multas por infracciones y las sanciones penales. Conoce las especies de aves protegidas, los requisitos para caza legal y como denunciar infracciones.</p>",
        'extra_tags': ['especies-amenazadas', 'en-peligro-de-extincion']
    },
    {
        'title': "Que Come un Zorzal en Jaula? Alimentacion y Cuidados",
        'body': "<p>Si tienes un zorzal en cautiverio, su alimentacion es clave para su salud. Los zorzales son aves omnivoras que en libertad comen insectos, frutas y lombrices. En jaula necesitan una dieta balanceada con frutas frescas, proteina animal y suplementos. Guia completa de alimentacion y cuidados para zorzales en cautiverio.</p>",
        'extra_tags': ['cuidados', 'consejos']
    },
    {
        'title': "Perdiz Ave: Caracteristicas, Habitat y Curiosidades",
        'body': "<p>La perdiz es un ave gallinacea de tamano mediano que habita en campos abiertos y zonas agricolas. Se caracteriza por su vuelo corto y ruidoso, su plumaje camuflado y su carne apreciada. En Chile existen varias especies de perdices nativas. Descubre sus caracteristicas, habitat, alimentacion y comportamiento.</p>",
        'extra_tags': []
    },
    {
        'title': "Tordo: Caracteristicas, Habitat y Diferencias con Otras Aves Negras",
        'body': "<p>El tordo es un ave de plumaje negro perteneciente a la familia Icteridae. Se diferencia de cuervos y zanates por su tamano menor y su pico mas fino. En Chile habitan varias especies de tordos con caracteristicas particulares. Aprende a identificarlos, conoce su habitat y sus curiosos comportamientos.</p>",
        'extra_tags': []
    },
    {
        'title': "Diucon: Caracteristicas, Habitat, Canto y Leyenda",
        'body': "<p>El diucon (Pyrope pyrope) es un ave chilena de la familia Tyrannidae. Conocido por su canto melodioso y su comportamiento territorial, habita en bosques y zonas arbustivas del centro y sur de Chile. Existen leyendas mapuche sobre esta ave. Descubre sus caracteristicas, habitat, canto y el significado cultural del diucon.</p>",
        'extra_tags': ['familia-de-tyrannidae']
    },
    {
        'title': "Chuncho: El Buho Chileno - Caracteristicas, Habitat y Donde Vive",
        'body': "<p>El chuncho (Glaucidium nanum) es un pequeno buho chileno, tambien conocido como cabure o pequen. Mide apenas 20 cm y se caracteriza por sus ojos amarillos y su canto que suena como silbido. Habita en bosques y zonas arboladas de Chile. Descubre sus caracteristicas, alimentacion, habitat y curiosidades.</p>",
        'extra_tags': ['aves-nocturnas']
    },
    {
        'title': "Hay Aves que Cruzan el Pantano y No se Manch? Significado y Poesia",
        'body': "<p>La frase 'hay aves que cruzan el pantano y no se manchan' es una metafora poetica y espiritual que ha trascendido generaciones. Analizamos el origen de esta expresion, su significado literal en la naturaleza (aves acuaticas con plumajes impermeables) y su profundo mensaje espiritual sobre la pureza y la resiliencia.</p>",
        'extra_tags': ['significado-espiritual']
    },
    {
        'title': "Zorzal: Caracteristicas, Habitat, Canto y Tipos en Chile",
        'body': "<p>El zorzal es un ave paseriforme de la familia Turdidae, conocida por su hermoso canto. En Chile existen varias especies como el zorzal comun, el zorzal colorado y el zorzal de patagonia. Se alimentan de insectos y frutas, y habitan en bosques, jardines y zonas urbanas. Guia completa sobre estas aves cantoras.</p>",
        'extra_tags': ['tipos-de-zorzal']
    },
    {
        'title': "Que Significa Ver un Aguila en la Biblia? Significado Profetico y Versiculos",
        'body': "<p>El aguila aparece numerosas veces en la Biblia con un poderoso significado simbolico. Representa renovacion, proteccion divina, poder espiritual y vision profetica. Versiculos como Isaias 40:31 comparan la fe con las alas del aguila. Exploramos el significado profetico del aguila en las Escrituras y su mensaje espiritual.</p>",
        'extra_tags': ['significado-espiritual', 'relacionados-aguila']
    },
    {
        'title': "Aves Chilenas y sus Nombres: Guia Completa con Fotos",
        'body': "<p>Chile alberga mas de 500 especies de aves, desde el imponente condor andino hasta el pequeno chercan. Esta guia completa te presenta las aves chilenas mas representativas con sus nombres comunes y cientificos, fotos y datos clave. Perfecta para birdwatchers, estudiantes y amantes de la naturaleza que quieren identificar aves de Chile.</p>",
        'extra_tags': ['aves-nativas', 'guia']
    },
]

print(f'Creating {len(POSTS)} posts (batch 2)...')
for i, p in enumerate(POSTS):
    title, body = p['title'], p['body']
    slug = title.lower().replace(' ','-').replace('?','').replace(':','').replace(',','').replace('á','a').replace('é','e').replace('í','i').replace('ó','o').replace('ú','u').replace('/','')[:80]
    tags = [tag_id('aves-nativas')]
    if any(w in title.lower() for w in ['significa','significado','espiritual','simbolismo','presagio','mito','profetico','biblia']):
        tid = tag_id('significado-espiritual')
        if tid: tags.append(tid)
    for et in p['extra_tags']:
        tid = tag_id(et)
        if tid: tags.append(tid)
    tags = list(set(t for t in tags if t))
    
    aff_link = ''
    if 'condor' in title.lower() or 'avist' in title.lower() or 'guia' in title.lower():
        aff_link = f'<p>🔭 <a href=\"https://www.amazon.es/s?k=binoculares+observacion+aves&tag=avesnativas-21\" target=\"_blank\" rel=\"nofollow sponsored\">Mejores binoculares para birdwatching en Amazon.es</a></p>'
    
    content = f'{body}{aff_link}{TAG_AFF}'
    create_post(title, content, slug, tags=tags)
    if i % 3 == 0:
        time.sleep(1)

print('Batch 2 done')
