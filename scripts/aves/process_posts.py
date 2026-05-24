"""
Procesa todos los posts del cluster de gaviotas:
1. Agrega link contextual a la pagina pilar con variacion semantica
2. Agrega FAQPage JSON-LD schema
3. Escribe output en directorio posts-output/
"""

PILLAR_URL = "https://avesnativaschilenas.cl/gaviotas-de-chile-guia-completa/"
PILLAR_DIR = "gaviota-posts-raw"
OUTPUT_DIR = "posts-output"

import os
import re
import json

# ========== PILAR LINK VARIANTS ==========
# (anchor_text, sentence_template)
PILLAR_LINKS = [
    f'<a href="{PILLAR_URL}">todas las especies de gaviotas de Chile</a>',
    f'<a href="{PILLAR_URL}">guia completa de gaviotas chilenas</a>',
    f'<a href="{PILLAR_URL}">las gaviotas que habitan en Chile</a>',
    f'<a href="{PILLAR_URL}">la guia definitiva de gaviotas de Chile</a>',
    f'<a href="{PILLAR_URL}">las distintas especies de gaviotas chilenas</a>',
    f'<a href="{PILLAR_URL}">nuestra guia completa sobre gaviotas en Chile</a>',
    f'<a href="{PILLAR_URL}">el mundo de las gaviotas de Chile</a>',
    f'<a href="{PILLAR_URL}">la diversidad de gaviotas en Chile</a>',
]

# ========== FAQ DATA per post ==========

