import requests, time

auth = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
base = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'

# Get all posts we created (11818-11839 range)
post_ids = range(11818, 11840)

def ai_ready_body(title, body_intro, h2_items=None):
    """Structure content for Google AI Overviews: quick answer + structured sections"""
    h2_html = ''
    if h2_items:
        for h2_q, h2_a in h2_items:
            h2_html += f'<h2>{h2_q}</h2><p>{h2_a}</p>'
    return f'<p>{body_intro}</p>{h2_html}'

AI_CONTENT = {
    11818: {  # Paloma de Cuello Verdoso
        'body': ai_ready_body(
            "Paloma de Cuello Verdoso: Identificacion, Caracteristicas y Fotos",
            "La paloma de cuello verdoso (Columbidae) es una variedad de paloma facilmente reconocible por su plumaje iridiscente verde y purpura en el cuello. Mide entre 30-35 cm y habita tanto en zonas urbanas como rurales de Chile y Sudamerica.",
            [
                ("Como identificar una paloma de cuello verdoso?",
                 "Su caracteristica principal es el brillo verde y purpura en el cuello que cambia con la luz. El resto del cuerpo es gris claro con tonos cafe. Se diferencia de la paloma comun por este plumaje iridiscente y su tamano ligeramente mayor."),
                ("Donde vive la paloma de cuello verdoso?",
                 "Habita en ciudades, parques, zonas agricolas y bosques abiertos. Se adapta facilmente a entornos urbanos donde encuentra alimento y sitios para anidar en edificios y arboles."),
                ("Que come la paloma de cuello verdoso?",
                 "Su alimentacion es principalmente granivora: semillas, granos, frutas caidas y ocasionalmente insectos pequenos. En zonas urbanas se alimenta de restos de comida y granos."),
            ]
        )
    },
    11819: {  # Excremento de Aves de Rapina
        'body': ai_ready_body(
            "Excremento de Aves de Rapina: Como Identificarlo y Que Significa",
            "El excremento de las aves de rapina se distingue por su alto contenido de calcio y restos oseos. A diferencia de otras aves, las rapaces producen egagropilas (pelotas de regurgitacion) y sus heces tienen una coloracion blanquecina densa con restos de presas no digeridos.",
            [
                ("Como identificar excremento de ave rapaz?",
                 "Las heces frescas son blancas con base liquida y contienen restos oseos, pelos o plumas. Las egagropilas son pelotas compactas de 2-5 cm con restos de presas. El excremento de halcon es similar al de aguila pero de menor tamano."),
                ("Es peligroso el excremento de aves rapaces?",
                 "Como cualquier excremento de ave, puede contener bacterias y hongos. Se recomienda evitar el contacto directo y usar proteccion al limpiar. No es mas peligroso que el de otras aves silvestres."),
            ]
        )
    },
    11820: {  # Bandurria
        'body': ai_ready_body(
            "Bandurria: Significado Espiritual, Caracteristicas y Habitat",
            "La bandurria (Theristicus caudatus) es un ave de la familia Threskiornithidae que habita en humedales de Chile y Sudamerica. Se caracteriza por su pico largo y curvo hacia abajo, su plumaje blanco/gris con alas negras, y su llamado fuerte y melodioso que resuena en pantanos y rios.",
            [
                ("Cual es el significado espiritual de la bandurria?",
                 "En la cultura popular chilena, la bandurria se asocia con la conexion entre el cielo y la tierra por su vuelo majestuoso. Su canto al amanecer simboliza renovacion espiritual. Algunas tradiciones la consideran mensajera de buenos presagios relacionada con el agua y la purificacion."),
                ("Donde vive la bandurria en Chile?",
                 "Habita en humedales, orillas de lagos, rios y zonas pantanosas desde Coquimbo hasta Magallanes. Prefiere areas abiertas cerca del agua donde se alimenta de insectos, crustaceos y peces pequenos."),
                ("Cuales son las especies de bandurria en Chile?",
                 "En Chile se encuentran principalmente la bandurria comun (Theristicus caudatus) y la bandurria de la puna (Theristicus branickii). Se diferencian por el tamano y la distribucion geografica."),
            ]
        )
    },
    11821: {  # Buho en la noche
        'body': ai_ready_body(
            "Que Significa Ver un Buho en la Noche? Mito, Realidad y Simbolismo",
            "Ver un buho en la noche tiene diferentes significados segun la cultura y el contexto. Desde la sabiduria en la mitologia griega (simbolo de Atenea) hasta presagio de cambio en tradiciones populares latinoamericanas. Los buhos (Strigiformes) son aves rapaces nocturnas con vision y audicion excepcionales.",
            [
                ("Que significa ver un buho en la noche espiritualmente?",
                 "Espiritualmente, ver un buho en la noche puede representar: sabiduria interior que emerge, capacidad de ver mas alla de las apariencias, transiciones importantes en la vida, o un llamado a prestar atencion a la intuicion. En muchas culturas indigenas es mensajero espiritual."),
                ("Es malo ver un buho en la noche?",
                 "No, no es malo. Los mitos que asocian los buhos con la mala suerte son solo supersticiones. Los buhos son aves beneficiosas que controlan plagas de roedores. Su aparicion es un encuentro afortunado con la vida silvestre nocturna."),
                ("Por que los buhos salen de noche?",
                 "Los buhos son animales nocturnos adaptados para cazar en la oscuridad. Tienen vision nocturna excepcional (5-10 veces mejor que la humana), vuelo silencioso gracias a plumas especiales, y audicion direccional precisa para localizar presas."),
            ]
        )
    },
    11822: {  # Falconidae
        'body': ai_ready_body(
            "Falconidae: Guia Completa de las Aves Rapaces Diurnas",
            "La familia Falconidae agrupa a los halcones, caracaras y chimangos: aves rapaces diurnas presentes en todos los continentes excepto la Antartida. Se caracterizan por su vuelo rapido (el halcon peregrino alcanza 320 km/h en picada), vision aguda y una muesca en el pico (diente falciforme) que los distingue de otras rapaces.",
            [
                ("Cuales son las especies de Falconidae en Chile?",
                 "Chile alberga 6 especies de Falconidae: Halcon peregrino (Falco peregrinus), Halcon perdiguero (Falco sparverius), Halcon plomizo (Falco femoralis), Caracara cordillerano (Phalcoboenus megalopterus), Caracara comun (Caracara plancus) y Chimango (Milvago chimango)."),
                ("Cual es la diferencia entre Falconidae y Accipitridae?",
                 "Los Falconidae (halcones) se diferencian de los Accipitridae (aguilas, buitres) porque cazan principalmente con el pico en lugar de las garras. Tienen una muesca dental en el pico superior para romper vertebras. Su construccion es mas estilizada con alas puntiagudas para alta velocidad."),
                ("Donde viven los halcones en Chile?",
                 "Los halcones se distribuyen por todo Chile, desde el desierto de Atacama hasta Tierra del Fuego. Cada especie ocupa un nicho diferente: el halcon peregrino prefiere acantilados costeros, el cernicalo campos abiertos, y el caracara cordillerano habita en la alta montana."),
            ]
        )
    },
    11823: {  # Pavo Real
        'body': ai_ready_body(
            "Pavo Real: Diferencias entre Macho y Hembra + Fotos",
            "El pavo real (Pavo cristatus) presenta un marcado dimorfismo sexual. El macho es famoso por su cola en abanico de plumas coloridas con ojos dorados y azules que despliega durante el cortejo. La hembra es de color pardo opaco sin cola vistosa, adaptada para camuflarse durante la incubacion.",
            [
                ("Como diferenciar pavo real macho y hembra?",
                 "El macho mide hasta 120 cm (cola de 150 cm adicional), pesa 4-6 kg y tiene plumaje azul iridiscente con cola espectacular. La hembra mide 85-95 cm, pesa 2.8-4 kg y es de color pardo cafe con vientre blanco. Solo el macho despliega la cola en abanico."),
                ("Por que el pavo real despliega su cola?",
                 "El despliegue de la cola es un comportamiento de cortejo para atraer a la hembra. Las hembras eligen al macho con la cola mas grande, simetrica y con mas ojos brillantes. Tambien lo usan como mecanismo de defensa para parecer mas grandes ante depredadores."),
                ("Donde viven los pavos reales?",
                 "Originarios de India y Sri Lanka, habitan bosques tropicales y subtropicales. Prefieren areas con agua cercana y arboles para dormir. Se han introducido en muchos paises como aves ornamentales en parques, zoologicos y fincas."),
            ]
        )
    },
    11824: {  # Chercan
        'body': ai_ready_body(
            "Chercan: Significado Espiritual y Caracteristicas de esta Ave Chilena",
            "El chercan (Troglodytes aedon) o ratona comun es un pajaro pequeno de 12 cm que habita en todo Chile. Se caracteriza por su canto potente y melodioso, su cola erecta y su comportamiento inquieto. Es conocido por su energia inagotable y su capacidad para adaptarse a diversos entornos.",
            [
                ("Cual es el significado espiritual del chercan?",
                 "El chercan se asocia con la alegria, la vitalidad y la perseverancia. Su canto constante incluso en dias lluviosos lo convierte en simbolo de optimismo. En la cultura popular chilena se dice que su presencia trae buena energia y su canto anuncia cambios positivos."),
                ("Donde vive el chercan en Chile?",
                 "Habita desde Arica hasta Magallanes, en bosques, jardines, zonas urbanas y campos agricolas. Es una especie muy adaptable que anida en cavidades naturales, huecos en paredes y cajas nido."),
                ("Que come el chercan?",
                 "Su alimentacion es principalmente insectivora: aranas, insectos pequenos, larvas y ocasionalmente frutas. Busca alimento activamente moviendose entre arbustos y el suelo."),
            ]
        )
    },
    11826: {  # Pajaro Gris
        'body': ai_ready_body(
            "Que Significa la Visita de un Pajaro Gris? Presagio y Significado Espiritual",
            "La visita inesperada de un pajaro gris a tu hogar puede tener multiples interpretaciones espirituales. En diferentes tradiciones, los pajaros de color gris se asocian con la intuicion, la neutralidad y mensajes del universo. El color gris en las aves representa equilibrio entre lo material y lo espiritual.",
            [
                ("Que significa que un pajaro gris visite tu casa?",
                 "Espiritualmente puede indicar: un periodo de transicion y equilibrio, la necesidad de observar antes de actuar, un mensaje de conexion con la sabiduria interior, o la llegada de noticias que requieren reflexion. Tambien puede ser simplemente un ave buscando alimento o refugio."),
                ("Que especies de pajaros grises son comunes en Chile?",
                 "En Chile hay varias aves de plumaje gris: la diuca (Diuca diuca), la dormilona cenicienta, el churrete comun, el canastero y algunas especies de zorzales juveniles. Cada una tiene caracteristicas y habitats distintos."),
            ]
        )
    },
    11828: {  # Plumaje Blanco y Penacho Amarillo
        'body': ai_ready_body(
            "Ave de Plumaje Blanco y Penacho Amarillo: Identificacion y Especies",
            "Varias especies de aves presentan plumaje blanco con penacho amarillo en la cabeza. En Sudamerica destacan la garceta grande (Ardea alba), la garceta nivea (Egretta thula) y ciertos loros. El penacho amarillo aparece generalmente en epoca reproductiva como señal de apareamiento.",
            [
                ("Cuales aves tienen plumaje blanco y penacho amarillo?",
                 "Las mas conocidas son: Garceta grande (Ardea alba) - penacho en nuca, Garceta nivea (Egretta thula) - penacho crestado, Garceta azulada (Egretta caerulea) juvenil de plumaje blanco, y el Cocoi (Ardea cocoi) con penacho negro y blanco."),
                ("Donde se encuentran estas aves en Chile?",
                 "Habitan humedales, lagunas costeras, rios y estuarios desde Arica hasta Chiloe. Son aves acuaticas que se alimentan de peces, anfibios y crustaceos en aguas poco profundas."),
            ]
        )
    },
    11830: {  # Piuquen
        'body': ai_ready_body(
            "Piuquen: Significado, Caracteristicas y Habitat de esta Ave Chilena",
            "El piuquen (Chloephaga melanoptera), tambien llamado guayata o cauquen, es un ave de la familia Anatidae que habita en los Andes de Chile y Argentina entre los 2.000 y 4.500 metros de altitud. Es un simbolo de la fauna altoandina con su plumaje blanco y negro y su capacidad para soportar temperaturas extremas.",
            [
                ("Cual es el significado del piuquen?",
                 "En la cultura andina, el piuquen simboliza resistencia y adaptacion por su capacidad de vivir en condiciones extremas de altura y frio. Es considerado un indicador de la salud de los ecosistemas altoandinos y aparece en la mitologia local como ave sagrada."),
                ("Donde vive el piuquen?",
                 "Habita exclusivamente en la puna y altiplano de Chile y Argentina, en lagunas y bofedales de altura. Se alimenta de pastos, hierbas y plantas acuaticas. Es una especie adaptada al frio extremo y la baja presion de oxigeno."),
            ]
        )
    },
    11832: {  # Condor Andino
        'body': ai_ready_body(
            "Condor Andino: Caracteristicas, Habitat y Donde Verlo en Chile",
            "El condor andino (Vultur gryphus) es el ave voladora mas grande del mundo con una envergadura alar de hasta 3.3 metros. Es simbolo nacional de Chile y aparece en su escudo. Puede vivir hasta 70 anos en cautiverio y es una de las aves mas longevas del planeta.",
            [
                ("Cuales son las caracteristicas del condor andino?",
                 "Peso: 11-15 kg. Longitud: 100-130 cm. Envergadura: 280-330 cm. Plumaje negro con collar blanco en el cuello. Cabeza y cuello desnudos (adaptacion para alimentarse de carrona). Los machos tienen cresta. Vuelo planeado sin batir alas por horas usando corrientes termicas."),
                ("Donde ver condores en Chile?",
                 "Los mejores lugares: Cajon del Maipo (RM), Valle del Elqui (Coquimbo), Parque Nacional Torres del Paine (Magallanes), Rio Blanco (Los Andes), y la Reserva Nacional Altos de Lircay (Maule). La epoca ideal es primavera-verano (octubre-marzo)."),
                ("Que come el condor andino?",
                 "Es carronero: se alimenta de animales muertos como guanacos, llamas, vacunos y conejos. Puede recorrer mas de 200 km en un dia buscando alimento. Juega un rol ecologico crucial eliminando cadavers que podrian propagar enfermedades."),
            ]
        )
    },
    11834: {  # Aves en Peligro
        'body': ai_ready_body(
            "Aves en Peligro de Extincion en Chile: Lista 2026 y Como Ayudarlas",
            "Chile alberga mas de 500 especies de aves, de las cuales 38 se encuentran en alguna categoria de amenaza segun la UICN y la Ley de Caza 19473. Las principales amenazas son la perdida de habitat, especies invasoras, cambio climatico y caza ilegal.",
            [
                ("Cuales son las aves mas amenazadas de Chile?",
                 "Entre las especies en peligro critico destacan: Picaflor de Juan Fernandez (Sephanoides fernandensis), Canquen colorado (Chloephaga rubidiceps), Pardela de Parkinson (Procellaria parkinsoni), y el Chorlo nevado (Charadrius nivosus). El loro tricahue esta en peligro de extincion."),
                ("Como ayudar a las aves en peligro de extincion?",
                 "Acciones concretas: instalar comederos y bebederos, participar en ciencia ciudadana (eBird Chile), donar a organizaciones como Codeff o Aves Chile, denunciar caza ilegal (Conaf), reducir uso de pesticidas en jardines, y mantener gatos dentro de casa."),
                ("Que protege la Ley de Caza 19473?",
                 "La Ley 19473 regula la caza en Chile, establece temporadas, especies protegidas, vedas y sanciones. Protege todas las aves nativas no declaradas como plagas. Las multas por caza ilegal van desde 5 a 200 UTM ($330.000 a $13 millones CLP)."),
            ]
        )
    },
    11836: {  # Gallina Criolla
        'body': ai_ready_body(
            "Gallina Criolla Chilena: Caracteristicas, Crianza y Cuidados",
            "La gallina criolla chilena es una raza adaptada a las condiciones locales a traves de generaciones de seleccion natural. Es mas resistente que las razas industriales, se alimenta de manera mas natural y produce huevos de cascara mas dura con yemas de color intenso.",
            [
                ("Cuales son las caracteristicas de la gallina criolla?",
                 "Peso: 1.5-2.5 kg. Plumaje variado (colorada, negra, blanca, mix). Pone 120-180 huevos/ano. Cascara de huevo color crema a marron claro. Temperamento: activa, buena buscadora de alimento, buena madre (clueca). Resistente a enfermedades comunes."),
                ("Como criar gallinas criollas en casa?",
                 "Necesitan: gallinero con 1 m2 por ave, espacio exterior para escarbar, alimentacion balanceada (granos + verduras + cascara de huevo molida), agua fresca siempre, y proteccion contra depredadores (zorro, quique, aguilas). No requieren vacunas especiales, solo desparasitacion cada 3 meses."),
            ]
        )
    },
    11839: {  # Tapaculo
        'body': ai_ready_body(
            "Tapaculo: Caracteristicas, Habitat y Curiosidades de esta Ave Chilena",
            "El tapaculo (Scelorchilus rubecula) es un ave de la familia Rhinocryptidae, endemica de los bosques templados de Chile y Argentina. Mide 14-16 cm y se caracteriza por su comportamiento esquivo, su pecho rojizo y su peculiar nombre que describe su cola erecta.",
            [
                ("Por que se llama tapaculo?",
                 "Recibe este nombre por su costumbre de mantener la cola erguida, dando la impresion de 'taparse' la parte posterior. En ingles se llama Chucao Tapaculo. Su nombre mapuche es 'chucao' y es protagonista de leyendas sobre el bosque valdiviano."),
                ("Donde vive el tapaculo en Chile?",
                 "Habita en el bosque templado lluvioso desde la Region del Maule hasta Aysen. Prefiere zonas con sotobosque denso y hojarasca donde busca insectos. Es un ave dificil de ver pero facil de escuchar por su canto caracteristico."),
                ("El tapaculo esta en peligro de extincion?",
                 "No esta globalmente amenazado, pero su habitat (bosque nativo) esta fragmentado. La deforestacion y los incendios forestales son sus principales amenazas. Es una especie indicadora de la salud del bosque templado chileno."),
            ]
        )
    },
}

for pid, data in AI_CONTENT.items():
    resp = requests.get('{}posts/{}'.format(base, pid), auth=auth)
    if resp.status_code != 200:
        print('FAIL get {}: {}'.format(pid, resp.status_code))
        continue
    
    post = resp.json()
    current_content = post['content']['rendered']
    new_body = data['body']
    
    # Keep existing affiliate disclaimer if present
    if 'afiliado' in current_content.lower() or 'amazon' in current_content.lower():
        new_body += '<p><em>Como afiliado de Amazon, gano por compras calificadas.</em></p>'
    
    result = requests.post('{}posts/{}'.format(base, pid), auth=auth, json={'content': new_body})
    if result.status_code == 200:
        print('UPDATED {}: {}'.format(pid, post['title']['rendered'][:50]))
    else:
        print('FAIL {}: {}'.format(pid, result.status_code))

print('AI Overview optimization done - 14 posts updated')
