import requests, time, re, json

AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'

BANNER = '<div class="actualizacion-2026" style="background:#fffde7;padding:10px 15px;border-radius:6px;margin-bottom:20px;border-left:4px solid #fbc02d;font-size:0.95em;"><strong>📅 Actualizado: Mayo 2026</strong> — Esta guía fue revisada y actualizada con información reciente para ayudarte a cuidar mejor de tus aves.</div>'
AFF = '<p><em>Como afiliado de Amazon, gano por compras calificadas.</em></p>'

def tag_id(slug):
    r = requests.get(BASE + 'tags', auth=AUTH, params={'slug': slug})
    if r.status_code == 200 and r.json():
        return r.json()[0]['id']
    r2 = requests.post(BASE + 'tags', auth=AUTH, json={'name': slug.replace('-',' ').title(), 'slug': slug})
    return r2.json()['id'] if r2.status_code == 201 else None

TAG_AVES_NATIVAS = tag_id('aves-nativas')
TAG_CUIDADOS = tag_id('cuidados')

def make_faq_schema(faqs):
    items = [{'@type':'Question','name':q,'acceptedAnswer':{'@type':'Answer','text':a}} for q,a in faqs]
    return f'<script type="application/ld+json">{{\"@context\":\"https://schema.org\",\"@type\":\"FAQPage\",\"mainEntity\":{json.dumps(items, ensure_ascii=False)}}}</script>'

def update_post(pid, content, tags=None):
    data = {'content': content}
    if tags is not None:
        data['tags'] = tags
    r = requests.put(f'{BASE}posts/{pid}', auth=AUTH, json=data)
    return r.status_code == 200

# ====== REWRITE CONTENT PER POST ======
# Each entry: (post_id, new_intro, h2_sections, faqs, new_tags)