FAQ_DATA = {
    "gaviota-cahuil": {
        "link_idx": 0,
        "faq": [
            ("Que es la gaviota cahuil y donde se encuentra en Chile?",
             "La gaviota cahuil (Chroicocephalus maculipennis) es una especie emblematica de la costa central y sur de Chile. Se encuentra desde Valparaiso hasta Magallanes todo el ano, y en invierno migra hacia el norte, llegando incluso hasta Arica y Brasil."),
            ("Cual es la mejor epoca para observar la gaviota cahuil?",
             "La mejor epoca para el avistamiento es entre primavera y verano (octubre a marzo), cuando estan en pleno periodo reproductivo. Los mejores horarios son temprano por la manana o al atardecer, especialmente en humedales como Cahuil, El Yali o Batuco."),
            ("Como identificar a la gaviota cahuil?",
             "Mide unos 42 cm, mas pequena que otras gaviotas. En epoca reproductiva presenta cabeza y garganta cafe oscuro con un semicirculo blanco detras del ojo, y pico y patas rojizos. En invierno la cabeza se aclara pero conserva el anillo ocular blanco."),
            ("Donde nidifica la gaviota cahuil?",
             "Nidifica en colonias flotantes, construyendo sus nidos sobre juncos o vegetacion acuatica en lagunas y humedales. La epoca de cria es entre noviembre y diciembre, con una nidada promedio de 3 a 4 huevos."),
            ("Por que la gaviota cahuil es importante para el ecosistema?",
             "Actua como bioindicador de la salud ambiental: su presencia en humedales y estuarios senala que el ecosistema esta sano. Ademas, depura el ambiente al alimentarse de residuos organicos y ayuda a controlar poblaciones de pequenos animales.")
        ]
    },
    "gaviota-de-franklin": {
        "link_idx": 1,
        "faq": [
            ("Que es la gaviota de Franklin y donde habita?",
             "La gaviota de Franklin (Larus pipixcan) es una especie migratoria que se reproduce en America del Norte y viaja hacia la costa de America del Sur durante el invierno. Habita en costas, lagos interiores y estuarios."),
            ("Cuales son las caracteristicas distintivas de la gaviota de Franklin?",
             "Es de tamano mediano con una envergadura de 100 a 110 cm. En epoca de cria su cabeza se torna negra con plumaje blanco y gris. En invierno la cabeza se blanquea. Tiene un pico corto y robusto ideal para capturar peces pequenos e insectos."),
            ("Que dieta tiene la gaviota de Franklin?",
             "Es omnivora: se alimenta de peces pequenos, crustaceos, insectos, larvas y restos de alimentos dejados por humanos. Suelen forrajear en grupos para localizar alimento mas eficientemente."),
            ("Como es el ciclo reproductivo de la gaviota de Franklin?",
             "La reproduccion ocurre entre mayo y junio. Construyen nidos simples en costas o vegetacion baja. Ponen de 2 a 3 huevos que incuban durante 3 semanas. Los polluelos son nidifugos y pueden moverse rapidamente."),
            ("Que importancia ecologica tiene la gaviota de Franklin?",
             "Actua como indicador de salud ambiental y controla poblaciones de peces pequenos e invertebrados. Su migracion entre America del Norte y del Sur facilita el intercambio de nutrientes entre ecosistemas distantes.")
        ]
    },
    "gaviota-dominicana": {
        "link_idx": 2,
        "faq": [
            ("Que es la gaviota dominicana y donde vive?",
             "La gaviota dominicana (Larus dominicanus) es una especie que habita en ecosistemas costeros del Caribe, incluyendo playas, estuarios y areas portuarias. Es un simbolo de la biodiversidad de la region."),
            ("Como identificar a la gaviota dominicana?",
             "Se distingue por su plumaje mayormente blanco con un caracteristico manto gris y cabeza blanca que se torna marron durante la cria. Su pico es corto y robusto, adaptado para alimentarse de peces y desechos marinos."),
            ("Que impacto tiene la gaviota dominicana en el ecosistema?",
             "Actua como indicador de salud ambiental, controla poblaciones de peces y crustaceos, contribuye al ciclo de nutrientes al alimentarse de restos marinos, y ayuda a mantener el equilibrio ecologico costero."),
            ("Cuando es la mejor epoca para observar gaviotas dominicanas?",
             "La mejor epoca es de noviembre a febrero, cuando se concentran en las costas durante el invierno. Tambien entre agosto y octubre, que coincide con la migracion de algunas especies."),
            ("Cuales son las principales amenazas para la gaviota dominicana?",
             "Enfrenta la perdida de habitat por urbanizacion costera, contaminacion marina, cambio climatico y eventos meteorologicos extremos como huracanes que destruyen sus colonias de anidacion.")
        ]
    },
    "gaviota-garuma": {
        "link_idx": 3,
        "faq": [
            ("Que es la gaviota garuma y donde habita?",
             "La gaviota garuma es una especie que habita desde el centro de Ecuador hasta el sur de Chile. Es unica porque se adapta al desierto de Atacama, uno de los ambientes mas aridos del planeta, donde nidifica lejos de la costa."),
            ("Cuales son las caracteristicas de la gaviota garuma?",
             "Mide entre 42 y 44 cm de longitud. Su plumaje es principalmente gris con cabeza blanca en verano. En vuelo se distingue por una banda blanca en las plumas secundarias de sus alas. Es conocida por su elegante vuelo y habilidades para la pesca."),
            ("Como se reproduce la gaviota garuma?",
             "Durante la reproduccion, los adultos cambian su plumaje a cabeza blanca y viajan al interior del desierto para nidificar. La hembra pone 1 o 2 huevos que ambos padres incuban alternadamente mientras el otro viaja a la costa por alimento."),
            ("De que se alimenta la gaviota garuma?",
             "Su dieta consiste principalmente en pulgones marinos y algunos peces. Los polluelos dependen de los viajes diarios de sus padres para obtener alimento hasta que desarrollan sus habilidades de vuelo."),
            ("Por que es importante la gaviota garuma en el ecosistema?",
             "Contribuye al equilibrio de la cadena alimenticia marina, controla poblaciones de presas marinas, participa en el reciclaje de nutrientes al consumir carrona, y su poblacion refleja la salud del ecosistema costero.")
        ]
    },
    "gaviota-peruana": {
        "link_idx": 4,
        "faq": [
            ("Que es la gaviota peruana y donde se encuentra?",
             "La gaviota peruana (Larus belcheri) es una especie emblematica de las costas del Pacifico, especialmente reconocida en el norte de Chile. Forma parte del paisaje marino y es conocida por su aspecto distintivo y su caracteristico canto."),
            ("Cuales son las caracteristicas de la gaviota peruana?",
             "Es una especie adaptada a las costas del Pacifico, con un plumaje que la distingue de otras gaviotas de la region. Habita en zonas costeras rocosas y playas arenosas del norte de Chile."),
            ("De que se alimenta la gaviota peruana?",
             "Se alimenta principalmente de peces pequenos, crustaceos y otros invertebrados marinos que encuentra en las costas del Pacifico. Es una especie oportunista que aprovecha los recursos disponibles en su habitat."),
            ("Donde se puede observar la gaviota peruana en Chile?",
             "Se puede observar en las costas del norte de Chile, especialmente en zonas rocosas y playas arenosas. Es una especie residente que forma parte del paisaje marino de la region."),
            ("Que papel ecologico cumple la gaviota peruana?",
             "Actua como indicador de la salud de los ecosistemas marinos costeros, ayuda a controlar poblaciones de peces e invertebrados, y contribuye al equilibrio de la cadena trofica en el Pacifico.")
        ]
    },
    "gaviotin-elegante": {
        "link_idx": 5,
        "faq": [
            ("Que es el gaviotin elegante y donde habita?",
             "El gaviotin elegante (Thalasseus elegans) es un ave marina que habita en costas rocosas y acantilados del oceano Pacifico. Se reproduce en regiones costeras de California y Mexico durante primavera y verano."),
            ("Cuales son las caracteristicas del gaviotin elegante?",
             "Mide unos 30 cm de longitud, con plumaje blanco y negro, alas delgadas y puntiagudas ideales para la caza. Su elegante vuelo es un espectaculo, y sus llamadas agudas y distintivas anaden encanto a su presencia en las costas."),
            ("Como se alimenta el gaviotin elegante?",
             "Su dieta se basa en peces pequenos, crustaceos y organismos marinos. Se zambulle en picado desde alturas considerables para atrapar presas con precision, demostrando su adaptacion al entorno acuatico."),
            ("Cuales son las principales amenazas del gaviotin elegante?",
             "Enfrenta la degradacion de habitats costeros, contaminacion marina e interferencias humanas en areas de anidacion. La proteccion de areas clave es fundamental para su supervivencia a largo plazo."),
            ("Por que es importante ecologicamente el gaviotin elegante?",
             "Actua como depredador de especies de peces que pueden desequilibrar poblaciones locales. Su presencia y actividades reproductivas son indicadores de la salud general de los habitats marinos costeros.")
        ]
    },
    "gaviotin-monja": {
        "link_idx": 6,
        "faq": [
            ("Que es el gaviotin monja y donde vive?",
             "El gaviotin monja es un ave marina distintiva de la costa chilena, reconocible por su plumaje gris oscuro y su pico y patas rojas. Las plumas blancas que se extienden desde la base del pico le dan un aspecto sofisticado."),
            ("Cuales son las caracteristicas del gaviotin monja?",
             "Su plumaje gris oscuro combinado con pico y patas rojas lo hacen inconfundible. Los juveniles tienen un plumaje mas pardusco sin las plumas blancas caracteristicas. Es conocido por su elegante vuelo sobre las costas chilenas."),
            ("Donde se puede observar el gaviotin monja en Chile?",
             "Se encuentra principalmente en las costas de Chile, donde habita en zonas rocosas y acantilados. Es una de las aves mas cautivadoras del mundo marino chileno."),
            ("Como se reproduce el gaviotin monja?",
             "Forma colonias durante la temporada de reproduccion en areas protegidas de la costa. Construye nidos en zonas elevadas para proteger a sus crias de depredadores y las marejadas."),
            ("Que papel ecologico cumple el gaviotin monja?",
             "Es un importante depredador en el ecosistema marino costero, ayudando a mantener el equilibrio de las poblaciones de peces y otras especies marinas en las costas de Chile.")
        ]
    },
    "gaviotin-sudamericano": {
        "link_idx": 7,
        "faq": [
            ("Que es el gaviotin sudamericano y donde habita?",
             "El gaviotin sudamericano es un ave migratoria que habita en costas arenosas, humedales y areas costeras de America del Sur. Se distingue por su esbelta silueta y su plumaje blanco con cabeza negra en epoca de cria."),
            ("Cuales son las caracteristicas del gaviotin sudamericano?",
             "Tiene una envergadura de alas de 80 a 90 cm. Su plumaje varia entre blanco, negro y gris, con un caracteristico 'sombrero negro' en la cabeza durante la epoca de cria. Es conocido por sus acrobacias aereas y su habilidad para la pesca."),
            ("Cuando es la epoca de reproduccion del gaviotin sudamericano?",
             "La epoca de cria ocurre entre septiembre y diciembre. Prefiere islas deshabitadas o playas remotas para anidar, donde forma colonias que le proporcionan mayor proteccion contra depredadores."),
            ("Que tecnicas de caza utiliza el gaviotin sudamericano?",
             "Utiliza diversas tecnicas: pesca en vuelo atrapando peces desde el aire, zambullidas lanzandose al agua, y observacion desde puntos elevados antes de lanzarse a capturar su presa."),
            ("Cuan lejos migra el gaviotin sudamericano?",
             "Tiene una notable capacidad de migracion, viajando miles de kilometros entre areas de alimentacion y reproduccion. Puede alcanzar velocidades de hasta 60 km/h durante sus travesias.")
        ]
    },
    "guanay": {
        "link_idx": 0,
        "faq": [
            ("Que es el guanay y donde habita?",
             "El guanay es un ave marina que habita principalmente en las costas de Peru y Chile. Su presencia es un indicador vital de la salud de los ecosistemas marinos, adaptandose a un entorno lleno de recursos en el Pacifico."),
            ("Cuales son las caracteristicas fisicas del guanay?",
             "Posee un cuerpo aerodinamico con alas largas y estrechas para vuelos prolongados. Su plumaje impermeable y de tonos oscuros le proporciona camuflaje. Tiene un pico robusto adaptado para la pesca y una vision aguda optimizada para el agua."),
            ("De que se alimenta el guanay?",
             "Su dieta se basa principalmente en peces como anchovetas, jureles y sardinas. Utiliza su agudo sentido de la vista para localizar presas mientras vuela sobre el agua, sumergiendose a profundidades considerables."),
            ("Donde anida el guanay?",
             "Anida en colonias masivas en zonas rocosas y acantilados costeros, aprovechando la seguridad que les brinda la altura. Prefiere areas con abundancia de recursos marinos, como bancos de peces cerca de la costa."),
            ("Por que es importante el guanay para el ecosistema marino?",
             "Actua como indicador de la salud del ecosistema marino. Sus colonias reflejan la abundancia de biodiversidad en la region. Ayuda a regular las poblaciones de peces y contribuye al equilibrio ecologico del Pacifico.")
        ]
    },
    "gaviota-andina": {
        "link_idx": 1,
        "faq": [
            ("Que es la gaviota andina y donde vive?",
             "La gaviota andina (Chroicocephalus serranus) es un simbolo de las alturas de los Andes chilenos. Esta especie de tamano mediano se ha adaptado a la vida en las montanas, habitando en lagos y zonas humedas de alta montana."),
            ("Cuales son las caracteristicas distintivas de la gaviota andina?",
             "Durante la epoca reproductiva luce un caracteristico capuchon negruzco que la hace inconfundible en el paisaje montanoso. Pertenece a la familia Laridae y al orden Charadriiformes, y su nombre en ingles es Andean Gull."),
            ("Donde se distribuye la gaviota andina?",
             "Se distribuye a lo largo de la cordillera de los Andes en Chile, habitando en lagos de altura, humedales y zonas montanosas. Es una especie adaptada a las aguas frias y a los paisajes imponentes de la alta montana."),
            ("Como se alimenta la gaviota andina?",
             "Se alimenta de peces pequenos, invertebrados acuaticos y otros organismos que encuentra en los lagos y humedales de altura. Su adaptacion a las condiciones extremas de los Andes la hace una especie unica entre las gaviotas."),
            ("Por que es importante conservar la gaviota andina?",
             "Es una especie indicadora de la salud de los ecosistemas de altura en los Andes. Su presencia refleja la calidad de los humedales y lagos de montana, ecosistemas fragiles y fundamentales para el equilibrio hidrico de la region.")
        ]
    },
    "gaviota-pacifico": {
        "link_idx": 2,
        "faq": [
            ("Que es la gaviota del Pacifico y donde habita?",
             "La gaviota del Pacifico (Larus pacificus) es una de las gaviotas mas imponentes de las costas chilenas. Habita en costas e islas del oceano Pacifico, prefiriendo playas solitarias y estuarios."),
            ("Cuales son las caracteristicas de la gaviota del Pacifico?",
             "Su envergadura alcanza hasta 150 cm, siendo una de las gaviotas mas grandes. Su plumaje es mayormente blanco con alas grises, y su pico es amarillo con un punto rojo en la parte inferior, el mas grande entre todas las gaviotas."),
            ("Que tamano tiene la gaviota del Pacifico?",
             "Con una envergadura de hasta 150 cm y un pico robusto de color amarillo con punto rojo, es una de las gaviotas mas imponentes. Sus patas son rosadas y robustas, dandole un toque distintivo y elegante."),
            ("Donde se puede observar la gaviota del Pacifico en Chile?",
             "Se encuentra en las costas e islas del oceano Pacifico chileno, prefiriendo areas costeras solitarias, playas y estuarios donde el horizonte se pierde en el azul del agua."),
            ("Por que es importante la gaviota del Pacifico en el ecosistema?",
             "Como una de las gaviotas mas grandes, juega un papel importante en la cadena trofica costera. Su presencia es un simbolo de la diversidad natural que alberga el oceano Pacifico en las costas de Chile.")
        ]
    },
    "como-saber-si-una-gaviota-es-macho-o-hembra": {
        "link_idx": 3,
        "faq": [
            ("Como saber si una gaviota es macho o hembra?",
             "Identificar el sexo de una gaviota puede ser un desafio ya que muchas especies presentan caracteristicas similares. Sin embargo, los machos suelen ser ligeramente mas grandes que las hembras, y durante la epoca de apareamiento los machos tienen plumajes mas intensos y comportamientos mas agresivos y territoriales."),
            ("Cuales son las diferencias entre gaviota macho y hembra?",
             "Los machos son ligeramente mas grandes y robustos, con plumajes mas brillantes durante la epoca de cria. Las hembras son un poco mas pequenas y pueden tener plumajes menos brillantes. Los machos son mas vocales y realizan danzas nupciales."),
            ("Cuantos anos vive una gaviota?",
             "La esperanza de vida de una gaviota varia entre 10 y 30 anos segun la especie. En ambientes protegidos pueden alcanzar hasta 40 anos. Factores como el habitat, disponibilidad de alimento y presencia de depredadores influyen en su longevidad."),
            ("Como es el ciclo de vida de una gaviota?",
             "Comienza con la incubacion de 2 a 4 huevos durante 24 a 30 dias. Los polluelos nacen cubiertos de plumon y dependen de sus padres. La etapa juvenil tiene plumaje marron moteado que cambia gradualmente hasta alcanzar el plumaje adulto a los 3 o 4 anos."),
            ("Como es el comportamiento social de las gaviotas?",
             "Son aves muy sociales que viven en grupos. Durante el cortejo, los machos realizan displays como vocalizaciones y movimientos de alas para atraer a las hembras. Ambos padres participan en la incubacion y cuidado de las crias.")
        ]
    },
    "gaviotas-en-zonas-urbanas": {
        "link_idx": 4,
        "faq": [
            ("Por que las gaviotas viven en zonas urbanas?",
             "Las gaviotas han encontrado en las ciudades un nuevo habitat que les ofrece recursos abundantes, especialmente desechos alimentarios generados por la actividad humana. Su adaptabilidad les permite prosperar en parques, azoteas y puertos."),
            ("Como se alimentan las gaviotas en las ciudades?",
             "Su dieta urbana incluye restos de comida en basureros, alimentos que las personas les arrojan en parques, e insectos y pequenos animales que prosperan en entornos urbanos. Son oportunistas y se han adaptado a reconocer patrones humanos."),
            ("Que impacto positivo tienen las gaviotas en el ecosistema urbano?",
             "Actuan como controladoras de desechos al alimentarse de restos de comida, contribuyen al control natural de plagas al consumir roedores e insectos, y aportan a la biodiversidad urbana introduciendo nuevas interacciones ecologicas."),
            ("Cuales son los desafios de la convivencia con gaviotas urbanas?",
             "La creciente poblacion de gaviotas puede generar conflictos como competencia por alimentos, contaminacion acustica, y desplazamiento de otras aves nativas. Es importante encontrar un equilibrio en la coexistencia."),
            ("Como se puede conservar a las gaviotas en entornos urbanos?",
             "Mediante educacion ambiental, manejo adecuado de residuos, creacion de areas verdes como refugios seguros para anidacion, y colaboracion entre organizaciones locales, comunidades y autoridades para desarrollar politicas de conservacion.")
        ]
    },
    "depredadores-de-las-gaviotas-que-amenazas-enfrentan": {
        "link_idx": 5,
        "faq": [
            ("Cuales son los principales depredadores de las gaviotas?",
             "Las gaviotas enfrentan depredadores como aves rapaces, mamiferos terrestres (gatos, perros, ratas) que atacan huevos y polluelos, y otros animales. Tambien enfrentan amenazas humanas como la destruccion de habitats y la contaminacion."),
            ("Por que las gaviotas gritan de noche?",
             "Las gaviotas emiten sonidos nocturnos principalmente para marcar su territorio, comunicarse con otros miembros de la colonia, y como respuesta al estres ambiental por la presencia de depredadores, humanos u otras aves."),
            ("Donde viven las gaviotas?",
             "Las gaviotas habitan cerca de cuerpos de agua como costas, lagos, rios y areas urbanas. Anidan en acantilados, playas, zonas rocosas y tambien en estructuras artificiales como techos y puentes en ciudades."),
            ("De que se alimentan las gaviotas?",
             "Tienen una dieta variada que incluye peces, crustaceos, insectos y restos de comida humana. Son aves oportunistas que se adaptan a los recursos disponibles en su entorno, desde el mar hasta las zonas urbanas."),
            ("Donde mueren las gaviotas?",
             "Las gaviotas mueren en diversos lugares: en las costas (polluelos que caen de nidos o son depredados), en el agua (por ahogamiento, intoxicacion o accidentes), y por colisiones con infraestructuras humanas, caza o condiciones climaticas extremas.")
        ]
    },
    "historia-cultural-de-las-gaviotas-simbolos-de-libertad-y-mar": {
        "link_idx": 6,
        "faq": [
            ("Que simbolizan las gaviotas culturalmente?",
             "Las gaviotas son simbolos universales de libertad y conexion con el mar. Han sido emblemas de esperanza y renovacion en muchas civilizaciones, inspirando a poetas, artistas y narradores a capturar su esencia en obras memorables."),
            ("Que significa ver una gaviota blanca volando?",
             "Ver una gaviota blanca volando simboliza libertad, esperanza y renovacion. Su color blanco asociado con la pureza sugiere un mensaje positivo: que siempre hay oportunidad de renacer. Espiritualmente, puede representar la conexion entre el mundo material y espiritual."),
            ("Que significado tiene una pluma de gaviota?",
             "La pluma de gaviota simboliza la libertad y la conexion con la naturaleza. Espiritualmente se considera portadora de mensajes del mas alla, representando no solo la libertad fisica sino tambien una liberacion emocional y espiritual."),
            ("Como aparece la gaviota en el arte y la literatura?",
             "La gaviota ha inspirado obras como 'La gaviota' de Anton Chekhov y 'Jonathan Livingston Seagull' de Richard Bach. Artistas como Turner y Homer la han incorporado en sus obras para transmitir la inmensidad del oceano y el espiritu indomable de la naturaleza."),
            ("Que significa escuchar gaviotas en la noche?",
             "Escuchar gaviotas en la noche puede responder a la busqueda de pareja durante la reproduccion, defensa del territorio, o comunicacion entre miembros de la colonia. Mas alla de mitos, su canto nocturno evoca la conexion con el mar y la naturaleza.")
        ]
    },
    "la-vida-secreta-de-las-aves-marinas": {
        "link_idx": 7,
        "faq": [
            ("Que son las aves marinas?",
             "Las aves marinas son un grupo diverso de aves adaptadas a la vida oceanica, incluyendo gaviotas, albatros, pinguinos y petreles. Se caracterizan por sus alas largas para planear sobre las olas y glandulas especiales para eliminar el exceso de sal."),
            ("Cuales son los habitats de las aves marinas?",
             "Las aves marinas habitan en costas ricas en biodiversidad, islas donde anidan seguras de depredadores terrestres, acantilados costeros ideales para el anidamiento, y oceanos abiertos donde pasan gran parte de su vida buscando alimento."),
            ("Como cazan las aves marinas?",
             "Utilizan diversas tecnicas: el buceo profundo como los pinguinos, el vuelo planeado como los albatros para recorrer grandes distancias, la caza en grupo para mayor eficacia, y el forrajeo oportunista como las gaviotas."),
            ("Por que son importantes las aves marinas en el ecosistema?",
             "Actuan como indicadores de la salud del oceano, regulan las poblaciones de peces, distribuyen nutrientes a traves de sus excrementos que fertilizan el agua, y contribuyen a la biodiversidad marina."),
            ("Cuales son las principales amenazas para las aves marinas?",
             "Las aves marinas enfrentan sobrepesca que reduce su alimento, cambio climatico que altera sus habitats, contaminacion por plasticos y quimicos, y destruccion de habitats costeros por urbanizacion. La colaboracion entre gobiernos y comunidades es clave para su proteccion.")
        ]
    }
}

