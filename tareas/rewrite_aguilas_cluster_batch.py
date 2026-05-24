import json
import requests

AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'


def update_post(post_id, title, content, excerpt=None, kind='posts'):
    payload = {'title': title, 'content': content}
    if excerpt is not None:
        payload['excerpt'] = excerpt
    r = requests.post(BASE + f'{kind}/{post_id}', auth=AUTH, json=payload, timeout=30)
    return r.status_code, r.text


def faq_schema(faqs):
    items = []
    for q, a in faqs:
        items.append({'@type': 'Question', 'name': q, 'acceptedAnswer': {'@type': 'Answer', 'text': a}})
    return '<script type="application/ld+json">' + json.dumps({'@context': 'https://schema.org', '@type': 'FAQPage', 'mainEntity': items}, ensure_ascii=False) + '</script>'


def build_content(item):
    parts = []
    if item.get('banner'):
        parts.append(item['banner'])
    parts.append(f"<p>{item['intro']}</p>")
    if item.get('lead_links'):
        parts.append('<p>' + ' · '.join(item['lead_links']) + '</p>')
    for section_title, body in item.get('sections', []):
        parts.append(f'<h2>{section_title}</h2>')
        parts.append(body)
    if item.get('related'):
        parts.append('<h2>Lecturas relacionadas</h2>')
        parts.append('<ul>' + ''.join(f'<li>{x}</li>' for x in item['related']) + '</ul>')
    if item.get('faqs'):
        parts.append('<h2>Preguntas frecuentes</h2>')
        for q, a in item['faqs']:
            parts.append(f'<h3>{q}</h3>')
            parts.append(f'<p>{a}</p>')
        parts.append(faq_schema(item['faqs']))
    return '\n\n'.join(parts)


