import requests, json, time

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
        print(f"FAIL {title[:40]}: {resp.status_code}")
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

AFF = '<p><em>Como afiliado de Amazon, gano por compras calificadas.</em></p>'

POSTS = [
    {
        'title': "Paloma de Cuello Verdoso: Identificacion, Caracteristicas y Fotos",
        'body': "<p>La paloma de cuello verdoso es una de las especies mas llamativas que podemos encontrar. Su plumaje iridiscente en el cuello la hace facilmente reconocible. Conoce sus caracteristicas, habitat, alimentacion y diferencias con otras palomas. Esta variedad de paloma se caracteriza por su vistoso plumaje verde y purpura en el cuello que brilla con la luz del sol.</p>",
        'extra_tags': ['tipos-de-palomas', 'relacionado-con-palomas']
    },
    {
        'title': "Excremento de Aves de Rapina: Como Identificarlo y Que Significa",
        'body': "<p>El excremento de las aves de rapina tiene caracteristicas muy particulares que permiten identificarlo facilmente. Las rapaces producen egagropilas y sus excrementos tienen alto contenido de calcio y restos oseos. Aprende a reconocer las heces de halcones, aguilas y lechuzas, y descubre su importancia ecologica.</p>",
        'extra_tags': ['aves-rapaces']
    },
    {
        'title': "Bandurria: Significado Espiritual, Caracteristicas y Habitat",
        'body': "<p>La bandurria es un ave fascinante que habita en humedales y zonas cercanas al agua. Su caracteristico pico curvo hacia abajo la hace inconfundible. La bandurria (Theristicus caudatus) pertenece a la familia Threskiornithidae y es conocida por su llamado melodioso. Explora su significado espiritual, habitat en Chile y su importancia ecologica.</p>",
        'extra_tags': ['tipos-de-bandurrias']
    },
    {
        'title': "Que Significa Ver un Buho en la Noche? Mito, Realidad y Simbolismo",
        'body': "<p>Ver un buho en la noche puede ser una experiencia impactante. En diferentes culturas la aparicion de un buho tiene diversos significados: desde presagio hasta simbolo de sabiduria. Los buhos son aves rapaces nocturnas con vision excepcional y vuelo silencioso. Descubre el significado espiritual detras de este encuentro nocturno.</p>",
        'extra_tags': ['aves-nocturnas', 'relacionado-aves-nocturnas']
    },
    {
        'title': "Falconidae: Guia Completa de las Aves Rapaces Diurnas",
        'body': "<p>La familia Falconidae incluye halcones, caracaras y otras aves rapaces diurnas. Se caracterizan por su vuelo rapido, vision aguda y tecnicas de caza especializadas. A diferencia de los Accipitridae, los Falconidae cazan con sus picos. Explora las especies presentes en Chile, sus caracteristicas, habitat y comportamiento.</p>",
        'extra_tags': ['familia-falconidae', 'rapaces-santiago']
    },
    {
        'title': "Pavo Real: Diferencias entre Macho y Hembra + Fotos",
        'body': "<p>El pavo real es una de las aves mas hermosas del mundo. El macho (Pavo cristatus) despliega su cola en abanico durante el cortejo, mientras que la hembra, de color pardo, pasa desapercibida para proteger su nido. Aprende a distinguirlos y descubre sus caracteristicas unicas.</p>",
        'extra_tags': []
    },
    {
        'title': "Chercan: Significado Espiritual y Caracteristicas de esta Ave Chilena",
        'body': "<p>El chercan (Troglodytes aedon) es un pajaro chileno pequeno y activo. Su canto alegre y comportamiento inquieto lo convierten en simbolo de energia y vitalidad. Mide apenas 12 cm y es famoso por su canto potente. Descubre su significado espiritual, caracteristicas y presencia en la cultura popular.</p>",
        'extra_tags': ['aves-nativas']
    },
    {
        'title': "Que Significa la Visita de un Pajaro Gris? Presagio y Significado Espiritual",
        'body': "<p>La visita inesperada de un pajaro gris puede tener significado espiritual. En diferentes tradiciones los pajaros grises se asocian con intuicion y mensajes del universo. Analizamos las interpretaciones espirituales, el simbolismo del color gris en las aves y que significa cuando un pajaro gris visita tu hogar.</p>",
        'extra_tags': ['significado-espiritual']
    },
    {
        'title': "Ave de Plumaje Blanco y Penacho Amarillo: Identificacion y Especies",
        'body': "<p>Existen varias especies de aves con plumaje blanco y penacho amarillo en America del Sur: garzas, garcetas y ciertas especies de loros. Este articulo te ayuda a identificarlas por sus caracteristicas: cuerpo blanco, penacho amarillo en la cabeza, tamanos y habitats. Guia practica para observadores de aves.</p>",
        'extra_tags': ['aves-nativas', 'aves-acuaticas']
    },
    {
        'title': "Piuquen: Significado, Caracteristicas y Habitat de esta Ave Chilena",
        'body': "<p>El piuquen (Chloephaga melanoptera) es un ave de la familia Anatidae que habita en los Andes de Chile y Argentina. Tambien conocido como guayata o cauquen, esta ave de plumaje blanco y negro es simbolo de la fauna altoandina. Conoce sus caracteristicas, habitat y significado cultural.</p>",
        'extra_tags': ['familia-anatidae']
    },
    {
        'title': "Condor Andino: Caracteristicas, Habitat y Donde Verlo en Chile",
        'body': "<p>El condor andino (Vultur gryphus) es el ave voladora mas grande del mundo y simbolo nacional de Chile. Con envergadura alar de mas de 3 metros, habita la cordillera de los Andes y puede vivir hasta 70 anos. Descubre sus caracteristicas, alimentacion, reproduccion y los mejores lugares para avistarlo.</p>",
        'extra_tags': ['aves-endemicas-de-chile', 'cordillera-de-los-andes']
    },
    {
        'title': "Aves en Peligro de Extincion en Chile: Lista 2026 y Como Ayudarlas",
        'body': "<p>Chile alberga gran diversidad de aves pero muchas especies enfrentan amenazas. Segun la Ley de Caza 19473 y UICN, especies como el picaflor de Juan Fernandez, loro tricahue y canquen colorado estan en peligro. Conoce la lista actualizada y como contribuir a su conservacion.</p>",
        'extra_tags': ['en-peligro-de-extincion', 'especies-amenazadas']
    },
    {
        'title': "Gallina Criolla Chilena: Caracteristicas, Crianza y Cuidados",
        'body': "<p>La gallina criolla chilena es una raza adaptada a las condiciones locales, criada por generaciones en campos de Chile. Son resistentes, se alimentan naturalmente y producen huevos de excelente calidad. Aprende sus caracteristicas, como criarlas y los cuidados basicos necesarios.</p>",
        'extra_tags': ['consejos', 'cuidados']
    },
    {
        'title': "Tapaculo: Caracteristicas, Habitat y Curiosidades de esta Ave Chilena",
        'body': "<p>El tapaculo (Scelorchilus rubecula) es una de las aves mas enigmaticas de Chile. Su nombre peculiar y comportamiento esquivo lo convierten en un desafio para observadores. Descubre sus caracteristicas fisicas, habitat, canto y curiosidades de esta fascinante ave de la familia Rhinocryptidae.</p>",
        'extra_tags': ['familia-rhinocryptidae']
    },
]

print(f'Creating {len(POSTS)} posts...')
for i, p in enumerate(POSTS):
    title, body = p['title'], p['body']
    slug = title.lower().replace(' ','-').replace('?','').replace(':','').replace(',','').replace('á','a').replace('é','e').replace('í','i').replace('ó','o').replace('ú','u').replace('/','')[:80]
    tags = [tag_id('aves-nativas')]
    if any(w in title.lower() for w in ['significa','significado','espiritual','simbolismo','presagio','mito']):
        tid = tag_id('significado-espiritual')
        if tid: tags.append(tid)
    for et in p['extra_tags']:
        tid = tag_id(et)
        if tid: tags.append(tid)
    tags = list(set(t for t in tags if t))
    content = f"{body}<p>{AFF}</p>"
    create_post(title, content, slug, tags=tags)
    if i % 3 == 0:
        time.sleep(1)

print('Batch 1 done')
