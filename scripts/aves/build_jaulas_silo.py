import json, urllib.request, base64

CREDS = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
AUTH = 'Basic ' + base64.b64encode(f'{CREDS[0]}:{CREDS[1]}'.encode()).decode()
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2'
PILLAR_ID = 6492
PILLAR_URL = 'https://avesnativaschilenas.cl/jaulas/'

def api(method, endpoint, data=None):
    req = urllib.request.Request(f'{BASE}{endpoint}', method=method)
    req.add_header('Authorization', AUTH)
    req.add_header('User-Agent', 'opencode/1.0')
    if data:
        req.add_header('Content-Type', 'application/json')
        req.data = json.dumps(data).encode('utf-8')
    with urllib.request.urlopen(req, timeout=30) as resp:
        return json.loads(resp.read())

# ─── Classification function ───
def classify_post(slug, title, raw):
    s = slug.lower().replace('-', ' ')
    t = title.lower()
    r = raw.lower()[:500]
    acc_kw = ['cubierta','funda','bandeja','rejilla','manija','manilla','cierre','gancho','puerta','cerradura','cepillos','limpieza','hoja','hojas','malla','protector','escudo','pieza','piezas','accesorio','accesorios','vision','comedero','bebedero','cupula','pie perfecto','red de proteccion','protector']
    if any(kw in s or kw in t for kw in acc_kw):
        return 'accesorios'
    port_kw = ['portatil','viaje','plegable','transporte','transportador','mano para viajes','viajar','aventura','aire libre','viajes','portabebes']
    if any(kw in s or kw in t for kw in port_kw):
        return 'portatiles'
    loro_kw = ['loro','cacatua','conure','ninfa']
    if any(kw in s or kw in t for kw in loro_kw):
        return 'para-loros'
    if any(kw in s or kw in t for kw in ['periquito','canario']):
        return 'para-pajaros-pequenos'
    if any(kw in s or kw in t for kw in ['paloma']):
        return 'para-palomas'
    dec_kw = ['vintage','decorativa','decoracion','madera','pagoda','shabby','bohemio','colgante']
    if any(kw in s or kw in t for kw in dec_kw):
        return 'decorativas'
    if any(kw in s or kw in t for kw in ['grande','aviario','jaulon','espacioso','60 pulgadas','lujo','guinealoft']):
        return 'grandes'
    return 'modelos'

# ─── Get all posts in jaulas category ───
all_posts = []
page = 1
while True:
    batch = api('GET', f'/posts?categories=163&per_page=100&page={page}&_fields=id,title,slug,content&context=edit')
    if not batch:
        break
    all_posts.extend(batch)
    page += 1
    if len(batch) < 100:
        break

print(f'Total posts: {len(all_posts)}')

# Classify and group
groups = {}
for p in all_posts:
    raw = p.get('content', {}).get('raw', '')
    g = classify_post(p['slug'], p['title']['rendered'], raw)
    groups.setdefault(g, []).append({'id': p['id'], 'slug': p['slug'], 'title': p['title']['rendered']})

for g in sorted(groups):
    print(f'  {g}: {len(groups[g])}')

# ─── Sub-pillar page content definitions ───
FEATURES_TABLE_ROWS = """<tr><td><strong>Extra\u00edble</strong></td><td>Bandeja o base extra\u00edble que facilita la limpieza diaria sin esfuerzo.</td></tr>
<tr><td><strong>Duradera</strong></td><td>Fabricada con materiales resistentes como acero o metal tratado, soporta el uso diario y el paso del tiempo.</td></tr>
<tr><td><strong>Segura</strong></td><td>Dise\u00f1o sin bordes afilados, con cierres firmes que evitan escapes accidentales.</td></tr>
<tr><td><strong>Plegable</strong></td><td>Se pliega f\u00e1cilmente para guardarla o transportarla sin ocupar espacio.</td></tr>
<tr><td><strong>Bloqueable</strong></td><td>Cierres de seguridad que impiden que el ave abra la puerta por s\u00ed misma.</td></tr>
<tr><td><strong>Abatible</strong></td><td>Puertas o laterales que se abaten para facilitar el acceso y la interacci\u00f3n con el ave.</td></tr>
<tr><td><strong>Espaciosa</strong></td><td>Dimensiones amplias que permiten al ave moverse, estirar las alas y ejercitarse c\u00f3modamente.</td></tr>
<tr><td><strong>Estable</strong></td><td>Base ancha y equilibrada que evita vuelcos, incluso con aves grandes o activas.</td></tr>
<tr><td><strong>Ligera</strong></td><td>F\u00e1cil de mover y transportar sin sacrificar resistencia estructural.</td></tr>
<tr><td><strong>Port\u00e1til</strong></td><td>Dise\u00f1ada para llevarla de viaje o cambiarla de ubicaci\u00f3n con comodidad.</td></tr>"""