# ========== POST ORDER ==========
POSTS = [
    "108_gaviota-cahuil.txt",
    "110_gaviota-de-franklin.txt",
    "112_gaviota-dominicana.txt",
    "116_gaviota-garuma.txt",
    "118_gaviota-peruana.txt",
    "122_gaviotin-elegante.txt",
    "124_gaviotin-monja.txt",
    "128_gaviotin-sudamericano.txt",
    "136_guanay.txt",
    "3021_gaviota-andina.txt",
    "3026_gaviota-pacifico.txt",
    "9453_como-saber-si-una-gaviota-es-macho-o-hembra.txt",
    "9743_gaviotas-en-zonas-urbanas.txt",
    "9744_depredadores-de-las-gaviotas-que-amenazas-enfrentan.txt",
    "9747_historia-cultural-de-las-gaviotas-simbolos-de-libertad-y-mar.txt",
    "11042_la-vida-secreta-de-las-aves-marinas.txt",
]

def extract_slug_and_link(content):
    """Extract SLUG and LINK from post header."""
    slug = ""
    link = ""
    for line in content.split("\n"):
        if line.startswith("SLUG: "):
            slug = line.replace("SLUG: ", "").strip()
        if line.startswith("LINK: "):
            link = line.replace("LINK: ", "").strip()
    return slug, link