ITEMS = [
    {
        'id': 15115,
        'kind': 'pages',
        'title': 'Aguilas en Chile: especies, habitat y donde verlas [Guia 2026]',
        'excerpt': 'Guia actualizada de aguilas en Chile: especies mas buscadas, habitat, diferencias con otras rapaces y donde observarlas sin molestarlas.',
        'banner': '<div class="actualizacion-2026" style="background:#fffde7;padding:10px 15px;border-radius:6px;margin-bottom:20px;border-left:4px solid #fbc02d;font-size:0.95em;"><strong>Actualizado: Mayo 2026</strong> - Esta guia ordena las especies de aguilas que la gente busca en Chile y separa las rapaces que suelen confundirse con ellas.</div>',
        'intro': 'En Chile, cuando alguien busca aguilas, a veces piensa en una especie concreta y otras en cualquier rapaz grande que planea sobre la cordillera. Esta pagina ordena ese panorama: cuales si aparecen en Chile, donde verlas y como distinguirlas sin confundirte con otras aves rapaces.',
        'lead_links': [
            '<a href="https://avesnativaschilenas.cl/rapaces/">Guia principal de rapaces chilenas</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/aguila-peregrina/">Aguila peregrina</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/aguila-crestada/">Aguila crestada</a>',
        ],
        'sections': [
            ('Que aguilas se ven realmente en Chile', '<p>La referencia principal es el <strong>aguila mora</strong>, muy ligada a la cordillera y a los espacios abiertos. En humedales y costa puede aparecer el <strong>aguila pescadora</strong>, sobre todo como visitante estacional.</p><table><thead><tr><th>Nombre</th><th>Presencia</th><th>Donde verla</th><th>Clave rapida</th></tr></thead><tbody><tr><td>Aguila mora</td><td>Si</td><td>Cordillera y valles abiertos</td><td>Grande y planeadora</td></tr><tr><td>Aguila pescadora</td><td>Si, estacional</td><td>Humedales y costa</td><td>Especialista en peces</td></tr><tr><td>Aguila arpia</td><td>No comun en Chile</td><td>Lectura relacionada</td><td>Gigante tropical</td></tr><tr><td>Aguila crestada</td><td>No comun en Chile</td><td>Lectura relacionada</td><td>Rapaz montana</td></tr></tbody></table>'),
            ('Donde ver aguilas en Chile', '<p>Los mejores lugares son la cordillera, la precordillera, laderas abiertas, quebradas y humedales amplios. La mejor hora suele ser cuando hay corrientes termicas, a mitad del dia y primeras horas de la tarde.</p>'),
            ('Como distinguir una aguila de otras rapaces', '<p>La silueta, el habitat y el tipo de vuelo ayudan mas que el tamano. El peuco y el aguilucho se confunden a menudo con aguilas, pero tienen proporciones y maniobras distintas.</p>'),
            ('Conservacion y amenazas', '<p>Las amenazas mas claras son perdida de habitat, persecucion humana, electrocucion, envenenamiento secundario y reduccion de presas. La mejor ayuda es observarlas con distancia y proteger sus ambientes.</p>'),
        ],
        'related': [
            '<a href="https://avesnativaschilenas.cl/aguilas/cuanto-mide-un-aguila-real-con-las-alas-abiertas/">Cuanto mide un aguila real con las alas abiertas</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/el-aguila-que-tipo-de-consumidor-es/">Que tipo de consumidor es el aguila</a>',
            '<a href="https://avesnativaschilenas.cl/rapaces/principales-amenazas-para-las-aves-rapaces-en-chile/">Principales amenazas de las aves rapaces</a>',
        ],
        'faqs': [
            ('Cual es el aguila mas comun en Chile?', 'La mas facil de asociar es el aguila mora, sobre todo en cordillera y paisajes abiertos.'),
            ('Donde se pueden ver aguilas en Chile?', 'En cerros, valles abiertos, laderas andinas, humedales costeros y areas rurales amplias.'),
            ('El peuco es un aguila?', 'No. Es una rapaz distinta, aunque mucha gente la confunde con una aguila.'),
            ('Se puede usar un aguila para ahuyentar palomas?', 'No como metodo de control urbano. La mejor solucion sigue siendo manejo del alimento y barreras fisicas.'),
            ('Que hago si encuentro un aguila herida?', 'No la manipules mas de lo necesario y contacta al SAG o a un centro de rescate.'),
        ],
    },
    {
        'id': 1853,
        'title': 'Aguila peregrina: caracteristicas, habitat y curiosidades',
        'excerpt': 'Guia completa del aguila peregrina en Chile: como reconocerla, donde vive, que come y por que es tan rapida.',
        'intro': 'El aguila peregrina es una de las rapaces mas admiradas por su potencia, velocidad y silueta robusta. Aunque muchas personas la llaman aguila por costumbre, su valor en el paisaje chileno es claro: domina el cielo con vuelos precisos y una gran capacidad de adaptacion.',
        'lead_links': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Volver al hub de aguilas</a>',
            '<a href="https://avesnativaschilenas.cl/rapaces/">Ver todas las rapaces</a>',
        ],
        'sections': [
            ('Como reconocerla', '<p>Destaca por su cuerpo compacto, alas largas y vuelo veloz. Su perfil es claramente de rapaz cazadora, con cabeza firme, pecho potente y maniobras rapidas cuando detecta presas.</p>'),
            ('Donde vive', '<p>Prefiere ambientes abiertos, acantilados, laderas y sectores con buena visibilidad. Puede moverse entre cordillera, costa y zonas rurales donde encuentre presas y sitios de reposo seguros.</p>'),
            ('Alimentacion y caza', '<p>Se alimenta de aves pequenas y medianas, ademas de otros vertebrados. Su estrategia es la persecucion a gran velocidad y el ataque preciso, lo que la convierte en una cazadora muy eficiente.</p>'),
            ('Curiosidades y conservacion', '<p>Es una especie que inspira respeto por su vuelo y por la precision de su caza. La proteccion de su habitat y la observacion sin acercamiento excesivo son claves para mantenerla visible en el tiempo.</p>'),
        ],
        'related': [
            '<a href="https://avesnativaschilenas.cl/aguilas/cuanto-mide-un-aguila-con-las-alas-abiertas/">Cuanto mide un aguila con las alas abiertas</a>',
            '<a href="https://avesnativaschilenas.cl/rapaces/">Aves rapaces chilenas</a>',
        ],
        'faqs': [
            ('La aguila peregrina vive en Chile?', 'Si, puede verse en distintos paisajes abiertos y costeros del pais.'),
            ('Que diferencia tiene con otras aguilas?', 'Su vuelo es mas rapido y su silueta esta muy orientada a la caza aerea.'),
            ('Es dificil verla?', 'Depende de la zona, pero suele aparecer en espacios abiertos con buena visibilidad.'),
            ('Que come principalmente?', 'Aves pequenas y medianas, ademas de otros vertebrados.'),
        ],
    },
    {
        'id': 1849,
        'title': 'Aguila arpia en los bosques: tamano imponente, habitat y papel ecologico',
        'excerpt': 'Todo sobre el aguila arpia: tamano, habitat tropical, comportamiento y por que es una de las rapaces mas impresionantes de America.',
        'intro': 'El aguila arpia no es una especie comun en Chile, pero aparece en el sitio porque mucha gente la busca al explorar grandes rapaces americanas. Es uno de los depredadores aereos mas imponentes del continente, famosa por su fuerza, su aspecto y su relacion con bosques tropicales.',
        'lead_links': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Volver al hub de aguilas</a>',
            '<a href="https://avesnativaschilenas.cl/rapaces/">Guia de rapaces</a>',
        ],
        'sections': [
            ('Como es y por que impresiona', '<p>Su enorme envergadura, patas poderosas y cabeza llamativa hacen que destaque entre las rapaces americanas. Su aspecto transmite fuerza, pero su conducta es la de una cazadora silenciosa y muy especializada.</p>'),
            ('Donde vive', '<p>Su mundo natural son los bosques tropicales de America del Sur y Centroamerica. Necesita arboles altos, presas disponibles y una estructura forestal que le permita moverse sin perder cobertura.</p>'),
            ('Rol ecologico', '<p>Actua como depredador tope, regulando poblaciones de mamiferos y aves medianas. Su presencia es una buena senal del estado de conservacion del bosque.</p>'),
            ('Por que interesa en Chile', '<p>Porque suele aparecer en busquedas comparativas junto a aguilas chilenas y rapaces grandes. Esta pagina sirve para diferenciarla de especies locales y evitar confusiones.</p>'),
        ],
        'related': [
            '<a href="https://avesnativaschilenas.cl/aguilas/aguila-crestada/">Aguila crestada</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/">Aguilas en Chile</a>',
        ],
        'faqs': [
            ('El aguila arpia vive en Chile?', 'No como especie comun. Se busca mas como referencia de gran rapaz americana.'),
            ('Por que se la considera tan grande?', 'Porque tiene un cuerpo muy robusto y extremidades adaptadas a capturar presas grandes.'),
            ('Que habitat necesita?', 'Bosques tropicales con arboles altos y presas disponibles.'),
            ('Se parece a las aguilas chilenas?', 'En tamano puede impresionar, pero su habitat y biologia son distintos.'),
        ],
    },
    {
        'id': 1621,
        'title': 'Aguila crestada: caracteristicas, habitat y curiosidades',
        'excerpt': 'Guia del aguila crestada: como identificarla, donde habita y que la diferencia de otras rapaces de Chile.',
        'intro': 'El aguila crestada es una de las rapaces mas llamativas que la gente asocia con montana, valles y sectores abiertos. Su silueta elegante y su vuelo alto la vuelven una especie clave para quienes observan rapaces en Chile.',
        'lead_links': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Volver al hub de aguilas</a>',
            '<a href="https://avesnativaschilenas.cl/rapaces/">Rapaces chilenas</a>',
        ],
        'sections': [
            ('Como reconocerla', '<p>Su crestita y su silueta clara ayudan a distinguirla. Suele planear con alas amplias y mostrar una presencia muy marcada cuando patrulla sectores abiertos.</p>'),
            ('Habitat', '<p>Frecuenta montanas, laderas, quebradas y ambientes abiertos donde pueda usar el relieve y las corrientes de aire. No suele depender de ciudades densas.</p>'),
            ('Alimentacion', '<p>Se alimenta de presas pequenas y medianas, segun disponibilidad local. Como otras rapaces, cumple una funcion de control natural en los ecosistemas.</p>'),
            ('Curiosidades', '<p>Es de las especies que mejor sirve para explicar por que no toda rapaz grande es un aguila mora. Su perfil ayuda a comparar rapaces similares y a reconocer variaciones regionales.</p>'),
        ],
        'related': [
            '<a href="https://avesnativaschilenas.cl/aguilas/peuco/">Peuco chileno</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/cuanto-mide-un-aguila-con-las-alas-abiertas/">Cuanto mide un aguila con las alas abiertas</a>',
        ],
        'faqs': [
            ('Donde vive el aguila crestada?', 'En montanas, laderas y ambientes abiertos con buena corriente de aire.'),
            ('Es comun verla en ciudades?', 'No, prefiere espacios abiertos y menos perturbados.'),
            ('Que come?', 'Pequenas presas vertebradas y otras especies disponibles en su entorno.'),
            ('Se confunde con el peuco?', 'Si, a veces por tamano y vuelo, pero no son la misma especie.'),
        ],
    },
    {
        'id': 178,
        'title': 'Peuco chileno: caracteristicas, habitat y comportamiento de un ave protegida',
        'excerpt': 'Guia del peuco chileno: como reconocerlo, donde vive, que come y por que se confunde tanto con las aguilas.',
        'intro': 'El peuco es una rapaz muy buscada por lectores que llegan con la palabra aguila en mente, aunque biologicamente no sea una aguila. Esta pagina ayuda a diferenciarlo bien y a entender su papel en el paisaje chileno.',
        'lead_links': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Volver al hub de aguilas</a>',
            '<a href="https://avesnativaschilenas.cl/rapaces/">Ver rapaces chilenas</a>',
        ],
        'sections': [
            ('Como reconocerlo', '<p>Es mas compacto que una aguila y suele moverse con rapidez entre sectores abiertos y arbolados. Su vuelo es maniobrable y su presencia es comun en zonas rurales o semiabiertas.</p>'),
            ('Habitat y comportamiento', '<p>Usa campos, bordes de bosque, cerros y sectores con arboles dispersos. Es oportunista y muy observador, lo que le permite aprovechar presas pequenas y medianas.</p>'),
            ('Alimentacion', '<p>Su dieta incluye reptiles pequenos, aves, roedores e insectos grandes. Por eso es un buen controlador natural de presas frecuentes en zonas rurales.</p>'),
            ('Diferencia con aguilas', '<p>La diferencia mas simple es la forma del cuerpo y la forma de volar. El peuco es mas ligero y maniobrable, mientras una aguila suele mostrar un vuelo mas pesado y planeado.</p>'),
        ],
        'related': [
            '<a href="https://avesnativaschilenas.cl/aguilas/aguila-crestada/">Aguila crestada</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/">Aguilas en Chile</a>',
        ],
        'faqs': [
            ('El peuco es un aguila?', 'No, es otra rapaz, aunque mucha gente lo confunde con una aguila.'),
            ('Donde vive?', 'En espacios abiertos, bordes de bosque y zonas rurales o semiurbanas.'),
            ('Que come?', 'Roedores, reptiles, aves pequenas e insectos grandes.'),
            ('Por que es importante?', 'Porque ayuda al control natural de presas y mantiene equilibrio ecologico.'),
        ],
    },
    {
        'id': 216,
        'title': 'Tiuque: caracteristicas, habitat y curiosidades',
        'excerpt': 'Guia del tiuque chileno: comportamiento, habitat, dieta y por que aparece tanto cerca de personas.',
        'intro': 'El tiuque es una de las rapaces mas cercanas a la vida humana en Chile. Aunque no es una aguila, aparece una y otra vez en las busquedas de aves rapaces porque comparte espacios abiertos y urbanos con facilidad.',
        'lead_links': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Volver al hub de aguilas</a>',
            '<a href="https://avesnativaschilenas.cl/rapaces/">Guia de rapaces</a>',
        ],
        'sections': [
            ('Como reconocerlo', '<p>Presenta silueta mediana, vuelo activo y conducta oportunista. Se adapta muy bien a campos, plazas, bordes urbanos y espacios con comida disponible.</p>'),
            ('Habitat y dieta', '<p>Vive en paisajes abiertos, zonas rurales y areas urbanas con arboles o postes donde posarse. Come de todo un poco: insectos, carroña, restos y pequenos vertebrados.</p>'),
            ('Por que se ve tanto en ciudades', '<p>Porque encuentra comida y sitios para posarse. Su exito urbano lo vuelve visible, aunque muchas personas lo confundan con un aguila joven o un aguilucho.</p>'),
            ('Rol ecologico', '<p>Ayuda a limpiar restos organicos y a controlar ciertas poblaciones pequeñas. Su presencia es un indicador util de ambientes abiertos y de la convivencia con humanos.</p>'),
        ],
        'related': [
            '<a href="https://avesnativaschilenas.cl/aguilas/peuco/">Peuco chileno</a>',
            '<a href="https://avesnativaschilenas.cl/rapaces/">Aves rapaces chilenas</a>',
        ],
        'faqs': [
            ('El tiuque es un aguila?', 'No. Es una rapaz distinta y muy adaptable.'),
            ('Donde vive?', 'En campos, bordes urbanos, plazas y zonas rurales.'),
            ('Que come?', 'Carroña, insectos, restos y pequenos vertebrados.'),
            ('Por que aparece tanto en ciudades?', 'Porque aprovecha comida y posaderos faciles.'),
        ],
    },
    {
        'id': 664,
        'title': 'El Traro Chileno: una maravilla de la naturaleza',
        'excerpt': 'Conoce al traro chileno: identificacion, comportamiento, habitat y por que se relaciona con la cultura local.',
        'intro': 'El traro chileno es una de esas aves que despiertan curiosidad por su presencia, su simbolismo y la forma en que se integra al paisaje del sur de Chile. Esta version deja de lado el relleno y se centra en su biologia real y en su valor ecologico.',
        'lead_links': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Volver al hub de aguilas</a>',
            '<a href="https://avesnativaschilenas.cl/rapaces/">Rapaces chilenas</a>',
        ],
        'sections': [
            ('Como es el traro', '<p>Es una rapaz de presencia fuerte, asociada a espacios abiertos y a zonas donde pueda cazar o carroñear con facilidad. Su perfil lo hace facilmente reconocible dentro de las aves rapaces del sur.</p>'),
            ('Habitat', '<p>Se mueve entre campos, bordes de bosque, praderas y sectores semiabiertos. Le favorecen los paisajes amplios con buen acceso a alimento y poca presion humana.</p>'),
            ('Alimentacion y conducta', '<p>Puede aprovechar pequeños vertebrados, insectos grandes y carroña segun disponibilidad. Esa flexibilidad explica por que aparece con tanta frecuencia en paisajes modificados por personas.</p>'),
            ('Valor ecologico', '<p>Como otras rapaces, ayuda a mantener el equilibrio de presas y a limpiar restos organicos. Es parte de la red natural que sostiene el ecosistema.</p>'),
        ],
        'related': [
            '<a href="https://avesnativaschilenas.cl/aguilas/traro/el-traro-chileno-tesoro-cultural-y-simbolo-nacional/">Traro chileno y simbolo nacional</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/">Aguilas en Chile</a>',
        ],
        'faqs': [
            ('El traro es una aguila?', 'No exactamente. Es una rapaz distinta que suele entrar al mismo grupo de busqueda.'),
            ('Donde vive?', 'En campos, praderas y bordes de bosque del sur de Chile.'),
            ('Que come?', 'Pequenos vertebrados, insectos grandes y carroña.'),
            ('Por que aparece en el sitio?', 'Porque mucha gente busca rapaces grandes y lo confunde con aguilas.'),
        ],
    },
    {
        'id': 2752,
        'title': 'El Traro Chileno: tesoro cultural y simbolo nacional',
        'excerpt': 'Historia, simbolismo y observacion del traro chileno, con enfoque cultural y ecologico.',
        'intro': 'Esta pagina complementa la guia naturalista del traro con una mirada cultural. El objetivo no es romantizar sin base, sino explicar por que esta rapaz aparece en relatos, nombres locales y referencias simbolicas del territorio.',
        'lead_links': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Volver al hub de aguilas</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/traro/">Ver guia biologica del traro</a>',
        ],
        'sections': [
            ('Nombre y simbolo', '<p>El traro forma parte del imaginario rural del sur de Chile. Su presencia en el paisaje y su conducta lo convirtieron en una especie conocida por varias generaciones.</p>'),
            ('Relacion con el territorio', '<p>En zonas abiertas y rurales, el traro se asocia con vigilancia, movimiento y adaptacion. Por eso aparece en relatos locales y en conversaciones sobre fauna cercana al hombre.</p>'),
            ('Como observarlo', '<p>Conviene buscarlo en campos, bordes de bosque y sectores amplios. La observacion responsable siempre vale mas que acercarse de mas o perseguirlo para fotografiarlo.</p>'),
            ('Por que se confunde con otras rapaces', '<p>Porque comparte siluetas generales con otras aves medianas o grandes. La clave esta en mirar la cabeza, el vuelo y el contexto del habitat.</p>'),
        ],
        'related': [
            '<a href="https://avesnativaschilenas.cl/aguilas/traro/">Guia del traro chileno</a>',
            '<a href="https://avesnativaschilenas.cl/rapaces/">Aves rapaces chilenas</a>',
        ],
        'faqs': [
            ('El traro tiene valor cultural?', 'Si, sobre todo como ave reconocible del paisaje rural chileno.'),
            ('Es un aguila?', 'No. Es una rapaz distinta, aunque se relaciona con el mismo grupo de busqueda.'),
            ('Donde verlo?', 'En campos, bordes de bosque y zonas abiertas del sur.'),
            ('Por que es importante?', 'Porque conecta biodiversidad, territorio y memoria local.'),
        ],
    },
    {
        'id': 1857,
        'title': 'El Aguila en la Biblia: significado espiritual y versiculos clave',
        'excerpt': 'Interpretacion biblica del aguila con enfoque claro: simbolismo, pasajes clave y su relacion con el ave real.',
        'intro': 'El aguila tiene un simbolismo muy fuerte en la Biblia, pero aqui conviene unir la lectura espiritual con la observacion natural. Eso evita repetir frases vacias y ayuda a entender por que esta ave sigue siendo tan poderosa en el imaginario religioso.',
        'lead_links': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Volver al hub de aguilas</a>',
            '<a href="https://avesnativaschilenas.cl/rapaces/">Ver rapaces chilenas</a>',
        ],
        'sections': [
            ('Simbolismo principal', '<p>En la Biblia, el aguila representa elevacion, renovacion, proteccion y, en algunos pasajes, juicio. Esa amplitud simbolica explica por que aparece tanto en textos, sermones y reflexiones cristianas.</p>'),
            ('Versiculos clave', '<p>Se suele relacionar con pasajes de renovacion de fuerzas, proteccion sobre los suyos y vuelo alto. Lo valioso aqui es leer el simbolo sin perder el contexto del texto original.</p>'),
            ('Lo que aporta la biologia', '<p>El comportamiento real del aguila, su vigilancia y su vuelo planeado ayudan a entender por que la imagen biblica funciona tan bien. No es magia: es una mezcla de observacion y lenguaje simbolico.</p>'),
            ('Aplicacion actual', '<p>La figura del aguila sigue usandose para hablar de resiliencia, distancia saludable y perspectiva. En un sitio de aves, esa lectura sirve si no se separa del ave real.</p>'),
        ],
        'related': [
            '<a href="https://avesnativaschilenas.cl/aguilas/el-vuelo-del-aguila-y-su-simbolismo-en-otras-culturas/">Simbolismo en otras culturas</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/">Aguilas en Chile</a>',
        ],
        'faqs': [
            ('Que simboliza el aguila en la Biblia?', 'Proteccion, renovacion, elevacion y tambien juicio segun el pasaje.'),
            ('Es solo un simbolo religioso?', 'No. Tambien es un ave real con comportamiento muy potente para explicar esa imagen.'),
            ('Por que aparece tanto?', 'Porque su vuelo y su vigilancia fueron faciles de convertir en metafora.'),
            ('Se puede interpretar sin exagerar?', 'Si, si se lee el texto en contexto y sin separar el simbolo de la naturaleza.'),
        ],
    },
    {
        'id': 9978,
        'title': 'Que significa sonar con un aguila: interpretacion y simbolismo',
        'excerpt': 'Que puede significar sonar con un aguila segun el simbolismo popular, la Biblia y la conducta real del ave.',
        'intro': 'Sonar con un aguila suele llamarse mensaje, presagio o reflexion espiritual. Esta version separa tres niveles: el simbolismo popular, la lectura biblica y la explicacion natural de por que esta ave impresiona tanto.',
        'lead_links': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Volver al hub de aguilas</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/caracteristicas-de-las-aguilas-segun-la-biblia/">Simbolismo biblico</a>',
        ],
        'sections': [
            ('Interpretacion simbolica', '<p>El aguila suele asociarse con fuerza, libertad, altura y vision amplia. Por eso en los sueños aparece como signo de cambio, proteccion o necesidad de tomar distancia.</p>'),
            ('Lectura biblica', '<p>Si la lectura es religiosa, el aguila puede representar renovacion espiritual, cuidado o elevacion. No es una formula exacta: depende del contexto personal y del texto de referencia.</p>'),
            ('Explicacion natural', '<p>El cerebro humano reacciona a su vuelo alto, su mirada fija y su presencia dominante. Esa mezcla explica por que un aguila deja una huella emocional tan fuerte incluso en sueños.</p>'),
            ('Como usar la interpretacion', '<p>Lo mas util es tomar el simbolo como una invitacion a revisar perspectiva, metas y entorno, no como una sentencia cerrada.</p>'),
        ],
        'related': [
            '<a href="https://avesnativaschilenas.cl/aguilas/caracteristicas-de-las-aguilas-segun-la-biblia/">Aguila en la Biblia</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/">Aguilas en Chile</a>',
        ],
        'faqs': [
            ('Sonar con un aguila es bueno?', 'Suele interpretarse como un simbolo de fuerza, vision o renovacion.'),
            ('Siempre tiene sentido religioso?', 'No. A veces solo refleja admiracion o una preocupacion personal.'),
            ('Que significa ver un aguila volando en el sueño?', 'Puede asociarse con libertad, perspectiva y cambios de altura emocional.'),
            ('Debo tomarlo literal?', 'No. Conviene interpretarlo como simbolo y no como prediccion exacta.'),
        ],
    },
    {
        'id': 9512,
        'title': 'El vuelo del aguila y su simbolismo en otras culturas',
        'excerpt': 'Como distintas culturas interpretan al aguila: poder, realeza, vision y proteccion, con base natural.',
        'intro': 'El aguila ha sido un simbolo potente en muchas culturas porque combina altura, control del vuelo y presencia dominante. Esta pagina une ese simbolismo con la especie real y con su importancia como rapaz visible en muchos paisajes.',
        'lead_links': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Volver al hub de aguilas</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/caracteristicas-de-las-aguilas-segun-la-biblia/">Lectura biblica</a>',
        ],
        'sections': [
            ('Simbolismo en distintas tradiciones', '<p>En varias culturas, el aguila representa poder, liderazgo, proteccion y vision amplia. Su imagen se usa en escudos, relatos y emblemas por su capacidad de dominar el cielo.</p>'),
            ('Lo que aporta el vuelo real', '<p>Su vuelo planeado y su capacidad de elevarse con poco esfuerzo aparente explican por que tantas culturas la eligieron como emblema de altura y dominio.</p>'),
            ('Uso en Chile y America', '<p>En America, la figura del aguila aparece tanto en discursos identitarios como en referencias a la fauna local. Entender el simbolo ayuda a leer mejor los nombres populares de las rapaces.</p>'),
            ('Mirada responsable', '<p>El simbolo es util, pero no debe tapar la especie real. Cuando observas aguilas, lo importante es reconocer su biologia y su papel ecologico.</p>'),
        ],
        'related': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Aguilas en Chile</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/caracteristicas-de-las-aguilas-segun-la-biblia/">Aguila en la Biblia</a>',
        ],
        'faqs': [
            ('Por que el aguila simboliza poder?', 'Porque su vuelo, su vision y su presencia transmiten dominio y control.'),
            ('Todas las culturas la interpretan igual?', 'No. Cambia el contexto, pero suele asociarse a fuerza y altura.'),
            ('Tiene base en la observacion real?', 'Si, sobre todo en la forma en que vuela y vigila el territorio.'),
            ('Sirve para entender rapaces chilenas?', 'Si, porque ayuda a distinguir simbolo cultural de especie biologica.'),
        ],
    },
    {
        'id': 9510,
        'title': 'Reflexiones sobre el aguila y su rol en la vida cristiana',
        'excerpt': 'Reflexion cristiana sobre el aguila con enfoque mas claro: simbolo, naturaleza y aplicacion espiritual.',
        'intro': 'Esta pagina conecta fe y naturaleza sin caer en relleno. El aguila puede funcionar como imagen de renovacion, fortaleza y altura espiritual, pero esa lectura gana valor cuando se apoya en la observacion del ave real.',
        'lead_links': [
            '<a href="https://avesnativaschilenas.cl/aguilas/caracteristicas-de-las-aguilas-segun-la-biblia/">Aguila en la Biblia</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/">Aguilas en Chile</a>',
        ],
        'sections': [
            ('Renovacion y fortaleza', '<p>La imagen del aguila se usa para hablar de recuperar fuerzas, elevar la mirada y salir de etapas de desgaste. Esa lectura sigue vigente porque el simbolo es muy intuitivo.</p>'),
            ('Lo que enseña la naturaleza', '<p>Su forma de planear y de vigilar el entorno ayuda a entender por que se convirtió en un referente de claridad y perspectiva.</p>'),
            ('Aplicacion espiritual', '<p>En la vida cristiana, el aguila se puede leer como llamado a mantener distancia de lo que agota y a recuperar foco en lo esencial.</p>'),
            ('Balance con la ciencia', '<p>La reflexion funciona mejor cuando no reemplaza la biologia. El valor del simbolo aumenta si respetamos al ave como especie silvestre.</p>'),
        ],
        'related': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Hub de aguilas</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/el-vuelo-del-aguila-y-su-simbolismo-en-otras-culturas/">Simbolismo en otras culturas</a>',
        ],
        'faqs': [
            ('Que representa el aguila en la fe cristiana?', 'Renovacion, fortaleza, proteccion y altura espiritual.'),
            ('Se puede leer sin exagerar?', 'Si, si se mantiene el simbolo unido a su contexto y a la naturaleza.'),
            ('Es solo una metafora?', 'No, tambien es una observacion de un ave real muy impresionante.'),
            ('Que aporta al lector?', 'Una imagen clara para pensar en resiliencia y perspectiva.'),
        ],
    },
    {
        'id': 9507,
        'title': 'Renovacion espiritual segun las ensenanzas del aguila',
        'excerpt': 'Una lectura de renovacion espiritual usando la figura del aguila y su comportamiento real.',
        'intro': 'La renovacion espiritual suele explicarse con imagenes de altura, silencio y reposo. El aguila funciona bien en ese papel porque su comportamiento inspira esa idea de volver a elevarse con claridad.',
        'lead_links': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Volver al hub de aguilas</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/caracteristicas-de-las-aguilas-segun-la-biblia/">Aguila en la Biblia</a>',
        ],
        'sections': [
            ('Que enseña el simbolo', '<p>Habla de recuperar fuerzas, renovar mirada y tomar distancia de lo que agota. Es una imagen muy usada porque es facil de entender y recordar.</p>'),
            ('Lo que muestra el ave', '<p>El planeo, la vigilancia y la altura del vuelo ayudan a reforzar esa idea de perspectiva. No es solo inspiracion: es observacion de un animal real.</p>'),
            ('Como aplicar la idea', '<p>La renovacion no consiste en huir de todo, sino en elegir mejor el punto de vista y el entorno donde uno se mueve.</p>'),
            ('Vinculo con la naturaleza', '<p>Cuando el simbolo se usa con respeto a la especie, la reflexion gana fuerza y se vuelve mas util para el lector.</p>'),
        ],
        'related': [
            '<a href="https://avesnativaschilenas.cl/aguilas/caracteristicas-de-las-aguilas-segun-la-biblia/">Aguila en la Biblia</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/">Aguilas en Chile</a>',
        ],
        'faqs': [
            ('Que significa renovacion espiritual?', 'Recuperar fuerza, claridad y enfoque interior.'),
            ('Por que usar el aguila?', 'Porque simboliza altura, vigilancia y capacidad de volver a elevarse.'),
            ('Es compatible con una lectura natural?', 'Si, mientras no se separe el simbolo del ave real.'),
            ('Ayuda al SEO?', 'Si, cuando se combina simbolismo con contenido util y bien estructurado.'),
        ],
    },
    {
        'id': 9369,
        'title': 'Como el aguila ensena a volar a sus polluelos',
        'excerpt': 'Explicacion biologica y observacion de campo sobre como los polluelos de aguila aprenden a volar.',
        'intro': 'La idea de que el aguila ensena a volar a sus polluelos tiene un enorme poder narrativo. Aqui la llevamos a terreno biologico: como ocurre el aprendizaje, que cuidados hay y por que el proceso es tan importante.',
        'lead_links': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Volver al hub de aguilas</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/aguila-peregrina/">Ver aguila peregrina</a>',
        ],
        'sections': [
            ('El inicio del aprendizaje', '<p>Los polluelos nacen dependientes y pasan por una etapa de fortalecimiento muscular, equilibrio y coordinacion antes de despegar con seguridad.</p>'),
            ('El papel de los padres', '<p>Los adultos aportan alimento, vigilancia y estimulos. No es un curso de vuelo literal, sino una secuencia de apoyo, empuje y aprendizaje progresivo.</p>'),
            ('Primeros vuelos', '<p>Los intentos iniciales suelen ser torpes, cortos y muy ligados al entorno inmediato. Con practica, el control mejora y el joven gana autonomia.</p>'),
            ('Que nos enseña esta conducta', '<p>La escena sirve para hablar de desarrollo, paciencia y adaptacion. En las aves rapaces, aprender a volar no es opcional: es supervivencia.</p>'),
        ],
        'related': [
            '<a href="https://avesnativaschilenas.cl/aguilas/como-el-aguila-ensena-a-volar-a-sus-polluelos/">Version complementaria</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/">Aguilas en Chile</a>',
        ],
        'faqs': [
            ('El aguila literalmente empuja a sus polluelos?', 'La conducta real es mas compleja: hay estimulo, vigilancia y aprendizaje progresivo.'),
            ('Cuanto tardan en volar?', 'Depende de la especie y del ambiente, pero el proceso no es inmediato.'),
            ('Los padres siguen ayudandolos?', 'Si, hasta que ganan autonomia suficiente.'),
            ('Se puede observar sin molestar?', 'Si, manteniendo distancia y evitando acercarse al nido.'),
        ],
    },
    {
        'id': 9161,
        'title': 'Como el aguila madre ensena a volar a sus polluelos',
        'excerpt': 'Version complementaria sobre el aprendizaje de vuelo en polluelos de aguila y el rol de los padres.',
        'intro': 'Esta version complementa la guia biologica con una lectura mas observacional: el aprendizaje del vuelo en las aguilas es una mezcla de fuerza, coordinacion, vigilancia y mucha practica.',
        'lead_links': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Volver al hub de aguilas</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/como-ensena-el-aguila-a-volar-a-sus-polluelos/">Ver la otra variante</a>',
        ],
        'sections': [
            ('Por que es clave volar', '<p>En rapaces, volar bien significa cazar, escapar y sobrevivir. El aprendizaje implica acondicionamiento fisico y desarrollo de confianza.</p>'),
            ('Que hacen los adultos', '<p>Los padres alimentan, protegen y empujan a los jovenes a ganar espacio. No es dura crueldad ni magia: es una estrategia natural.</p>'),
            ('Que ocurre en el borde del nido', '<p>Alli se producen los intentos, los errores y los primeros despegues. La escena es intensa porque el polluelo cambia de dependencia a autonomia.</p>'),
            ('Lectura para observadores', '<p>Si ves este comportamiento, lo correcto es retirarte y dejar que el proceso siga. La distancia es siempre mejor que la curiosidad demasiado cercana.</p>'),
        ],
        'related': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Aguilas en Chile</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/aguila-peregrina/">Aguila peregrina</a>',
        ],
        'faqs': [
            ('Hay dos versiones del tema en el sitio?', 'Si, esta pagina complementa la otra variante del mismo concepto.'),
            ('Es un proceso facil?', 'No, requiere tiempo, fuerza y coordinacion.'),
            ('Los polluelos aprenden solos?', 'Aprenden con apoyo, estimulo y practica.'),
            ('Puedo acercarme al nido?', 'No es recomendable; la distancia protege a la cria.'),
        ],
    },
    {
        'id': 9362,
        'title': 'Cuanto mide un aguila real con las alas abiertas',
        'excerpt': 'Medidas, envergadura y comparaciones utiles para entender cuanto mide un aguila real con las alas abiertas.',
        'intro': 'La pregunta sobre la envergadura de un aguila real suele venir por curiosidad visual: cuanto ocupa en vuelo, como se compara con otras rapaces y por que su silueta impresiona tanto.',
        'lead_links': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Volver al hub de aguilas</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/cuanto-mide-un-aguila-con-las-alas-abiertas/">Version general de la medida</a>',
        ],
        'sections': [
            ('Envergadura aproximada', '<p>En un aguila real, la envergadura suele rondar rangos amplios segun la especie, la edad y el sexo. Lo importante para el lector no es una cifra aislada, sino entender que el ala abierta define su capacidad de planeo y caza.</p>'),
            ('Comparacion visual', '<p>Vista de cerca, la diferencia entre una rapaz mediana y un aguila real es enorme. El ala abierta sirve para comprender el espacio que necesita para maniobrar y cazar.</p>'),
            ('Por que importa la medida', '<p>La envergadura afecta el vuelo, el gasto energetico y el tipo de presas que puede capturar. Es una medida biologica, no solo estetica.</p>'),
            ('Como observarla', '<p>Lo mejor es verla planear en ambientes abiertos, donde el tamaño del vuelo se aprecia mucho mejor que posada.</p>'),
        ],
        'related': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Aguilas en Chile</a>',
            '<a href="https://avesnativaschilenas.cl/rapaces/">Rapaces chilenas</a>',
        ],
        'faqs': [
            ('La medida cambia segun el sexo?', 'Si, puede cambiar segun especie, sexo y edad.'),
            ('Se aprecia mejor volando o posada?', 'Volando, porque la envergadura se ve completa.'),
            ('Sirve para identificarla?', 'Ayuda mucho, pero no basta por si sola.'),
            ('Todas las aguilas miden igual?', 'No. La medida varia bastante entre especies.'),
        ],
    },
    {
        'id': 6992,
        'title': 'Cuanto mide un aguila con las alas abiertas',
        'excerpt': 'Guia rapida para entender la envergadura de las aguilas y como cambia segun la especie.',
        'intro': 'Esta pagina responde la duda mas general: cuanto mide un aguila cuando abre las alas. En vez de dar una sola cifra, conviene mirar rangos y diferencias entre especies, porque no todas las rapaces grandes tienen el mismo tamano ni la misma forma de vuelo.',
        'lead_links': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Volver al hub de aguilas</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/cuanto-mide-un-aguila-real-con-las-alas-abiertas/">Medida de aguila real</a>',
        ],
        'sections': [
            ('Rango general', '<p>La envergadura cambia segun especie, edad y contexto geografico. Lo util es entender que el ala abierta define el tipo de planeo y la maniobra que puede hacer el ave.</p>'),
            ('Factores que influyen', '<p>Sexo, alimentacion, etapa de vida y adaptacion al habitat pueden modificar el tamano visible. En rapaces grandes, cada centimetro importa para cazar y ahorrar energia.</p>'),
            ('Como compararla', '<p>Comparar con otras rapaces del mismo entorno permite notar si una especie es realmente grande o solo parece grande por la distancia y el angulo de observacion.</p>'),
            ('Interpretacion correcta', '<p>La medida no es un dato de vanidad; es una pieza importante para entender ecologia, movilidad y comportamiento de la especie.</p>'),
        ],
        'related': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Aguilas en Chile</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/cuanto-mide-un-aguila-real-con-las-alas-abiertas/">Aguila real</a>',
        ],
        'faqs': [
            ('Cambia mucho entre especies?', 'Si, bastante.'),
            ('La envergadura ayuda a identificar?', 'Si, especialmente si la combinas con habitat y forma del vuelo.'),
            ('Se ve igual en todas las edades?', 'No. Los jovenes pueden tener proporciones distintas.'),
            ('Es mejor medir en vuelo?', 'Si, porque el ala desplegada muestra la medida real.'),
        ],
    },
    {
        'id': 6998,
        'title': 'El aguila desvelada: como regula la vida en su ecosistema',
        'excerpt': 'Explicacion ecologica del papel del aguila como depredador y regulador de poblaciones.',
        'intro': 'El aguila no solo impresiona por su vuelo: tambien cumple una funcion ecologica importante. Esta pagina explica por que se la considera un regulador natural y como su presencia afecta a la cadena alimentaria.',
        'lead_links': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Volver al hub de aguilas</a>',
            '<a href="https://avesnativaschilenas.cl/rapaces/">Rapaces chilenas</a>',
        ],
        'sections': [
            ('Rol como depredador', '<p>Las aguilas ayudan a controlar poblaciones de pequenos vertebrados y otras presas. Eso evita desequilibrios y mantiene la presion natural sobre especies abundantes.</p>'),
            ('Efecto en el ecosistema', '<p>Cuando una rapaz esta presente, el ecosistema suele mostrar mejor equilibrio entre presas, depredadores y disponibilidad de refugio.</p>'),
            ('Por que importa', '<p>La gente suele pensar en el aguila como simbolo, pero su papel real es biogeografico y ecologico. Es una pieza funcional, no solo estetica.</p>'),
            ('Relacion con la conservacion', '<p>Proteger el habitat del aguila significa tambien cuidar el ambiente que sostiene a muchas otras especies.</p>'),
        ],
        'related': [
            '<a href="https://avesnativaschilenas.cl/rapaces/principales-amenazas-para-las-aves-rapaces-en-chile/">Amenazas de rapaces</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/">Aguilas en Chile</a>',
        ],
        'faqs': [
            ('Que hace el aguila en el ecosistema?', 'Regula poblaciones y ayuda al equilibrio natural.'),
            ('Es depredador tope?', 'En muchos contextos si, o esta muy cerca de esa posicion.'),
            ('Por que es importante conservarla?', 'Porque protege el equilibrio de presas y habitat.'),
            ('Sirve para entender cadenas alimenticias?', 'Si, es un ejemplo muy claro de depredador alto.'),
        ],
    },
    {
        'id': 9354,
        'title': 'Que significa que un aguila se pare en tu casa',
        'excerpt': 'Que puede significar ver un aguila posada en una casa y que hacer para observarla sin interferir.',
        'intro': 'Ver un aguila posada en una casa o en una estructura humana puede despertar mucha interpretacion simbolica. Pero antes de pensar en mensajes o presagios, conviene entender la conducta real del ave y el contexto del lugar.',
        'lead_links': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Volver al hub de aguilas</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/caracteristicas-de-las-aguilas-segun-la-biblia/">Simbolismo biblico</a>',
        ],
        'sections': [
            ('Que puede estar haciendo', '<p>Probablemente este descansando, vigilando o usando el punto mas alto disponible para observar el entorno. Las rapaces aprovechan postes, techos y arboles altos para tener mejor visibilidad.</p>'),
            ('Interpretacion simbolica', '<p>Muchos lectores lo ven como signo de fuerza, proteccion o cambio. Esa lectura es valida como simbolo, pero no sustituye la explicacion biologica.</p>'),
            ('Que hacer', '<p>No te acerques de golpe, no la intentes alimentar y evita movimientos bruscos. Lo mejor es dejar que siga su ruta o su descanso sin molestia.</p>'),
            ('Cuando preocuparse', '<p>Solo si el ave esta herida o atrapada. En ese caso, conviene contactar ayuda especializada y no intentar manipularla sin experiencia.</p>'),
        ],
        'related': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Aguilas en Chile</a>',
            '<a href="https://avesnativaschilenas.cl/aguilas/caracteristicas-de-las-aguilas-segun-la-biblia/">Aguila en la Biblia</a>',
        ],
        'faqs': [
            ('Es un buen presagio?', 'Depende de la lectura personal; biologicamente solo significa que uso ese punto alto.'),
            ('Debo espantarla?', 'No, salvo que exista un riesgo real para el ave o para personas vulnerables.'),
            ('Por que elige el techo?', 'Porque ofrece altura, visibilidad y una buena plataforma de observacion.'),
            ('Y si esta herida?', 'Contacta apoyo de fauna silvestre.'),
        ],
    },
    {
        'id': 6987,
        'title': 'Accipitriformes vs falconiformes',
        'excerpt': 'Diferencias claras entre accipitriformes y falconiformes para no confundir aguilas, halcones y otras rapaces.',
        'intro': 'Mucha gente mete a todas las rapaces en el mismo saco. Esta pagina aclara una confusion muy comun: que diferencia hay entre accipitriformes y falconiformes, y por que esa distincion importa cuando hablas de aguilas.',
        'lead_links': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Volver al hub de aguilas</a>',
            '<a href="https://avesnativaschilenas.cl/rapaces/">Guia de rapaces</a>',
        ],
        'sections': [
            ('Accipitriformes', '<p>Incluyen aguilas, buitres del viejo mundo y varias rapaces de gran tamano con vuelo planeado y garras potentes.</p>'),
            ('Falconiformes', '<p>Se relacionan con halcones y especies de vuelo mas rapido, con morfologia distinta y estrategias de caza muy precisas.</p>'),
            ('Por que importa', '<p>La clasificacion ayuda a no confundir comportamientos, habitats y formas de vuelo. No todas las rapaces grandes son aguilas.</p>'),
            ('Aplicacion en Chile', '<p>En observacion local, esta diferencia sirve para reconocer que especie estas viendo antes de etiquetarla por tamano o presencia.</p>'),
        ],
        'related': [
            '<a href="https://avesnativaschilenas.cl/aguilas/">Aguilas en Chile</a>',
            '<a href="https://avesnativaschilenas.cl/rapaces/">Aves rapaces chilenas</a>',
        ],
        'faqs': [
            ('Son el mismo grupo?', 'No. Son grupos distintos dentro de las rapaces.'),
            ('Las aguilas donde van?', 'Dentro de los accipitriformes.'),
            ('Los halcones donde van?', 'Se asocian a falconiformes o grupos cercanos segun clasificacion usada.'),
            ('Sirve para identificar aves?', 'Si, porque te ayuda a separar vuelo, forma y estrategia de caza.'),
        ],
    },
]


def main():
    ok = 0
    for item in ITEMS:
        content = build_content(item)
        status, _ = update_post(item['id'], item['title'], content, item.get('excerpt'), item.get('kind', 'posts'))
        if status == 200:
            ok += 1
            print(f"OK {item['id']} -> {item['title']}")
        else:
            print(f"ERR {item['id']} -> {status}")
    print(f'Total actualizados: {ok}/{len(ITEMS)}')


if __name__ == '__main__':
    main()