def make_faq_schema(faqs):
    items = [f'{{"@type":"Question","name":{json.dumps(q)},"acceptedAnswer":{{"@type":"Answer","text":{json.dumps(a)}}}}}' for q, a in faqs]
    return f'<script type="application/ld+json">{{"@context":"https://schema.org","@type":"FAQPage","mainEntity":[{",".join(items)}]}}</script>'

SUBPAGES = [
    {
        'slug': 'portatiles',
        'title': 'Jaulas Port\u00e1tiles para Aves: Gu\u00eda de Compra y Mejores Modelos',
        'excerpt': 'Descubre las mejores jaulas port\u00e1tiles, plegables y de viaje para tus aves. Compara modelos ligeros y seguros para llevar a tu loro, periquito o canario a cualquier parte.',
        'intro': 'Si te gusta viajar con tu ave o necesitas una jaula que puedas mover f\u00e1cilmente por la casa, las jaulas port\u00e1tiles son la soluci\u00f3n ideal. Este tipo de jaula incluye modelos plegables, transportadores y jaulas de viaje dise\u00f1adas para ser ligeras y funcionales sin sacrificar la seguridad de tu mascota.',
        'features_note': 'Las jaulas port\u00e1tiles destacan por ser ligeras y plegables, pero tambi\u00e9n deben ser seguras y duraderas. Estas son las caracter\u00edsticas clave que debes considerar:',
        'faqs': [
            ('\u00bfQu\u00e9 tama\u00f1o de jaula port\u00e1til necesito para mi loro?', 'El tama\u00f1o depende de la especie. Para loros medianos como ninfas o conures, busca una jaula de al menos 40x30x40 cm. Para loros grandes, opta por un transportador robusto de acero. La clave es que el ave pueda pararse erguida y girar sin problemas.'),
            ('\u00bfLas jaulas plegables son seguras para viajes largos?', 'S\u00ed, siempre que tengan cierres bloqueables y barrotes de metal resistente. Las mejores opciones incluyen candados de seguridad y materiales como acero con recubrimiento electrost\u00e1tico que evita la oxidaci\u00f3n.'),
            ('\u00bfPuedo usar una jaula port\u00e1til como hogar permanente?', 'No es recomendable. Las jaulas port\u00e1tiles est\u00e1n dise\u00f1adas para viajes, visitas al veterinario o estancias temporales. Para el hogar permanente, elige una jaula espaciosa de nuestra gu\u00eda principal.'),
            ('\u00bfQu\u00e9 accesorios necesita una jaula de viaje?', 'Como m\u00ednimo, perchas firmes, comederos y bebederos antiderrame, y una bandeja extra\u00edble para limpieza. Algunos modelos incluyen fundas protectoras para reducir el estr\u00e9s del ave durante el traslado.'),
        ]
    },
    {
        'slug': 'para-loros',
        'title': 'Jaulas para Loros: Gu\u00eda Completa para Elegir la Jaula Perfecta',
        'excerpt': 'Encuentra la jaula ideal para tu loro, cacat\u00faa o conure. Compara modelos robustos, seguros y espaaciosos con barrotes resistentes y cierres bloqueables.',
        'intro': 'Los loros son aves inteligentes y activas que necesitan jaulas espaciosas y resistentes. Elegir la jaula correcta es fundamental para su bienestar f\u00edsico y mental. En esta gu\u00eda encontrar\u00e1s jaulas dise\u00f1adas espec\u00edficamente para loros, cacat\u00faas, conures y ninfas, con barrotes gruesos, cierres seguros y espacio suficiente para juguetes y accesorios.',
        'features_note': 'Para jaulas de loros, prioriza la seguridad y la durabilidad sobre la portabilidad. Estas caracter\u00edsticas son las m\u00e1s importantes:',
        'faqs': [
            ('\u00bfCu\u00e1l es el tama\u00f1o m\u00ednimo de jaula para un loro?', 'El tama\u00f1o m\u00ednimo recomendado es de 60x50x80 cm para loros medianos. Para especies grandes como guacamayos, necesitar\u00e1s al menos 90x70x120 cm. La regla de oro: la jaula debe permitir que el loro extienda completamente sus alas sin tocar los barrotes.'),
            ('\u00bfQu\u00e9 tipo de barrotes son mejores para loros?', 'Los barrotes de acero inoxidable o metal con recubrimiento son los m\u00e1s recomendados. El espacio entre barrotes no debe superar los 2-3 cm para loros medianos. Los barrotes horizontales son ideales porque permiten trepar.'),
            ('\u00bfC\u00f3mo evitar que mi loro escape de la jaula?', 'Busca jaulas con cierres bloqueables tipo candado o pestillo de seguridad. Algunos loros aprenden a abrir pestillos simples, por lo que recomendamos sistemas de doble seguridad.'),
            ('\u00bfEs mejor una jaula con bandeja extra\u00edble?', 'S\u00ed, las bandejas extra\u00edbles facilitan enormemente la limpieza diaria y ayudan a mantener la higiene del ave, previniendo enfermedades respiratorias.'),
        ]
    },
    {
        'slug': 'accesorios',
        'title': 'Accesorios para Jaulas de Aves: Fundas, Bandejas, Rejillas y M\u00e1s',
        'excerpt': 'Todo lo que necesitas para equipar la jaula de tu ave: cubiertas, bandejas extra\u00edbles, rejillas, comederos, bebederos y cepillos de limpieza.',
        'intro': 'Los accesorios adecuados transforman una jaula b\u00e1sica en un hogar completo y funcional para tu ave. Desde cubiertas protectoras hasta bandejas extra\u00edbles y rejillas de separaci\u00f3n, cada accesorio cumple una funci\u00f3n espec\u00edfica para mejorar la calidad de vida de tu mascota y facilitar su cuidado diario.',
        'features_note': 'Al elegir accesorios, valora estos aspectos clave que marcan la diferencia en el d\u00eda a d\u00eda:',
        'faqs': [
            ('\u00bfLas fundas para jaulas son necesarias?', 'Las fundas ayudan a regular la luz y temperatura, reducen el estr\u00e9s del ave y favorecen el sue\u00f1o. Son especialmente \u00fatiles en habitaciones con mucha luz artificial o si tu ave se estresa f\u00e1cilmente.'),
            ('\u00bfCada cu\u00e1nto debo cambiar la bandeja de la jaula?', 'La bandeja debe limpiarse a diario. Las bandejas extra\u00edbles facilitan esta tarea. Usa papel peri\u00f3dico o sustratos especiales y renueva el fondo cada 24 horas para evitar acumulaci\u00f3n de bacterias.'),
            ('\u00bfQu\u00e9 tipo de comedero es mejor para evitar desperdicios?', 'Los comederos antiderrame con borde elevado son los m\u00e1s eficaces. Tambi\u00e9n existen comederos de rejilla que obligan al ave a extraer la semilla una por una, reduciendo el desperdicio.'),
            ('\u00bfC\u00f3mo limpiar correctamente los accesorios de la jaula?', 'Usa agua caliente y vinagre blanco (nunca lej\u00eda ni productos t\u00f3xicos). Los cepillos de limpieza con cerdas firmes y mangos largos facilitan llegar a todas las \u00e1reas.'),
        ]
    },
    {
        'slug': 'para-pajaros-pequenos',
        'title': 'Jaulas para P\u00e1jaros Peque\u00f1os: Canarios, Periquitos y Ninfas',
        'excerpt': 'Las mejores jaulas para canarios, periquitos y p\u00e1jaros peque\u00f1os. Modelos con barrotes estrechos, perchas m\u00faltiples y accesorios para aves activas.',
        'intro': 'Los p\u00e1jaros peque\u00f1os como canarios, periquitos y ninfas necesitan jaulas adaptadas a su tama\u00f1o y comportamiento. Aunque son aves m\u00e1s d\u00f3ciles, requieren espacio para volar entre perchas, juguetes para estimularse y una ventilaci\u00f3n adecuada. En esta gu\u00eda encontrar\u00e1s modelos dise\u00f1ados espec\u00edficamente para ellos.',
        'features_note': 'Para aves peque\u00f1as, la separaci\u00f3n entre barrotes y la facilidad de limpieza son prioritarias:',
        'faqs': [
            ('\u00bfQu\u00e9 separaci\u00f3n entre barrotes es ideal para periquitos?', 'M\u00e1ximo 1,5 cm entre barrotes. Si la separaci\u00f3n es mayor, el ave podr\u00eda intentar escapar o quedar atascada. Para canarios, busca entre 1 y 1,2 cm.'),
            ('\u00bfCu\u00e1nto espacio necesita un canario en su jaula?', 'M\u00ednimo 40x30x40 cm para un canario. Si tienes m\u00e1s de uno, duplica el tama\u00f1o. Las jaulas alargadas (m\u00e1s anchas que altas) son mejores porque permiten vuelos cortos de percha a percha.'),
            ('\u00bfLas jaulas para periquitos deben tener forma especial?', 'Se recomiendan jaulas rectangulares o cuadradas. Las jaulas redondas pueden desorientar al ave y no ofrecen esquinas donde refugiarse. Adem\u00e1s, las rectangulares aprovechan mejor el espacio interior.'),
            ('\u00bfQu\u00e9 accesorios son imprescindibles para aves peque\u00f1as?', 'Perchas de diferentes grosores, comederos externos, un bebedero tipo tubo, una bandeja extra\u00edble y al menos dos juguetes para evitar el aburrimiento.'),
        ]
    },
    {
        'slug': 'decorativas',
        'title': 'Jaulas Decorativas, Vintage y de Madera para Aves',
        'excerpt': 'Jaulas que combinan estilo y funcionalidad: vintage, madera, pagoda, shabby chic y modelos decorativos que embellecen tu hogar mientras cuidan de tu ave.',
        'intro': 'Si buscas una jaula que sea tanto un hogar para tu ave como un elemento decorativo, las jaulas decorativas y vintage son tu opci\u00f3n. Con dise\u00f1os que van desde el estilo victoriano hasta el shabby chic, estas jaulas a\u00f1aden personalidad a cualquier espacio sin descuidar el bienestar de tu mascota.',
        'features_note': 'En jaulas decorativas, el equilibrio entre est\u00e9tica y funcionalidad es clave. Presta atenci\u00f3n a estas caracter\u00edsticas:',
        'faqs': [
            ('\u00bfLas jaulas de madera son seguras para las aves?', 'S\u00ed, siempre que la madera est\u00e9 tratada con productos no t\u00f3xicos y resistentes a la humedad. La madera de haya y pino tratado son opciones seguras. Evita maderas con barnices o pinturas t\u00f3xicas.'),
            ('\u00bfQu\u00e9 cuidados especiales requieren las jaulas vintage?', 'Requieren limpieza frecuente con pa\u00f1o h\u00famedo y revisi\u00f3n peri\u00f3dica de las uniones. Las jaulas de metal con recubrimiento en polvo son m\u00e1s resistentes que las pintadas a mano. Aplica aceite protector en las bisagras.'),
            ('\u00bfPuedo usar una jaula decorativa para un loro grande?', 'Depende del dise\u00f1o. Las jaulas decorativas suelen ser para aves peque\u00f1as o medianas. Para loros grandes, prioriza la robustez sobre la est\u00e9tica.'),
            ('\u00bfLas jaulas colgantes son recomendables?', 'Son ideales para espacios peque\u00f1os, pero aseg\u00farate de que el soporte sea firme y la jaula est\u00e9 bien equilibrada. No son recomendables para aves grandes o muy activas.'),
        ]
    },
    {
        'slug': 'grandes',
        'title': 'Jaulas Grandes y Aviarios para Aves: Espacio y Libertad',
        'excerpt': 'Jaulas espaciosas, jaulones y aviarios para aves grandes o m\u00faltiples mascotas. Modelos con estructura reforzada, ruedas y acceso f\u00e1cil.',
        'intro': 'Las aves grandes como guacamayos, cacat\u00faas o loros de gran tama\u00f1o necesitan jaulas que les ofrezcan espacio real para moverse. Los jaulones y aviarios est\u00e1n dise\u00f1ados para proporcionar un entorno amplio, con estructura reforzada y acceso c\u00f3modo para la limpieza y la interacci\u00f3n.',
        'features_note': 'En jaulas grandes, la estabilidad, la durabilidad y el espacio son factores determinantes:',
        'faqs': [
            ('\u00bfCu\u00e1nto debe medir un aviario para dos loros?', 'Como m\u00ednimo 120x70x150 cm. Los aviarios permiten una vida m\u00e1s natural, con espacio para vuelos cortos, m\u00faltiples perchas y zonas de alimentaci\u00f3n separadas.'),
            ('\u00bfEs mejor un aviario de interior o exterior?', 'Depende del clima y la especie. Los aviarios de interior ofrecen temperatura controlada; los de exterior requieren protecci\u00f3n contra lluvia y viento pero aportan luz natural y ventilaci\u00f3n.'),
            ('\u00bfLas jaulas con ruedas son seguras para aves grandes?', 'S\u00ed, siempre que las ruedas tengan frenos de seguridad. Las ruedas facilitan la limpieza y permiten mover la jaula para aprovechar la luz solar durante el d\u00eda.'),
            ('\u00bfQu\u00e9 material es m\u00e1s resistente para jaulas grandes?', 'El acero inoxidable es la mejor opci\u00f3n. El acero con recubrimiento en polvo es una alternativa m\u00e1s econ\u00f3mica, pero puede desgastarse con el tiempo si el ave muerde las barras.'),
        ]
    },
    {
        'slug': 'para-palomas',
        'title': 'Jaulas para Palomas: Transporte, Crianza y Cuidado',
        'excerpt': 'Jaulas para palomas y aves de corral: modelos de transporte, crianza y palomares dom\u00e9sticos. Estructuras resistentes y ventiladas para el bienestar de tus aves.',
        'intro': 'Las palomas requieren jaulas con caracter\u00edsticas espec\u00edficas: suficiente espacio para aletear, perchas anchas para descansar y una ventilaci\u00f3n adecuada. Ya sea para transporte, cr\u00eda o como hogar permanente, estas jaulas est\u00e1n dise\u00f1adas para satisfacer las necesidades de las palomas y aves similares.',
        'features_note': 'Para jaulas de palomas, la ventilaci\u00f3n y la facilidad de limpieza son tan importantes como la seguridad:',
        'faqs': [
            ('\u00bfQu\u00e9 tama\u00f1o de jaula necesito para una paloma?', 'M\u00ednimo 50x40x40 cm para una paloma dom\u00e9stica. Si es para crianza, el palomar debe ser a\u00fan m\u00e1s grande, con compartimentos separados para nidificaci\u00f3n.'),
            ('\u00bfLas jaulas para palomas necesitan nido?', 'Si planeas criar palomas, s\u00ed. Las palomas necesitan una base c\u00f3ncava o nido donde poner sus huevos. Algunas jaulas incluyen bandejas de nidificaci\u00f3n removibles.'),
            ('\u00bfC\u00f3mo transportar palomas de forma segura?', 'Usa transportadores ventilados con perchas antideslizantes. Los modelos plegables son pr\u00e1cticos para viajes. Aseg\u00farate de que la puerta tenga cierre doble para evitar fugas.'),
            ('\u00bfLas palomas pueden vivir al aire libre en una jaula?', 'S\u00ed, pero la jaula debe tener protecci\u00f3n contra lluvia y viento directo. Las jaulas de exterior para palomas deben tener techo impermeable y buena ventilaci\u00f3n cruzada.'),
        ]
    },
    {
        'slug': 'modelos',
        'title': 'Modelos de Jaulas para Aves: Ferplast, IMAC, Voltrega y M\u00e1s',
        'excerpt': 'Comparativa de los mejores modelos y marcas de jaulas para aves: Ferplast, IMAC, Voltrega, Kuandarm y otras. Encuentra el modelo ideal para tu mascota.',
        'intro': 'El mercado ofrece una gran variedad de modelos y marcas de jaulas para aves, cada una con caracter\u00edsticas \u00fanicas. En esta gu\u00eda reunimos los modelos m\u00e1s populares de fabricantes como Ferplast, IMAC, Voltrega, Kuandarm y otras marcas reconocidas, para que puedas comparar y elegir la que mejor se adapte a tu ave y tu presupuesto.',
        'features_note': 'Al comparar modelos de distintas marcas, estos aspectos te ayudar\u00e1n a tomar la mejor decisi\u00f3n:',
        'faqs': [
            ('\u00bfQu\u00e9 marca de jaulas para aves es la mejor?', 'No hay una \u00fanica mejor marca, depende de tus necesidades. Ferplast destaca por su relaci\u00f3n calidad-precio, IMAC por sus dise\u00f1os cl\u00e1sicos, Voltrega por su resistencia y Kuandarm por sus modelos decorativos. Revisa nuestra comparativa para elegir seg\u00fan tu presupuesto y tipo de ave.'),
            ('\u00bfLas jaulas Ferplast son recomendables para principiantes?', 'S\u00ed, Ferplast ofrece modelos accesibles y funcionales, ideales para quienes se inician en el cuidado de aves. Sus jaulas suelen incluir accesorios b\u00e1sicos como perchas y comederos.'),
            ('\u00bfCu\u00e1l es la diferencia entre una jaula IMAC y una Voltrega?', 'IMAC se especializa en dise\u00f1os cl\u00e1sicos y elegantes, mientras que Voltrega prioriza la robustez y funcionalidad. Las jaulas Voltrega suelen tener barrotes m\u00e1s gruesos, ideales para aves grandes.'),
            ('\u00bfC\u00f3mo elegir entre tantos modelos disponibles?', 'Define primero el tipo de ave, el presupuesto y el espacio disponible. Luego filtra por caracter\u00edsticas como material, tama\u00f1o, tipo de cierre y accesorios incluidos. Nuestra gu\u00eda principal de jaulas te ayudar\u00e1 a orientarte.'),
        ]
    },
]