def build_faq_schema(faq_list):
    """Build FAQPage JSON-LD schema."""
    main_entity = []
    for question, answer in faq_list:
        main_entity.append({
            "@type": "Question",
            "name": question,
            "acceptedAnswer": {
                "@type": "Answer",
                "text": answer
            }
        })
    schema = {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": main_entity
    }
    return json.dumps(schema, ensure_ascii=False, indent=2)

def get_pillar_link(link_idx):
    """Get the pillar link HTML."""
    idx = link_idx % len(PILLAR_LINKS)
    return PILLAR_LINKS[idx]

def get_pillar_sentence(link_idx):
    """Get a natural sentence with the pillar link."""
    links = [
        f"Si te interesa conocer mas, no dejes de visitar nuestra {get_pillar_link(0)}.",
        f"Para profundizar en el tema, te recomendamos revisar nuestra {get_pillar_link(1)}.",
        f"Descubre mas informacion sobre {get_pillar_link(2)}.",
        f"Amplia tus conocimientos con {get_pillar_link(3)}.",
        f"No te pierdas {get_pillar_link(4)}.",
        f"Para seguir aprendiendo, visita {get_pillar_link(5)}.",
        f"Conoce en detalle {get_pillar_link(6)}.",
        f"Explora {get_pillar_link(7)}.",
    ]
    return links[link_idx % len(links)]