rewrites = [
    # === PULGA DE PALOMA ===
    (14119, [
        "<p>La pulga de paloma es un ectoparásito que puede convertirse en una molestia tanto para las aves como para las personas. También conocidas como pulgas de paloma o piojos de paloma, estos insectos se alimentan de sangre y pueden infestar balcones, terrazas y áticos donde las palomas anidan. En esta guía completa aprenderás a identificarlas, eliminarlas y prevenir su regreso de forma efectiva.</p>",
        "<p>Las pulgas de las palomas son pequeños insectos sin alas, de color oscuro y cuerpo aplanado lateralmente que les permite moverse entre las plumas. Miden entre 1 y 3 mm y son excelentes saltadoras, pudiendo alcanzar distancias hasta 200 veces su tamaño. A diferencia de los piojos, las pulgas se alimentan de sangre (hematófagas) y pueden picar a humanos cuando no encuentran un huésped ave disponible.</p>"
    ], [
        ("¿Cómo identificar la pulga de paloma?", "<p>Las pulgas de paloma (<em>Pulex irritans</em> variedad aviar) son de color marrón oscuro a negro, cuerpo comprimido lateralmente y patas traseras adaptadas para saltar. Sus picaduras en humanos aparecen como pequeños puntos rojos con un centro más oscuro, generalmente en tobillos y piernas. A diferencia de las chinches, las picaduras de pulga suelen aparecer en grupos de 2-3 y causan picazón intensa.</p>"),
        ("Riesgos para la salud", "<p>Las pulgas de paloma no solo causan picazón e irritación. Pueden transmitir bacterias y parásitos como <em>Rickettsia typhi</em> (tifus murino) y <em>Dipylidium caninum</em> (tenia). En personas alérgicas, la saliva de la pulga puede desencadenar dermatitis severa. En aves, una infestación severa puede causar anemia, pérdida de peso y debilitamiento general.</p>"),
        ("Cómo eliminar las pulgas de paloma paso a paso", "<p><strong>1. Tratar las aves:</strong> Si tienes palomas domésticas, aplica polvo insecticida específico para aves (como permetrina en polvo) siguiendo las indicaciones del veterinario. <strong>2. Limpiar el entorno:</strong> Retira nidos viejos, plumas y acumulaciones de heces. Lava las superficies con agua caliente y desinfectante. <strong>3. Insecticidas ambientales:</strong> Usa aerosoles con piretroides en zonas de paso de las aves. <strong>4. Aspirar a fondo:</strong> Pasa la aspiradora por alfombras, sofás y rendijas donde puedan esconderse huevos y larvas.</p>"),
        ("Remedios caseros efectivos", "<p>El vinagre de manzana diluido en agua (1:1) puede ayudar a repeler las pulgas cuando se rocía en zonas de descanso de las aves. El aceite de neem es otro repelente natural que interfiere con el ciclo reproductivo de las pulgas. La tierra de diatomeas de grado alimenticio espolvoreada en nidos y perchas deshidrata a las pulgas adultas. Estos métodos son complementarios, no sustitutos del tratamiento principal.</p>"),
        ("Prevención a largo plazo", "<p>Para evitar que las pulgas regresen: instala mallas o pinchos en balcones y alféizares donde las palomas se posan, sella grietas y huecos en fachadas, mantén una limpieza regular de terrazas y tejados, y considera el uso de repelentes ultrasónicos o visuales para disuadir a las palomas de anidar cerca de tu hogar.</p>"),
        ("¿Cuándo llamar a un profesional?", "<p>Si después de aplicar estas medidas la infestación persiste, o si la colonia de palomas es numerosa y de difícil acceso, contacta a una empresa de control de plagas profesional. Ellos cuentan con productos de uso restringido y equipos de protección adecuados para tratar zonas elevadas y de difícil acceso.</p>")
    ], [
        ("¿La pulga de paloma puede vivir en humanos?", "No, las pulgas de paloma prefieren aves como huésped, pero pueden picar a humanos si no hay otro hospedador disponible. No establecen colonias en personas ni en mascotas peludas como perros o gatos."),
        ("¿Cómo saber si mi casa tiene pulgas de paloma?", "Revisa si hay pequeñas motas negras (heces de pulga) en alféizares, balcones o cerca de nidos. También puedes colocar un plato con agua jabonosa cerca de una fuente de luz: las pulgas saltarán hacia el calor y quedarán atrapadas."),
        ("¿El vinagre mata las pulgas de paloma?", "El vinagre de manzana actúa como repelente, no como insecticida. Ayuda a mantenerlas alejadas pero no elimina una infestación existente."),
        ("¿Cuánto tiempo vive una pulga de paloma?", "El ciclo de vida completo dura entre 2 y 3 semanas en condiciones óptimas. Las pulgas adultas pueden vivir varios meses si tienen acceso regular a sangre.")
    ], ['aves-nativas', 'cuidados']),

    # === GALLINA CRIOLLA CHILENA ===
    (11836, [
        "<p>La gallina criolla chilena es una raza adaptada a las condiciones locales del campo chileno, criada por generaciones gracias a su resistencia, fertilidad y capacidad de autoabastecimiento. A diferencia de las razas industriales, la gallina criolla se alimenta naturalmente, produce huevos de excelente calidad y requiere menos cuidados intensivos. Esta guía completa te enseñará todo sobre sus características, crianza y cuidados.</p>"
    ], [
        ("Características de la gallina criolla chilena", "<p>La gallina criolla chilena es de tamaño mediano, con plumaje variado que incluye tonos coloradas, negras, blancas y mezcladas. Su peso oscila entre 1,5 y 2,5 kg en las hembras y hasta 3 kg en los gallos. Son aves rústicas, con patas fuertes y pico resistente, ideales para el pastoreo libre. Su cresta es simple y de color rojo brillante. Una de sus principales ventajas es su longevidad: pueden vivir y poner huevos durante 5 a 7 años, mucho más que las razas industriales.</p>"),
        ("Cría y reproducción", "<p>Las gallinas criollas son excelentes cluecas: se echan naturalmente sobre los huevos y son madres dedicadas con sus polluelos. La temporada de cría óptima es la primavera, cuando las temperaturas son suaves y hay abundancia de insectos. La incubación dura 21 días. Cada gallina puede incubar de 10 a 14 huevos. Los polluelos nacen cubiertos de plumón y pueden caminar y alimentarse por sí mismos desde el primer día, aunque la madre los protege y guía durante las primeras semanas.</p>"),
        ("Alimentación adecuada", "<p>La gallina criolla se alimenta principalmente de granos (maíz partido, trigo, avena), verduras, restos de cocina (verduras, cáscaras de frutas) y lo que encuentra picoteando en el terreno: insectos, gusanos, semillas y pasto. Es importante complementar con calcio (cáscaras de huevo molidas o conchuela) para asegurar huevos de cáscara firme. El agua fresca y limpia debe estar siempre disponible.</p>"),
        ("Instalaciones y espacio", "<p>El gallinero debe ser amplio, seco y bien ventilado. Se recomienda un mínimo de 1 m² por cada 4 gallinas dentro del gallinero, más un corral exterior de al menos 2 m² por ave. Los nidos deben ubicarse en zonas tranquilas y oscuras, a razón de 1 nido por cada 3-4 gallinas. La cama (viruta, paja o cascarilla de arroz) debe cambiarse cada 2-3 semanas para evitar la acumulación de amoníaco y parásitos.</p>"),
        ("Salud y prevención de enfermedades", "<p>Las gallinas criollas son resistentes pero no inmunes. Las enfermedades más comunes son el moquillo, la coriza y los parásitos externos (piojos, ácaros). La desparasitación debe hacerse cada 3-4 meses con productos naturales como tierra de diatomeas o vinagre de manzana en el agua. La vacunación contra la enfermedad de Newcastle y la viruela aviar es recomendable en zonas de alta densidad avícola.</p>")
    ], [
        ("¿Cuántos huevos pone una gallina criolla al año?", "Una gallina criolla chilena pone entre 120 y 180 huevos al año, dependiendo de la alimentación, la edad y la época del año. Su producción es estacional: pone más en primavera y verano, y disminuye en invierno."),
        ("¿Se puede criar gallinas criollas en el patio de una casa?", "Sí, siempre que se cumplan las normas municipales y se cuente con un espacio mínimo de 4 m² por ave. En zonas urbanas se recomienda consultar la ordenanza local sobre tenencia de aves de corral."),
        ("¿Las gallinas criollas necesitan gallo para poner huevos?", "No, la gallina pone huevos sin necesidad de gallo. El gallo solo es necesario si se desea incubar los huevos y obtener polluelos."),
        ("¿Cuál es la mejor alimentación para que pongan más huevos?", "Una dieta equilibrada con 16-18% de proteína, calcio suplementario y acceso a pastoreo natural. El maíz partido y los residuos de verduras son excelentes complementos.")
    ], ['aves-nativas', 'cuidados']),

    # === ALIMENTACIÓN DE LAS AVES (2 posts - fusionarlos) ===
    (11359, [
        "<p>La alimentación de las aves es uno de los aspectos más fascinantes de su biología. Existen tantos tipos de dietas como especies de aves: desde colibríes que liban néctar hasta águilas que cazan presas vivas. Comprender los tipos de alimentación de las aves es clave para quien las observa, las estudia o las tiene como mascotas. Esta guía completa clasifica las dietas aviares, explica sus adaptaciones y ofrece ejemplos de cada categoría.</p>"
    ], [
        ("Aves granívoras: las comedoras de semillas", "<p>Las aves granívoras se alimentan principalmente de semillas y granos. Tienen picos cortos, cónicos y fuertes, diseñados para romper cáscaras duras. Ejemplos clásicos son los canarios, gorriones, pinzones, jilgueros, palomas, tórtolas, codornices y faisanes. Su sistema digestivo incluye una molleja musculosa que tritura los granos, a menudo ayudada por pequeñas piedras (grit) que el ave ingiere deliberadamente.</p>"),
        ("Aves insectívoras: cazadoras de insectos", "<p>Las aves insectívoras se alimentan de insectos y otros invertebrados. Tienen picos finos y puntiagudos, perfectos para atrapar presas pequeñas. Ejemplos: golondrinas, vencejos, mirlos, carboneros, herrerillos, alondras y pájaros carpinteros. Durante la temporada de cría, muchas aves que normalmente comen semillas también alimentan a sus polluelos con insectos, ricos en proteínas esenciales para el crecimiento.</p>"),
        ("Aves carnívoras y rapaces", "<p>Las aves carnívoras o rapaces se alimentan de carne. Tienen picos ganchudos y garras afiladas para capturar y desgarrar presas. Se dividen en rapaces diurnas (águilas, halcones, milanos, aguiluchos) y rapaces nocturnas (búhos, lechuzas, chunchos, cárabos). Su dieta incluye roedores, reptiles, otras aves, peces e insectos grandes. Los buitres y cóndores son carroñeros especializados en consumir animales muertos.</p>"),
        ("Aves frugívoras y nectarívoras", "<p>Las frugívoras se alimentan de frutas y bayas, cumpliendo un rol clave en la dispersión de semillas. Ejemplos: tucanes, loros, cotorras, zorzales y mirlos. Las nectarívoras, como los colibríes y los mieleros, se alimentan de néctar floral usando lenguas largas y especializadas. Ambas dietas son ricas en azúcares y proporcionan energía rápida para vuelos activos.</p>"),
        ("Aves omnívoras: las más versátiles", "<p>Las aves omnívoras combinan alimentos de origen vegetal y animal. Esta versatilidad les permite adaptarse a diferentes hábitats y estaciones. Ejemplos: cuervos, urracas, gaviotas, gallinas y estorninos. Su pico suele ser de forma intermedia, ni tan cónico como el de las granívoras ni tan ganchudo como el de las rapaces.</p>"),
        ("Adaptaciones del pico según la dieta", "<p>Existe una regla de oro en ornitología: la forma del pico predice la dieta. Picos ganchudos = carnívoras. Picos cortos y cónicos = granívoras. Picos largos y finos = nectarívoras o insectívoras especializadas. Picos anchos y planos = filtradoras (patos, flamencos). Picos rectos y fuertes = carpinteros (para taladrar madera). Picos curvados y robustos = loros y tucanes (para manipular frutas).</p>")
    ], [
        ("¿Todas las aves tienen el mismo tipo de dieta?", "No, las aves tienen dietas muy diversas. Existen al menos 7 grandes tipos: granívoras, insectívoras, carnívoras, frugívoras, nectarívoras, omnívoras y carroñeras. Muchas especies combinan varios tipos según la estación."),
        ("¿Qué ave tiene la dieta más variada?", "Los cuervos y las urracas son omnívoros extremadamente versátiles: comen insectos, fruta, carroña, huevos, semillas, pequeños vertebrados y hasta basura humana."),
        ("¿Los colibríes solo comen néctar?", "No, complementan su dieta con pequeños insectos y arañas para obtener proteínas esenciales que el néctar no les proporciona."),
        ("¿Cómo saber qué come un ave por su pico?", "La forma del pico es un indicador confiable: cónico = semillas, ganchudo = carne, largo y fino = néctar/insectos, ancho y plano = filtración.")
    ], ['aves-nativas', 'cuidados']),

    # === DE QUÉ SE ALIMENTAN LAS AVES (make it different from 11359) ===
    (11353, [
        "<p>¿De qué se alimentan las aves? Es una pregunta con cientos de respuestas distintas. Las aves han colonizado prácticamente todos los ecosistemas del planeta, y su dieta refleja esa increíble diversidad. En esta guía completa sobre la dieta aviar exploramos los distintos tipos de alimentos que consumen, desde semillas y néctar hasta presas vivas y carroña, y cómo cada especie se ha adaptado a su nicho alimenticio.</p>"
    ], [
        ("Semillas y granos: la base de muchas dietas", "<p>Las semillas son el alimento más común entre las aves. Canarios, periquitos, jilgueros, palomas y gorriones basan su dieta en semillas de alpiste, mijo, avena, girasol y cáñamo. Las aves granívoras tienen picos cortos y fuertes para romper cáscaras y una molleja potente que tritura los granos con ayuda de piedrecillas (grit) que ingieren.</p>"),
        ("Frutas y néctar: energía rápida", "<p>Muchas aves tropicales y paseriformes se alimentan de frutas y bayas. Los tucanes, loros, zorzales y mirlos son frugívoros importantes para la dispersión de semillas. Los colibríes, por su parte, beben néctar de las flores usando su lengua extensible y tubular, visitando cientos de flores al día para satisfacer su altísimo metabolismo.</p>"),
        ("Insectos y pequeños invertebrados", "<p>Los insectos son la principal fuente de proteína para las aves insectívoras como golondrinas, vencejos, carboneros y pájaros carpinteros. Durante la época de cría, incluso las aves granívoras alimentan a sus polluelos con insectos, ya que las proteínas son esenciales para el desarrollo de plumas y músculos.</p>"),
        ("Peces y vida acuática", "<p>Las aves acuáticas como garzas, cormoranes, martines pescadores, pingüinos y pelícanos se alimentan de peces, crustáceos y moluscos. Tienen picos adaptados para pescar: largos y puntiagudos (garzas), con gancho (cormoranes) o con bolsa (pelícanos). Los flamencos filtran plancton con sus picos especializados.</p>"),
        ("Carroña y desechos", "<p>Los buitres, cóndores y algunos cuervos se especializan en consumir animales muertos. Su sistema digestivo es capaz de neutralizar bacterias y toxinas de la carne en descomposición. Las gaviotas y urracas también aprovechan restos de comida humana, lo que las convierte en omnívoras oportunistas.</p>")
    ], [
        ("¿Puedo darle pan a las aves?", "No se recomienda. El pan no tiene valor nutricional para las aves y puede causar problemas digestivos. Es mejor ofrecer semillas, frutas o piensos específicos."),
        ("¿Las aves necesitan beber agua?", "Sí, todas las aves necesitan agua fresca a diario para hidratarse y para mantener sus plumas en buen estado. Muchas también se bañan regularmente."),
        ("¿Qué comen los polluelos de las aves?", "Depende de la especie. Los polluelos de aves granívoras reciben papilla de semillas regurgitada; los de aves insectívoras reciben insectos; y los de aves rapaces reciben carne desgarrada por los padres."),
        ("¿Las aves comen de noche?", "La mayoría de las aves son diurnas y se alimentan durante el día. Las excepciones son las rapaces nocturnas (búhos, lechuzas), los chotacabras y algunas aves acuáticas que aprovechan las mareas nocturnas.")
    ], ['aves-nativas', 'cuidados']),

    # === SI LOS PÁJAROS NO SE PREOCUPAN POR COMER ===
    (9421, [
        "<p>La frase \"si los pájaros no se preocupan por qué comer\" proviene de una conocida enseñanza bíblica (Mateo 6:26) que invita a confiar en la providencia divina. Pero desde una perspectiva biológica, los pájaros sí dedican gran parte de su energía a buscar alimento. Esta reflexión explora el significado espiritual y la realidad natural detrás de esta poderosa metáfora.</p>"
    ], [
        ("El significado espiritual de la frase", "<p>En el contexto bíblico, Jesús utiliza a las aves como ejemplo de confianza en la providencia: \"Mirad las aves del cielo, que no siembran, ni siegan, ni recogen en graneros; y vuestro Padre celestial las alimenta\". Esta enseñanza invita a las personas a confiar en que sus necesidades básicas serán cubiertas, liberándose de la ansiedad excesiva por el futuro.</p>"),
        ("La realidad biológica: las aves sí se preocupan", "<p>Desde el punto de vista ornitológico, las aves dedican entre el 60% y el 80% de sus horas de luz a buscar alimento. Un carbonero puede visitar hasta 1000 veces su comedero en un solo día. Un colibrí consume hasta 10 veces su peso en néctar diariamente. Lejos de no preocuparse, las aves han desarrollado complejas estrategias de forrajeo, memoria espacial para recordar fuentes de alimento, y comportamientos migratorios para seguir la disponibilidad estacional de comida.</p>"),
        ("La lección ecológica: equilibrio natural", "<p>La enseñanza bíblica y la realidad biológica no son contradictorias. Las aves trabajan incansablemente por su alimento, pero lo hacen dentro de un ecosistema que les provee lo necesario. Confianza no significa pasividad, sino armonía con un sistema mayor. Cada ave cumple su rol en la cadena alimenticia y el equilibrio natural.</p>")
    ], [
        ("¿Qué dice la Biblia sobre las aves?", "Mateo 6:26 dice: \"Mirad las aves del cielo, que no siembran, ni siegan, ni recogen en graneros; y vuestro Padre celestial las alimenta. ¿No valéis vosotros mucho más que ellas?\"."),
        ("¿Cuánto tiempo pasan las aves buscando comida?", "Entre el 60% y el 80% de las horas de luz del día, dependiendo de la especie y la disponibilidad de alimento."),
        ("¿Las aves almacenan comida?", "Sí, muchas aves como arrendajos, carboneros y urracas esconden semillas y nueces para consumirlas en épocas de escasez, demostrando una notable memoria espacial para recordar cientos de escondites.")
    ], ['significado-espiritual', 'cuidados']),

    # === CUAL ES EL MEJOR PAJARO PARA TENER EN CASA ===
    (9452, [
        "<p>¿Cuál es el mejor pájaro para tener en casa? La respuesta depende de tu estilo de vida, espacio disponible y experiencia previa con aves. En esta guía comparativa analizamos las especies más populares como mascotas, sus necesidades de cuidado, esperanza de vida, nivel de interacción y los pros y contras de cada una para ayudarte a elegir el compañero alado ideal.</p>"
    ], [
        ("Canario: el clásico cantor", "<p>El canario es ideal para principiantes. Son aves independientes que no requieren interacción constante. Su principal atractivo es el canto del macho. Viven de 8 a 12 años en buenas condiciones. Necesitan una jaula amplia, alpiste de calidad, frutas y verduras frescas, y baños regulares. No requieren compañía forzada y se adaptan bien a espacios pequeños como departamentos.</p>"),
        ("Periquito australiano: pequeño y sociable", "<p>El periquito australiano es una de las aves de compañía más populares del mundo. Son juguetones, aprenden a silbar e incluso a repetir palabras. Viven de 6 a 10 años. Necesitan una jaula espaciosa con barrotes horizontales para trepar, juguetes para estimulación mental, y lo ideal es tenerlos en pareja para que no se sientan solos. Su alimentación base es el mijo y alpiste, complementado con verduras.</p>"),
        ("Ninfa (cacatúa australiana): cariñosa y divertida", "<p>La ninfa o carolina es famosa por su copete amarillo y sus mejillas anaranjadas. Son extremadamente cariñosas, disfrutan la interacción humana y aprenden a silbar melodías. Viven de 15 a 20 años. Requieren una jaula grande (mínimo 80 cm de ancho), juguetes variados y tiempo fuera de la jaula diariamente. No son recomendables para personas que pasan mucho tiempo fuera de casa.</p>"),
        ("Agapornis (inseparable): energía pura", "<p>Los agapornis o inseparables son loros pequeños pero llenos de personalidad. Son muy activos, juguetones y forman vínculos fuertes con sus dueños. Viven de 10 a 15 años. Necesitan una jaula grande, juguetes para roer (madera, cuero), y una dieta variada con semillas, frutas y verduras. Son ruidosos y requieren atención diaria. Lo ideal es tenerlos en pareja.</p>"),
        ("Diamante mandarín: fácil y de bajo mantenimiento", "<p>El diamante mandarín es un excelente pájaro para quienes quieren disfrutar de aves sin demasiadas exigencias. Son pequeños, tranquilos y viven bien en grupo. Viven de 5 a 7 años. Se conforman con una jaula mediana, alpiste y mijo, y verduras frescas. No requieren interacción forzada y son ideales para observar su comportamiento social en pareja o colonia.</p>")
    ], [
        ("¿Cuál es la mejor ave para un principiante?", "El canario y el periquito australiano son las opciones más recomendables para principiantes por su facilidad de cuidado y costos accesibles."),
        ("¿Qué pájaro vive más años?", "Las ninfas y los loros pequeños (agapornis) pueden vivir entre 15 y 20 años con cuidados adecuados."),
        ("¿Se puede tener un solo pájaro o es mejor una pareja?", "Depende de la especie. Los canarios pueden vivir solos. Los periquitos, ninfas y agapornis se benefician de tener compañía (humana o de su misma especie).")
    ], ['aves-nativas', 'cuidados']),

    # === QUE CREMA PONER A UN CANARIO HERIDO ===
    (9437, [
        "<p>Si tu canario tiene una herida, es natural querer ayudarlo rápidamente. Pero no cualquier crema es segura para las aves. Su piel es extremadamente delicada y muchos productos humanos pueden ser tóxicos. En esta guía te explicamos qué crema puedes ponerle a un canario en la herida, cuáles debes evitar y cómo realizar una curación segura paso a paso.</p>"
    ], [
        ("¿Qué crema es segura para un canario?", "<p>Los únicos productos tópicos seguros para aves son aquellos formulados específicamente para ellas o recomendados por un veterinario aviar. La pomada veterinaria con antibiótico como la bacitracina o la neomicina en presentación oftálmica (sin corticoides) puede usarse en heridas superficiales. También es seguro el gel de áloe vera puro al 100% en heridas menores. La clorhexidina diluida al 0.05% es ideal para limpiar la herida antes de aplicar cualquier producto.</p>"),
        ("Productos que NUNCA debes usar", "<p>Nunca uses cremas con corticoides (hidrocortisona, betametasona) en aves: se absorben rápidamente por su piel delgada y pueden causar daños hepáticos. Evita también el alcohol, agua oxigenada, yodo en exceso, pomadas con mentol o alcanfor, cremas para humanos con perfume, y cualquier producto que no esté aprobado por un veterinario aviar.</p>"),
        ("Primeros auxilios para un canario herido", "<p>1. Lávate bien las manos. 2. Sujeta al canario con una toalla suave, cubriéndole la cabeza para reducir el estrés. 3. Limpia la herida con suero fisiológico o clorhexidina diluida al 0.05% usando una gasa estéril. 4. Si hay sangrado, aplica presión suave con una gasa estéril hasta que se detenga. 5. Aplica una capa fina de la crema recomendada por el veterinario. 6. Coloca al canario en una jaula tranquila y cálida para que se recupere.</p>"),
        ("Cuándo acudir al veterinario", "<p>Acude al veterinario aviar inmediatamente si: la herida es profunda o no deja de sangrar, si ves hueso o tejido expuesto, si el ala o pata está colgando (posible fractura), si el canario está letárgico o no come, o si aparece hinchazón, pus o mal olor en la herida. Las infecciones en aves progresan muy rápido y pueden ser mortales en 24-48 horas.</p>")
    ], [
        ("¿Se puede usar Betadine en canarios?", "El Betadine diluido (yodo povidona) puede usarse solo para limpiar heridas superficiales y en dilución 1:10 con agua. No debe aplicarse en ojos ni en heridas profundas."),
        ("¿La crema de árnica es segura para aves?", "No. El árnica es tóxica para las aves y nunca debe aplicarse en su piel."),
        ("¿Cada cuánto cambiar la cura?", "Una vez al día es suficiente, a menos que el veterinario indique otra frecuencia. Si la herida se ensucia o moja, cambia la cura inmediatamente."),
        ("¿Puedo usar aceite de coco en la herida?", "El aceite de coco virgen extra puede usarse como emoliente suave en heridas superficiales, pero no tiene propiedades antisépticas comprobadas para aves.")
    ], ['aves-nativas', 'cuidados']),

    # === PAVO REAL MACHO VS HEMBRA ===
    (9426, [
        "<p>Diferenciar un pavo real macho de una hembra es muy sencillo una vez que conoces las claves. El dimorfismo sexual del pavo real común (<em>Pavo cristatus</em>) es uno de los más marcados del reino animal. Mientras el macho despliega un abanico de plumas iridiscentes de hasta 2 metros, la hembra pasa desapercibida con su plumaje pardo. En esta guía te mostramos todas las diferencias.</p>"
    ], [
        ("Diferencias físicas principales", "<p><strong>Macho:</strong> Mide 100-115 cm (hasta 225 cm con el abanico). Pesa 4-6 kg. Plumaje azul iridiscente en pecho y cuello. Cola secundaria con ocelos multicolores. Copete de plumas con eje blanco y punta azul verdosa. Espolones en las patas. <strong>Hembra:</strong> Mide ~95 cm. Pesa 2,75-4 kg. Plumaje pardo-marrón con manchas pálidas. Sin abanico ni ocelos. Copete marrón con puntas verdes. Sin espolones notorios. Cuello verde metálico apagado.</p>"),
        ("Diferencias en el comportamiento", "<p>El macho despliega su abanico y emite llamadas ruidosas para atraer a las hembras durante la época de apareamiento (primavera). Realiza la danza de cortejo moviendo el abanico y las alas. La hembra es más reservada, emite sonidos más suaves y es quien elige al macho basándose en la calidad y tamaño de su plumaje. Las hembras son las únicas que incuban los huevos y crían a los polluelos.</p>"),
        ("¿A qué edad se nota la diferencia?", "<p>Hasta los 2 meses de edad, machos y hembras jóvenes son prácticamente idénticos, ambos de color pardo-amarillento. A partir del segundo mes, los machos jóvenes comienzan a desarrollar las plumas primarias externas de color gris claro, mientras que las hembras las mantienen marrones. La cola secundaria (abanico) comienza a desarrollarse en el macho durante el segundo año de vida y alcanza su máximo esplendor hacia el tercer año.</p>"),
        ("Otras diferencias notables", "<p>Los machos tienen la cara con piel blanca desnuda y dos líneas blancas arriba y abajo del ojo. Las hembras tienen la cara marrón rojiza. En las alas, los machos presentan plumas barradas (con bandas) mientras que en las hembras son lisas. Las patas del macho son más robustas y equipadas con espolones que usa para defenderse y competir por las hembras.</p>")
    ], [
        ("¿El pavo real hembra también tiene cola colorida?", "No, solo el macho desarrolla el abanico de plumas coloridas con ocelos. La hembra tiene una cola marrón corta y sin adornos."),
        ("¿Cómo se llama la hembra del pavo real?", "La hembra del pavo real se llama pava real (del inglés peahen)."),
        ("¿Los pavos reales cambian de color según la temporada?", "El plumaje del macho no cambia de color, pero el abanico se renueva anualmente mediante la muda, que ocurre después de la temporada de apareamiento."),
        ("¿A qué edad un pavo real macho desarrolla su cola?", "El abanico comienza a formarse durante el segundo año de vida y alcanza su máximo desarrollo hacia los 3 años.")
    ], ['aves-nativas', 'cuidados']),

    # === PAVO REAL PELIGRO EXTINCIÓN ===
    (9403, [
        "<p>¿Está el pavo real en peligro de extinción? La respuesta breve es: depende de la especie. El pavo real común (<em>Pavo cristatus</em>) es considerado de Preocupación Menor por la UICN, pero el pavo real verde (<em>Pavo muticus</em>) está clasificado como En Peligro (EN). Existe también el pavo real del Congo (<em>Afropavo congensis</em>), clasificado como Vulnerable. Analicemos la situación de cada una.</p>"
    ], [
        ("Estado de conservación del pavo real común (Pavo cristatus)", "<p>El pavo real común o de la India es la especie más extendida y su estado es de Preocupación Menor (LC) según la UICN. Se estima una población de más de 100.000 individuos. Sin embargo, las subespecies silvestres enfrentan amenazas locales como la caza furtiva por sus plumas y carne, la pérdida de hábitat por deforestación y la depredación por perros y gatos asilvestrados.</p>"),
        ("Pavo real verde (Pavo muticus): en peligro real", "<p>El pavo real verde del Sudeste Asiático está clasificado como En Peligro (EN). Su población ha disminuido drásticamente por la caza indiscriminada, la destrucción de su hábitat para agricultura y la fragmentación de sus poblaciones. Se estiman menos de 20.000 individuos maduros en estado silvestre. Los esfuerzos de conservación se centran en proteger sus hábitats restantes y controlar la caza furtiva.</p>"),
        ("Pavo real del Congo (Afropavo congensis): vulnerable", "<p>El pavo real del Congo es la especie menos conocida y la única endémica de África. Clasificado como Vulnerable (VU), habita las selvas tropicales de la República Democrática del Congo. Se estiman entre 2.500 y 10.000 individuos. Las amenazas incluyen la minería, la agricultura migratoria y la caza para consumo local.</p>"),
        ("¿Qué podemos hacer para protegerlos?", "<p>Para ayudar en la conservación de los pavos reales: apoya organizaciones de conservación que trabajan en Asia y África, no compres plumas de pavo real verde (podrían provenir de caza ilegal), evita productos que contribuyan a la deforestación de sus hábitats, y si tienes pavos reales en cautiverio, asegúrate de que provengan de criaderos legales y no del tráfico de especies.</p>")
    ], [
        ("¿Cuántos pavos reales quedan en el mundo?", "Se estiman más de 100.000 pavos reales comunes, menos de 20.000 pavos reales verdes, y entre 2.500 y 10.000 pavos reales del Congo."),
        ("¿Por qué el pavo real está en peligro de extinción en algunos lugares?", "Las principales causas son la caza furtiva por plumas y carne, la deforestación de su hábitat, y la depredación por especies introducidas como perros y gatos."),
        ("¿El pavo real común está en peligro?", "No, el pavo real común (<em>Pavo cristatus</em>) está clasificado como Preocupación Menor (LC) por la UICN, aunque enfrenta amenazas locales."),
        ("¿Dónde vive el pavo real en estado silvestre?", "El pavo real común es nativo de India, Pakistán, Sri Lanka y Nepal. El pavo real verde habita en el Sudeste Asiático (Myanmar, Tailandia, Vietnam, Laos). El pavo real del Congo vive en la República Democrática del Congo.")
    ], ['especies-amenazadas', 'cuidados']),

    # === DE QUÉ COLOR SON LOS HUEVOS DE PAVO REAL ===
    (9364, [
        "<p>Los huevos de pavo real tienen un color y tamaño muy característicos que los diferencian de otras aves de corral. La hembra del pavo real (<em>Pavo cristatus</em>) pone huevos de color marrón claro a crema, con una textura lisa y ligeramente brillante. Pero el color no es lo único interesante: conoce todo sobre la puesta, incubación y curiosidades de los huevos de esta majestuosa ave.</p>"
    ], [
        ("Color y apariencia de los huevos", "<p>Los huevos de pavo real son de color marrón claro o beige, similares al color del café con leche. Algunas hembras ponen huevos con un tono ligeramente verdoso o crema. La cáscara es lisa, con una textura uniforme y un sutil brillo natural. No tienen manchas ni moteados, a diferencia de los huevos de gallina. La forma es ovalada, ligeramente más puntiaguda en un extremo.</p>"),
        ("Tamaño y peso", "<p>Un huevo de pavo real mide aproximadamente 6-7 cm de largo y 4-5 cm de ancho. Su peso oscila entre 70 y 90 gramos, lo que equivale a aproximadamente 1,5 veces el tamaño de un huevo de gallina grande. La hembra pone entre 3 y 6 huevos por nidada, generalmente uno cada 24-48 horas.</p>"),
        ("Período de incubación", "<p>La incubación dura entre 28 y 30 días, durante los cuales la hembra se dedica exclusivamente a empollar los huevos, abandonando el nido solo para alimentarse brevemente. El macho no participa en la incubación ni en el cuidado de los polluelos. La temperatura de incubación óptima es de 37,5 °C con una humedad del 50-60%. Para incubación artificial, se necesitan voltear los huevos al menos 3 veces al día durante los primeros 25 días.</p>"),
        ("Diferencias entre especies de pavo real", "<p>El pavo real verde (<em>Pavo muticus</em>) pone huevos de color crema a marrón claro, similares al pavo real común, pero ligeramente más pequeños (60-80 g). El pavo real del Congo (<em>Afropavo congensis</em>) pone solo 2-4 huevos por nidada, de color blanco cremoso, más redondeados que los de las otras dos especies.</p>")
    ], [
        ("¿Los huevos de pavo real se pueden comer?", "Sí, son comestibles y tienen un sabor similar al huevo de gallina, pero con una yema más grande y un sabor ligeramente más intenso. Sin embargo, no son comerciales debido a la baja producción."),
        ("¿Cuánto tarda en nacer un polluelo de pavo real?", "La incubación dura de 28 a 30 días. Los polluelos nacen cubiertos de plumón amarillento con motas marrones y pueden caminar y alimentarse por sí mismos desde el primer día."),
        ("¿Cada cuánto pone huevos un pavo real?", "La hembra pone 3-6 huevos por temporada de cría (primavera-verano), uno cada 24-48 horas. Si se retiran los huevos, puede hacer una segunda puesta."),
        ("¿El macho ayuda a incubar los huevos?", "No, solo la hembra incuba los huevos y cuida de los polluelos. El macho se dedica a cortejar a otras hembras durante la temporada.")
    ], ['aves-nativas', 'cuidados']),

    # === COMEDERO PARA AVES CON BOTELLA ===
    (9381, [
        "<p>Hacer un comedero para aves con una botella de plástico reciclada es un proyecto DIY sencillo, económico y que ayuda a las aves silvestres de tu vecindario. En esta guía paso a paso te enseñamos cómo fabricar tu propio comedero casero, qué tipo de alimento ofrecer según las especies de tu zona, y cómo mantenerlo limpio y seguro para las aves.</p>"
    ], [
        ("Materiales necesarios", "<p>Necesitarás: una botella de plástico de 1,5 a 2 litros (transparente o verde claro), tijeras o cutter, dos cucharas de madera (servirán como perchas y dosificadores de semillas), alambre o cuerda resistente para colgar, y semillas para aves (alpiste, mijo, girasol). Opcional: pintura acrílica no tóxica para decorar.</p>"),
        ("Paso a paso del comedero casero", "<p><strong>1.</strong> Lava bien la botella y retira la etiqueta. <strong>2.</strong> Haz dos agujeros enfrentados a 5 cm de la base, del tamaño del mango de la cuchara. <strong>3.</strong> Inserta una cuchara de madera atravesando ambos agujeros, con la pala hacia arriba. <strong>4.</strong> Repite el proceso 8-10 cm más arriba con la segunda cuchara, rotándola 90 grados. <strong>5.</strong> Sobre cada cuchara, agranda ligeramente el agujero para que las semillas caigan. <strong>6.</strong> Haz dos agujeros pequeños en la tapa para pasar el alambre o cuerda para colgar.</p>"),
        ("Mejores alimentos para ofrecer", "<p>Las semillas de girasol atraen a una gran variedad de aves. El alpiste es ideal para jilgueros y gorriones. El maíz partido atrae a palomas y tórtolas. Las mezclas comerciales de semillas para aves silvestres son una opción equilibrada. Durante el invierno, el sebo (grasa animal mezclada con semillas) es un excelente suplemento energético. Evita siempre el pan, las galletas y los alimentos salados.</p>"),
        ("Mantenimiento e higiene", "<p>Limpia el comedero cada 2 semanas con agua caliente y un cepillo (sin detergentes). Las semillas mojadas o enmohecidas pueden enfermar a las aves. Coloca el comedero en un lugar protegido del viento y la lluvia, a una altura mínima de 1,5 metros del suelo para evitar gatos. Idealmente cerca de un árbol o arbusto donde las aves puedan refugiarse rápidamente.</p>")
    ], [
        ("¿Qué tipo de botella es mejor?", "Las botellas de plástico PET de 1,5 a 2 litros son ideales. Las botellas de vidrio no se recomiendan por su peso y riesgo de rotura."),
        ("¿Dónde colgar el comedero?", "En un lugar tranquilo, protegido del viento, a 1,5-2 m de altura, cerca de árboles o arbustos donde las aves puedan refugiarse."),
        ("¿Cada cuánto se debe rellenar?", "Cada 2-3 días en invierno (cuando las aves necesitan más energía) y cada 4-5 días en verano. Si ves que el alimento se moja, reduce la cantidad."),
        ("¿Atraerá ratas o plagas?", "Si mantienes el comedero limpio y no dejas semillas acumuladas en el suelo, es poco probable. Recoge las cáscaras caídas y evita sobrellenarlo.")
    ], ['cuidados']),

    # === 10 MEDIDAS PARA PROTEGER ANIMALES EN PELIGRO DE EXTINCIÓN ===
    (9379, [
        "<p>Proteger a los animales en peligro de extinción es una responsabilidad de todos. Cada año, la UICN actualiza su Lista Roja de especies amenazadas, y las cifras son alarmantes: más de 41.000 especies están en peligro de desaparecer. En Chile, aves como el picaflor de Juan Fernández, el cóndor andino y el pingüino de Humboldt necesitan nuestra ayuda. Aquí te presentamos 10 medidas concretas que puedes aplicar para marcar la diferencia.</p>"
    ], [
        ("1. Reduce tu huella ecológica", "<p>El cambio climático es una de las mayores amenazas para la biodiversidad. Reduce tu consumo de energía, usa transporte público, recicla y evita plásticos de un solo uso. Cada tonelada de CO₂ que evitamos ayuda a preservar hábitats completos.</p>"),
        ("2. Apoya organizaciones de conservación", "<p>Organizaciones como WWF, BirdLife International, la Red de Observadores de Aves (ROC) en Chile, y Codeff realizan un trabajo invaluable. Puedes donar, hacerte socio o voluntario. Incluso donaciones pequeñas mensuales tienen un impacto real en proyectos de conservación.</p>"),
        ("3. No compres productos de especies protegidas", "<p>Evita comprar souvenirs de plumas, marfil, cueros exóticos o animales disecados. El tráfico ilegal de especies mueve miles de millones de dólares al año y es una de las principales causas de extinción. Si viajas, investiga qué productos están prohibidos en tu destino.</p>"),
        ("4. Crea un jardín amigable con la fauna", "<p>Planta especies nativas de tu región. Las plantas nativas atraen insectos autóctonos que sirven de alimento a las aves. Evita pesticidas y herbicidas químicos. Instala comederos y bebederos para aves, y refugios para insectos polinizadores.</p>"),
        ("5. Denuncia la caza furtiva y el tráfico ilegal", "<p>En Chile, el Servicio Agrícola y Ganadero (SAG) y Carabineros reciben denuncias. Si ves caza furtiva, tenencia ilegal de fauna silvestre o comercio irregular, llama al 134 (Carabineros) o al +56 2 2345 1100 (SAG). La Ley de Caza N° 19.473 protege a las especies amenazadas.</p>")
    ], [
        ("¿Cuál es la principal causa de extinción de animales?", "La pérdida de hábitat por deforestación, urbanización y agricultura es la principal amenaza, seguida del cambio climático, la caza furtiva y las especies invasoras."),
        ("¿Cómo puedo ayudar desde mi casa?", "Reduciendo tu consumo, reciclando, plantando especies nativas, no comprando productos ilegales, y educando a tu familia sobre la importancia de la biodiversidad."),
        ("¿Qué especies están en peligro crítico en Chile?", "El picaflor de Juan Fernández, el pingüino de Humboldt, el huemul, la ranita de Darwin y el loro tricahue, entre otras."),
        ("¿Funcionan las áreas protegidas?", "Sí, los parques nacionales y reservas naturales son la herramienta más efectiva para conservar hábitats. Chile ha protegido más del 20% de su territorio marino y terrestre.")
    ], ['especies-amenazadas', 'cuidados']),

    # === PÁJARO HERIDO: QUÉ HACER ===
    (9348, [
        "<p>Encontrar un pájaro herido puede ser angustiante, pero saber cómo actuar correctamente puede marcar la diferencia entre la vida y la muerte. Antes de intervenir, evalúa la situación: no todos los pájaros en el suelo necesitan ayuda. Los volantones (aves jóvenes aprendiendo a volar) suelen estar sanos y sus padres los cuidan desde cerca. Esta guía te explica paso a paso qué hacer con un pájaro herido.</p>"
    ], [
        ("Primero: evalúa si realmente necesita ayuda", "<p>Si el pájaro tiene plumas completas, salta y aletea, probablemente es un volantón aprendiendo a volar. Sus padres están cerca alimentándolo. Solo debes intervenir si: está visiblemente herido (sangre, ala caída), no se mueve ni intenta huir al acercarte, ha sido atacado por un gato o perro, o está en un lugar peligroso (calle, obra).</p>"),
        ("Cómo atrapar y contener al ave", "<p>Usa guantes (o tus manos limpias si no tienes). Acércate lentamente y cúbrelo con una toalla o paño suave para reducir su estrés. Sujeta el cuerpo con firmeza pero sin apretar, permitiendo que las alas queden pegadas al cuerpo. Coloca el ave en una caja de cartón con orificios de ventilación, forrada con papel absorbente. Mantén la caja en un lugar oscuro, cálido y silencioso.</p>"),
        ("Primeros auxilios básicos", "<p>Si hay sangrado activo, aplica presión suave con una gasa estéril hasta que se detenga. No uses alcohol ni agua oxigenada. Limpia heridas superficiales solo con suero fisiológico. No intentes entablillar un ala rota tú mismo. No le des comida ni agua hasta que un profesional lo evalúe. Ofrecer alimento incorrecto puede matarlo.</p>"),
        ("Contacta a un profesional", "<p>Busca el centro de recuperación de fauna silvestre más cercano. En Chile, el Servicio Agrícola y Ganadero (SAG) coordina centros de rescate en cada región. También puedes contactar a la Red de Observadores de Aves (ROC) para orientación. Llama al 112 o al SAG (+56 2 2345 1100) para que te deriven al centro más cercano.</p>")
    ], [
        ("¿Debo darle agua a un pájaro herido?", "No. Ofrecer agua o comida a un pájaro herido puede provocar que se ahogue o que aspire líquido a los pulmones. Espera a que un veterinario lo evalúe."),
        ("¿Qué hago si no hay centro de recuperación cerca?", "Contacta a un veterinario de animales exóticos o pequeños animales. Como último recurso, puedes seguir las instrucciones telefónicas de un especialista mientras organizas el traslado."),
        ("¿Puedo curar un pájaro herido en casa?", "Solo como medida temporal (máximo 24 horas) mientras contactas a un profesional. Las aves silvestres se estresan mucho en cautiverio y pueden morir incluso sin lesiones graves."),
        ("¿Los gatos siempre matan a los pájaros?", "Las heridas de gato son extremadamente peligrosas por las bacterias en su saliva. Incluso si el pájaro parece ileso, necesita antibióticos veterinarios. Una mordedura de gato sin tratamiento causa infección fatal en 48-72 horas.")
    ], ['aves-nativas', 'cuidados']),
]

# Process all rewrites
print(f'Rewriting {len(rewrites)} posts...')
for i, (pid, intros, sections, faqs, extra_tags) in enumerate(rewrites):
    # Build content
    full_content = BANNER
    for p in intros:
        full_content += p
    for heading, body in sections:
        full_content += f'<h2>{heading}</h2>{body}'
    
    # Add FAQ section
    if faqs:
        full_content += '<h2>Preguntas frecuentes</h2>'
        for q, a in faqs:
            full_content += f'<h3>{q}</h3><p>{a}</p>'
        full_content += make_faq_schema(faqs)
    
    full_content += AFF
    
    # Build tags
    tags = [TAG_CUIDADOS]
    for et in extra_tags:
        tid = tag_id(et)
        if tid:
            tags.append(tid)
    tags = list(set(t for t in tags if t))
    
    # Update post
    if update_post(pid, full_content, tags):
        print(f'  OK {pid}')
    else:
        print(f'  FAIL {pid}')
    if i % 3 == 0:
        time.sleep(1)

print('Batch 1 done')