# ─── Create sub-pillar pages ───
created_pages = {}
for sp in SUBPAGES:
    # Build characteristics table
    features_html = f'<figure class="wp-block-table"><table><tbody>{FEATURES_TABLE_ROWS}</tbody></table></figure>'
    
    faq_schema = make_faq_schema(sp['faqs'])
    
    faq_html = f'{faq_schema}\n<h2 class="wp-block-heading">Preguntas frecuentes sobre {sp["title"].split(":")[0].lower()}</h2>\n'
    for i, (q, a) in enumerate(sp['faqs']):
        faq_html += f'<p><strong>{q}</strong><br>{a}</p>\n'
    
    content = f'''<!-- wp:paragraph -->
<p>{sp["intro"]}</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Caracter\u00edsticas clave de {sp["title"].split(":")[0].lower()}</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>{sp["features_note"]}</p>
<!-- /wp:paragraph -->

<!-- wp:table -->
{features_html}
<!-- /wp:table -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Mejores modelos y productos de {sp["title"].split(":")[0].lower()}</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Hemos seleccionado los mejores productos de {sp["title"].split(":")[0].lower()} disponibles. Cada enlace te lleva a la ficha completa del producto con sus caracter\u00edsticas, ventajas y valoraciones. Simplemente elige el que mejor se adapte a tus necesidades.</p>
<!-- /wp:paragraph -->

<!-- wp:asap/cluster {{"display":"category","display_setting":[163],"edit_mode":false}} /-->

<!-- wp:heading -->
{faq_html}
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><strong>\u00bfBuscas otro tipo de jaula?</strong> Visita nuestra <a href="{PILLAR_URL}">Gu\u00eda Principal de Jaulas para Aves</a> donde encontrar\u00e1s todos los tipos: port\u00e1tiles, para loros, accesorios, modelos decorativos, aviarios y mucho m\u00e1s.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {{"width":100,"className":"cta"}} -->
<div class="wp-block-button has-custom-width wp-block-button__width-100 cta"><a class="wp-block-button__link wp-element-button" href="https://amzn.to/3TdvvIt" target="_blank" rel="noreferrer noopener nofollow">\ud83d\udc49\ud83c\udffb Ver ofertas de jaulas en Amazon</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->'''

    page_data = {
        'title': sp['title'],
        'content': content,
        'slug': sp['slug'],
        'parent': PILLAR_ID,
        'status': 'publish',
        'excerpt': sp['excerpt'],
        'meta': {'_yoast_wpseo_title': sp['title']}
    }
    
    try:
        # Check if page already exists
        existing = api('GET', f'/pages?slug={sp["slug"]}&parent={PILLAR_ID}&_fields=id')
        if existing:
            pid = existing[0]['id']
            api('PUT', f'/pages/{pid}', page_data)
            created_pages[sp['slug']] = pid
            print(f'UPDATED page /jaulas/{sp["slug"]}/ (ID {pid})')
        else:
            result = api('POST', '/pages', page_data)
            created_pages[sp['slug']] = result['id']
            print(f'CREATED page /jaulas/{sp["slug"]}/ (ID {result["id"]})')
    except Exception as e:
        print(f'ERROR creating {sp["slug"]}: {e}')

