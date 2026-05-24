import json
import requests

AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'


def update(kind, pid, title, content, excerpt=None):
    payload = {'title': title, 'content': content}
    if excerpt is not None:
        payload['excerpt'] = excerpt
    return requests.post(BASE + f'{kind}/{pid}', auth=AUTH, json=payload, timeout=30)


def faq_schema(faqs):
    items = [{'@type': 'Question', 'name': q, 'acceptedAnswer': {'@type': 'Answer', 'text': a}} for q, a in faqs]
    return '<script type="application/ld+json">' + json.dumps({'@context': 'https://schema.org', '@type': 'FAQPage', 'mainEntity': items}, ensure_ascii=False) + '</script>'


ITEMS = [
    {
        'kind': 'posts',
        'id': 1669,
        'title': 'Lechuzas y buhos en Chile: habitat, alimentacion y simbolismo',
        'excerpt': 'Guia de lechuzas y buhos en Chile: como reconocerlos, donde viven, que comen y que significan en cultura y naturaleza.',
        'body': '''<div class="actualizacion-2026" style="background:#fffde7;padding:10px 15px;border-radius:6px;margin-bottom:20px;border-left:4px solid #fbc02d;font-size:0.95em;"><strong>Actualizado: Mayo 2026</strong> - Esta guia separa lechuzas, buhos y rutas de simbolismo sin perder la base biologica.</div>

<p>Las lechuzas y los buhos dominan la noche con vuelo silencioso, ojos grandes y una capacidad de caza muy afinada. En Chile, el grupo incluye especies muy buscadas por su simbolismo, su control de plagas y su presencia en paisajes rurales y urbanos.</p>

<p>Si buscas una especie concreta, empieza por <a href="https://avesnativaschilenas.cl/rapaces/lechuza/tucuquere/">tucuquere</a>, <a href="https://avesnativaschilenas.cl/rapaces/lechuza/pequen/">pequen</a> o <a href="https://avesnativaschilenas.cl/rapaces/lechuza/">la guia general de lechuzas</a>. Si lo que quieres es entender el simbolismo, revisa tambien <a href="https://avesnativaschilenas.cl/rapaces/lechuza/que-significa-ver-una-lechuza-de-noche/">ver una lechuza de noche</a> y <a href="https://avesnativaschilenas.cl/rapaces/lechuza/las-lechuzas-traen-buena-suerte-simbolismo/">buena suerte y simbolismo</a>.</p>

<h2>Lechuzas y buhos: que los diferencia</h2>
<p>En lenguaje popular se mezclan mucho. La lechuza suele asociarse a rostros claros y disco facial marcado, mientras que el buho agrupa varias rapaces nocturnas de aspecto robusto y conducta muy silenciosa.</p>

<h2>Donde viven</h2>
<p>Habitan campos, bosques, bordes rurales y zonas con refugios naturales o construcciones tranquilas. Algunas especies toleran bien la presencia humana; otras prefieren sectores poco perturbados y con presas abundantes.</p>

<h2>Que comen</h2>
<p>Su dieta suele incluir roedores, insectos grandes, pequenos reptiles y, en algunos casos, aves pequenas. Por eso son aliadas del equilibrio natural y del control biologico.</p>

<h2>Rol ecologico</h2>
<p>Reducen plagas, evitan explosiones de roedores y sirven como indicador de ambientes sanos. Cuando una lechuza o un buho desaparecen, suele ser señal de que algo en el paisaje se altero.</p>

<h2>Simbolismo y realidad</h2>
<p>En culturas populares pueden aparecer como presagio, sabiduria o misterio. Pero la realidad es mas interesante: son depredadores nocturnos muy especializados.</p>

<h2>Preguntas frecuentes</h2>
<h3>Lechuza y buho son lo mismo?</h3>
<p>No siempre. En uso popular se mezclan, pero biologicamente hay diferencias de forma y grupo.</p>
<h3>Son buenas para el campo?</h3>
<p>Si, porque ayudan a controlar roedores y otras presas pequenas.</p>
<h3>Traen mala suerte?</h3>
<p>No. Ese es un mito cultural sin base biologica.</p>
<h3>Donde las veo en Chile?</h3>
<p>En campos, bordes rurales, bosques y zonas tranquilas con refugio y alimento.</p>
<h3>Se pueden observar de cerca?</h3>
<p>No conviene. Lo mejor es verlas a distancia y sin interferir en su actividad nocturna.</p>
''',
        'faqs': [
            ('Lechuza y buho son lo mismo?', 'No siempre. En uso popular se mezclan, pero biologicamente hay diferencias de forma y grupo.'),
            ('Son buenas para el campo?', 'Si, porque ayudan a controlar roedores y otras presas pequenas.'),
            ('Traen mala suerte?', 'No. Ese es un mito cultural sin base biologica.'),
            ('Donde las veo en Chile?', 'En campos, bordes rurales, bosques y zonas tranquilas con refugio y alimento.'),
            ('Se pueden observar de cerca?', 'No conviene. Lo mejor es verlas a distancia y sin interferir en su actividad nocturna.'),
        ],
    },
    {
        'kind': 'posts',
        'id': 722,
        'title': 'Halcones chilenos: como son, habitat y comportamiento',
        'excerpt': 'Guia de halcones chilenos: tamano, vuelo, habitat, caza y diferencias con aguilas y otras rapaces.',
        'body': '''<div class="actualizacion-2026" style="background:#fffde7;padding:10px 15px;border-radius:6px;margin-bottom:20px;border-left:4px solid #fbc02d;font-size:0.95em;"><strong>Actualizado: Mayo 2026</strong> - Esta guia separa mejor a los halcones de aguilas, lechuzas y otras rapaces chilenas.</div>

<p>Los halcones chilenos son rapaces veloces, precisas y muy adaptables. A diferencia de las aguilas, que suelen destacar por el planeo y la presencia dominante, los halcones sobresalen por la velocidad, el picado y la capacidad de maniobra.</p>

<p>Si tu objetivo es comparar rapaces grandes, revisa la guia de <a href="https://avesnativaschilenas.cl/aguilas/">aguilas en Chile</a>. Si buscas rapaces nocturnas, entra a <a href="https://avesnativaschilenas.cl/rapaces/lechuza/">lechuzas y buhos</a>.</p>

<h2>Como son los halcones</h2>
<p>Su cuerpo es aerodinamico, con alas pensadas para maniobrar y una vision capaz de detectar presas a distancia. En Chile se asocian con ambientes abiertos, costeros, montanos y urbanos.</p>

<h2>Diferencias con aguilas</h2>
<p>Las aguilas suelen ser mas grandes y planeadoras; los halcones, mas rapidos y compactos. Si ves un ave de vuelo explosivo y silueta afinada, probablemente estes mirando un halcon y no una aguila.</p>

<h2>Especies relacionadas</h2>
<ul>
<li><a href="https://avesnativaschilenas.cl/aguilas/">Aguilas en Chile</a></li>
<li><a href="https://avesnativaschilenas.cl/aguilas/aguila-peregrina/">Aguila peregrina</a></li>
<li><a href="https://avesnativaschilenas.cl/rapaces/rapaces-santiago/">Rapaces de Santiago</a></li>
<li><a href="https://avesnativaschilenas.cl/rapaces/falconidae-guia-completa-de-las-aves-rapaces-diurnas/">Falconidae</a></li>
</ul>

<h2>Preguntas frecuentes</h2>
<h3>Que diferencia a un halcon de un aguila?</h3>
<p>El halcon suele ser mas compacto y rapido; la aguila, mas grande y planeadora.</p>
<h3>Se ven en ciudades?</h3>
<p>Si, algunas especies se adaptan bien a edificios altos y espacios abiertos urbanos.</p>
<h3>Que comen?</h3>
<p>Aves pequenas, insectos grandes y otras presas disponibles segun la especie.</p>
<h3>Son utiles ecologicamente?</h3>
<p>Si, porque ayudan a regular poblaciones de presas y mantienen equilibrio en el ecosistema.</p>
''',
        'faqs': [
            ('Que diferencia a un halcon de un aguila?', 'El halcon suele ser mas compacto y rapido; la aguila, mas grande y planeadora.'),
            ('Se ven en ciudades?', 'Si, algunas especies se adaptan bien a edificios altos y espacios abiertos urbanos.'),
            ('Que comen?', 'Aves pequenas, insectos grandes y otras presas disponibles segun la especie.'),
            ('Son utiles ecologicamente?', 'Si, porque ayudan a regular poblaciones de presas y mantienen equilibrio en el ecosistema.'),
        ],
    },
    {
        'kind': 'posts',
        'id': 15121,
        'title': 'Las lechuzas y buhos traen buena suerte? Simbolismo, mitos y realidad',
        'excerpt': 'Respuesta clara sobre la buena suerte, el simbolismo y la biologia real de lechuzas y buhos.',
        'body': '''<p>Muchas culturas han visto a las lechuzas y buhos como portadores de buena o mala suerte. La realidad es menos mistica: estas aves generan simbolismo porque son nocturnas, silenciosas y muy llamativas.</p>

<p>Si quieres entender el grupo completo, vuelve a la <a href="https://avesnativaschilenas.cl/rapaces/lechuza/">guia de lechuzas y buhos</a> y, si buscas observacion general de rapaces, revisa <a href="https://avesnativaschilenas.cl/rapaces/">la categoria principal de rapaces</a>.</p>

<h2>De donde viene el mito</h2>
<p>En muchas tradiciones, la noche se asocia a misterio, y las aves nocturnas terminan cargadas de significado. Eso explica por que se les vincula con presagios, sabiduria o proteccion.</p>

<h2>La realidad biologica</h2>
<p>No traen suerte por si mismas. Son depredadores nocturnos especializados que ayudan a controlar roedores y otras presas pequenas.</p>

<h2>Preguntas frecuentes</h2>
<h3>Traen buena suerte?</h3>
<p>Como simbolo cultural, depende de la tradicion. Como ave real, no existe una regla universal de suerte.</p>
<h3>Son utiles?</h3>
<p>Si, porque ayudan a controlar plagas y mantener el equilibrio ecologico.</p>
<h3>Son peligrosas?</h3>
<p>No para las personas en condiciones normales.</p>
''',
        'faqs': [
            ('Traen buena suerte?', 'Como simbolo cultural, depende de la tradicion. Como ave real, no existe una regla universal de suerte.'),
            ('Son utiles?', 'Si, porque ayudan a controlar plagas y mantener el equilibrio ecologico.'),
            ('Son peligrosas?', 'No para las personas en condiciones normales.'),
        ],
    },
    {
        'kind': 'posts',
        'id': 9758,
        'title': 'La importancia de las lechuzas en el control de plagas en Chile',
        'excerpt': 'Por que las lechuzas son aliadas naturales en el control biologico de plagas y como favorecer su presencia.',
        'body': '''<div class="actualizacion-2026" style="background:#fffde7;padding:10px 15px;border-radius:6px;margin-bottom:20px;border-left:4px solid #fbc02d;font-size:0.95em;"><strong>Actualizado: Mayo 2026</strong> - Esta guia explica el valor real de las lechuzas como control biologico, sin exagerar ni romantizar su papel.</div>

<p>Las lechuzas son aliadas naturales en el control de plagas porque cazan roedores y otros pequenos animales que danan cultivos o bodegas.</p>

<p>Si quieres ver el grupo completo, vuelve a la <a href="https://avesnativaschilenas.cl/rapaces/lechuza/">guia de lechuzas y buhos</a>.</p>

<h2>Por que controlan plagas</h2>
<p>Al cazar ratones, topillos y otras presas pequenas, reducen danos en huertos, graneros y zonas rurales.</p>

<h2>Como favorecerlas</h2>
<ul>
<li>Evita venenos que puedan afectarlas por cadena alimentaria.</li>
<li>Mantén sectores rurales con refugios naturales.</li>
<li>No molestes nidos ni sitios de descanso.</li>
</ul>

<h2>Preguntas frecuentes</h2>
<h3>Sirven para controlar roedores?</h3>
<p>Si, son una de las aves mas efectivas para ese trabajo natural.</p>
<h3>Necesitan ayuda humana?</h3>
<p>Principalmente necesitan habitat sano y menos perturbacion.</p>
<h3>Puedo atraerlas con alimento?</h3>
<p>No conviene. Lo mejor es mantener un ambiente adecuado y no forzarlas.</p>
''',
        'faqs': [
            ('Sirven para controlar roedores?', 'Si, son una de las aves mas efectivas para ese trabajo natural.'),
            ('Necesitan ayuda humana?', 'Principalmente necesitan habitat sano y menos perturbacion.'),
            ('Puedo atraerlas con alimento?', 'No conviene. Lo mejor es mantener un ambiente adecuado y no forzarlas.'),
        ],
    },
]


def main():
    ok = 0
    for item in ITEMS:
        content = item['body'] + '\n\n' + faq_schema(item['faqs'])
        resp = update(item['kind'], item['id'], item['title'], content, item.get('excerpt'))
        if resp.status_code == 200:
            ok += 1
            print(f"OK {item['id']} -> {item['title']}")
        else:
            print(f"ERR {item['id']} -> {resp.status_code}")
    print(f'Total actualizados: {ok}/{len(ITEMS)}')


if __name__ == '__main__':
    main()