def strip_existing_faq(content):
    """Remove any existing FAQPage JSON-LD schemas from content."""
    # Remove <script type="application/ld+json">...</script> blocks that contain FAQPage
    return re.sub(
        r'<script type="application/ld\+json">\s*\{\s*"@context":\s*"https://schema\.org",\s*"@type":\s*"FAQPage".*?</script>\s*',
        '',
        content,
        flags=re.DOTALL
    )

def insert_faq_before_first_h2(content, faq_schema):
    """Insert FAQ schema as JSON-LD script right before the first <h2> tag."""
    # First strip any existing FAQ schemas
    content = strip_existing_faq(content)
    schema_tag = f'<script type="application/ld+json">\n{faq_schema}\n</script>\n\n'
    match = re.search(r'<h2[\s>]', content)
    if match:
        pos = match.start()
        content = content[:pos] + schema_tag + content[pos:]
        return content
    # If no h2, append before closing content
    return content + "\n" + schema_tag

def append_pillar_link_before_end(content, pillar_sentence):
    """Append pillar link sentence before the last closing tags or at the end of content."""
    # Insert before last </article> or before affiliate recommendation or at end
    # Look for affiliate div or closing patterns
    affiliate_match = re.search(r'<div class="afiliado-recomendacion"', content)
    if affiliate_match:
        pos = affiliate_match.start()
        return content[:pos] + f"\n<p>{pillar_sentence}</p>\n" + content[pos:]
    # Look for content-cluster
    cluster_match = re.search(r'<div class="content-cluster', content)
    if cluster_match:
        pos = cluster_match.start()
        return content[:pos] + f"\n<p>{pillar_sentence}</p>\n" + content[pos:]
    # Look for </article>
    article_match = re.search(r'</article>', content)
    if article_match:
        pos = article_match.start()
        return content[:pos] + f"\n<p>{pillar_sentence}</p>\n" + content[pos:]
    # Check if there's a closing </div> or similar pattern near the end
    # Just append before last 50 chars
    return content.rstrip() + f"\n\n<p>{pillar_sentence}</p>\n"