print(f'\nSub-pillar page IDs: {json.dumps(created_pages)}')

# ─── Update main pillar with silo navigation ───
# Read current content
main = api('GET', f'/pages/{PILLAR_ID}?context=edit')
main_raw = main.get('content', {}).get('raw', '')

# Build silo nav section
silo_links = []
for sp in SUBPAGES:
    silo_links.append(
        f'<li><a href="{PILLAR_URL}{sp["slug"]}/"><strong>{sp["title"].split(":")[0]}</strong></a> — {sp["excerpt"][:80]}...</li>'
    )

silo_section = f'''<!-- wp:heading -->
<h2 class="wp-block-heading">Tipos de jaulas para aves</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Explora nuestras gu\u00edas especializadas por tipo de jaula. Cada una incluye recomendaciones, comparativas y los mejores modelos del mercado.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>{"".join(silo_links)}</ul>
<!-- /wp:list -->

<!-- wp:separator -->
<hr class="wp-block-separator has-alpha-channel-opacity">
<!-- /wp:separator -->'''

# Insert after the first heading (replace the cluster block area)
# Find the cluster block and insert the silo nav after it
old = '<!-- wp:asap/cluster {"display":"category","display_setting":[163],"orderby":"title_asc","edit_mode":false} /-->'
if old in main_raw:
    new_main_raw = main_raw.replace(old, old + '\n\n' + silo_section)
    api('PUT', f'/pages/{PILLAR_ID}', {'content': new_main_raw})
    print(f'Updated main pillar /jaulas/ with silo navigation')