def process_post(filename):
    filepath = os.path.join(PILLAR_DIR, filename)
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    slug, _ = extract_slug_and_link(content)
    
    # Find FAQ data
    faq_key = None
    for key in FAQ_DATA:
        if key in slug or key.replace("-", "_") in slug:
            faq_key = key
            break
    
    if slug in FAQ_DATA:
        faq_key = slug
    
    # If no exact match, try by partial filename
    if not faq_key:
        for key in FAQ_DATA:
            if key in filename:
                faq_key = key
                break
    
    if not faq_key:
        print(f"  WARNING: No FAQ data found for {filename}")
        return
    
    faq_info = FAQ_DATA[faq_key]
    link_idx = faq_info["link_idx"]
    faq_list = faq_info["faq"]
    
    # Build FAQ schema
    faq_schema = build_faq_schema(faq_list)
    
    # Insert FAQ schema
    content = insert_faq_before_first_h2(content, faq_schema)
    
    # Add pillar link
    pillar_sentence = get_pillar_sentence(link_idx)
    content = append_pillar_link_before_end(content, pillar_sentence)
    
    # Write output
    os.makedirs(OUTPUT_DIR, exist_ok=True)
    outpath = os.path.join(OUTPUT_DIR, filename)
    with open(outpath, 'w', encoding='utf-8') as f:
        f.write(content)
    
    print(f"  OK: {filename} -> {outpath}")

def main():
    print("Procesando posts del cluster de gaviotas...")
    print(f"Pilar: {PILLAR_URL}")
    print()
    
    for post_file in POSTS:
        print(f"Procesando: {post_file}")
        process_post(post_file)
    
    print()
    print(f"Completado. Todos los archivos en '{OUTPUT_DIR}/'")

if __name__ == "__main__":
    main()