else:
    print(f'WARNING: Could not find cluster block in main pillar')
    # Append before the last heading
    # As fallback, append at end
    new_main_raw = main_raw.rstrip() + '\n\n' + silo_section
    api('PUT', f'/pages/{PILLAR_ID}', {'content': new_main_raw})
    print(f'Appended silo navigation to main pillar')

# ─── Update each post with link to its sub-pillar ───
# Build group mapping
slug_to_group = {}
for p in all_posts:
    raw = p.get('content', {}).get('raw', '')
    g = classify_post(p['slug'], p['title']['rendered'], raw)
    slug_to_group[p['id']] = g

# For each group, add a contextual link paragraph before the end of each post
group_page_ids = created_pages  # slug -> page_id
group_urls = {slug: f'{PILLAR_URL}{slug}/' for slug in group_page_ids}

updated_count = 0
for p in all_posts:
    pid = p['id']
    g = slug_to_group[pid]
    if g not in group_urls:
        continue
    sub_url = group_urls[g]
    
    # Get fresh content
    post_data = api('GET', f'/posts/{pid}?context=edit')
    raw = post_data.get('content', {}).get('raw', '')
    
    # Check if already has the link
    if sub_url in raw:
        continue
    
    # Add link paragraph before the last block
    # Find Amazon affiliate block or last paragraph
    anchor_texts = {
        'portatiles': [
            'jaulas port\u00e1tiles para aves',
            'jaulas de viaje plegables',
            'transportadores para aves',
        ],
        'para-loros': [
            'jaulas para loros recomendadas',
            'jaulas para cacat\u00faas y conures',
            'mejores jaulas para loros',
        ],
        'accesorios': [
            'accesorios para jaulas de aves',
            'fundas y cubiertas para jaulas',
            'bandejas y rejillas para jaulas',
        ],
        'para-pajaros-pequenos': [
            'jaulas para canarios y periquitos',
            'jaulas para p\u00e1jaros peque\u00f1os',
        ],
        'decorativas': [
            'jaulas decorativas y vintage',
            'jaulas de madera para aves',
        ],
        'grandes': [
            'jaulas grandes y aviarios',
            'jaulones espaciosos para aves',
        ],
        'para-palomas': [
            'jaulas para palomas',
            'transportadores para palomas',
        ],
        'modelos': [
            'modelos de jaulas para aves',
            'comparativa de marcas de jaulas',
        ],
    }
    
    import random
    texts = anchor_texts.get(g, ['gu\u00eda de ' + g.replace('-', ' ') + ' para aves'])
    anchor = random.choice(texts)
    
    # Build the paragraph with the link
    link_p = f'<p>\ud83d\udc49 Si quieres ver m\u00e1s opciones y comparar caracter\u00edsticas, visita nuestra <a href="{sub_url}">{anchor}</a>.</p>'
    
    # Insert before the last </div> or at the end
    # Try to find the last Amazon bloc or cluster and insert before
    # Simple approach: insert before the last </p> in the content
    # More robust: find last occurrence of </p> and insert before it
    last_p = raw.rfind('</p>')
    if last_p > 0:
        new_raw = raw[:last_p] + '</p>\n\n' + link_p + raw[last_p+4:]
    else:
        new_raw = raw.rstrip() + '\n\n' + link_p
    
    api('PUT', f'/posts/{pid}', {'content': new_raw})
    updated_count += 1
    if updated_count % 20 == 0:
        print(f'Updated {updated_count} posts...')

print(f'\nTotal posts updated with sub-pillar links: {updated_count}')
print('\n=== SILO COMPLETE ===')
print(f'Main pillar: /jaulas/ (ID {PILLAR_ID})')
for sp in SUBPAGES:
    pid = created_pages.get(sp['slug'])
    count = len(groups.get(sp['slug'], []))
    print(f'  /jaulas/{sp["slug"]}/ (ID {pid}) — {count} posts linked')
