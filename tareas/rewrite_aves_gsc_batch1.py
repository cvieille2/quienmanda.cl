"""
rewrite_aves_gsc_batch1.py

Reescribe en bloque varias URLs de avesnativaschilenas.cl con posicion
50+ y demanda de busqueda clara.
"""

import requests

AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2'


def update_post(post_id, title, content, excerpt=None):
    data = {'title': title, 'content': content}
    if excerpt:
        data['excerpt'] = excerpt
    return requests.post(f'{BASE}/posts/{post_id}', auth=AUTH, json=data, timeout=30)


REWRITES = [
    {
        'id': 1870,
        'title': 'Condor andino en Chile: caracteristicas, habitat, alimentacion y conservacion',
        'excerpt': 'Guia completa del condor andino en Chile: como reconocerlo, donde vive, que come y por que necesita proteccion.',
        'content': '''<p>El <strong>condor andino</strong> (<em>Vultur gryphus</em>) es la rapaz voladora mas emblematica de la cordillera de los Andes. Su silueta enorme, el vuelo planeado y su papel como carroñero lo convierten en una especie clave para los ecosistemas de altura y en un simbolo natural de Chile y Sudamerica.</p>

<p>Si quieres seguir explorando rapaces chilenas, revisa la <a href="https://avesnativaschilenas.cl/rapaces/">guia del grupo de rapaces</a>, la seccion de <a href="https://avesnativaschilenas.cl/aguila/">aguilas chilenas</a> y el contenido de <a href="https://avesnativaschilenas.cl/rapaces/jote/">jotes</a>.</p>

<h2>Ficha rapida del condor andino</h2>
<table>
<thead><tr><th>Dato</th><th>Resumen</th></tr></thead>
<tbody>
<tr><td>Tamano</td><td>Hasta 1,3 m de longitud</td></tr>
<tr><td>Envergadura</td><td>Hasta 3,2 m</td></tr>
<tr><td>Alimentacion</td><td>Carrona</td></tr>
<tr><td>Habitat</td><td>Cordillera, acantilados y zonas abiertas</td></tr>
<tr><td>Estado</td><td>Vulnerable</td></tr>
</tbody>
</table>

<h2>Como reconocer al condor andino</h2>
<p>Se distingue por su tamano descomunal, alas anchas, plumaje negro y collar blanco alrededor del cuello. En los machos adultos, la cresta y el caracter imponente son aun mas visibles. Cuando planea, casi no bate las alas: aprovecha las corrientes termicas para recorrer grandes distancias con un gasto energetico minimo.</p>

<h2>Donde vive en Chile</h2>
<p>En Chile habita desde la cordillera del norte hasta la Patagonia. Aunque se le asocia con los Andes altos, tambien puede verse en quebradas, acantilados costeros y zonas abiertas donde existan corrientes de aire y alimento.</p>

<h2>Que come y por que es importante</h2>
<p>El condor no caza presas vivas: consume carrona. Eso ayuda a limpiar el ambiente, reduce riesgos sanitarios y recicla materia organica. Su papel ecologico es clave en ecosistemas de montaña, donde otras especies aprovechan restos y el ciclo de nutrientes se mantiene activo.</p>

<h2>Amenazas y conservacion</h2>
<p>Las amenazas mas comunes son el envenenamiento por cebos, la perdida de habitat, la persecucion directa y la ingesta de restos contaminados. Aunque es una especie protegida, su poblacion sigue necesitando monitoreo y educacion ambiental.</p>

<h2>Donde verlo</h2>
<p>Las mejores oportunidades suelen darse en rutas cordilleranas, miradores altos y zonas de acantilados donde el viento sopla constante. Si vas a observarlo, mantente a distancia, evita el ruido y no uses drones.</p>

<h2>Preguntas frecuentes</h2>
<h3>El condor andino vive en ciudades?</h3>
<p>No. Su habitat natural son zonas abiertas, montañosas y de dificil acceso.</p>
<h3>El condor caza animales?</h3>
<p>No. Se alimenta de carrona y cumple una funcion sanitaria esencial.</p>
<h3>Por que esta amenazado?</h3>
<p>Principalmente por venenos, perdida de habitat y conflictos con actividades humanas.</p>

<h2>Explora mas sobre rapaces chilenas</h2>
<p>Si este tema te interesa, sigue con la <a href="https://avesnativaschilenas.cl/rapaces/">guia del grupo de rapaces</a> y vuelve al portal principal en <a href="https://avesnativaschilenas.cl/">Aves Nativas Chilenas</a>.</p>'''
    },
    {
        'id': 9156,
        'title': 'Grupo de aves marinas al que pertenecen las gaviotas: guia de la familia Laridae en Chile',
        'excerpt': 'Las gaviotas pertenecen a la familia Laridae. Aqui tienes una guia clara con sus rasgos, habitat, comportamiento y especies chilenas.',
        'content': '''<p>Las <strong>gaviotas</strong> pertenecen a la familia <strong>Laridae</strong>, un grupo de aves marinas muy adaptables que vive en costas, puertos, rios y sectores urbanos cercanos al mar. En Chile, son parte cotidiana del paisaje costero y destacan por su oportunismo, su vuelo estable y su capacidad para aprovechar recursos muy diversos.</p>

<p>Si quieres explorar el grupo principal de especies, revisa tambien la <a href="https://avesnativaschilenas.cl/marina/gaviota/">guia completa de gaviotas en Chile</a> y la categoria general de <a href="https://avesnativaschilenas.cl/marina/">aves marinas</a>.</p>

<h2>Que caracteriza a la familia Laridae</h2>
<p>Las gaviotas tienen alas largas, pico fuerte y una dieta muy flexible. Se alimentan de peces, invertebrados, carrona y restos humanos. Esa flexibilidad explica por que pueden vivir tanto en playas remotas como en ciudades portuarias.</p>

<h2>Especies y formas de observacion en Chile</h2>
<p>En el pais hay especies costeras y otras que se mueven entre zonas oceanicas y continentales. Para identificarlas, conviene fijarse en el tamano, el tono del dorso, el color del pico y el patron de vuelo.</p>

<h2>Diferencia entre gaviotas, fardelas y gaviotines</h2>
<table>
<thead><tr><th>Grupo</th><th>Rasgo clave</th><th>Comportamiento</th></tr></thead>
<tbody>
<tr><td>Gaviotas</td><td>Pico robusto, vuelo oportunista</td><td>Costas, puertos, playas</td></tr>
<tr><td>Fardelas</td><td>Ala muy larga, vuelo planeado</td><td>Pelagicas, mar abierto</td></tr>
<tr><td>Gaviotines</td><td>Cuerpo mas esbelto, pico fino</td><td>Pesca superficial</td></tr>
</tbody>
</table>

<h2>Por que son tan exitosas</h2>
<p>La respuesta es simple: se adaptan a casi todo. Su comportamiento social, su inteligencia y su tolerancia a diferentes fuentes de alimento las convierten en una de las aves marinas mas visibles del pais.</p>

<h2>Preguntas frecuentes</h2>
<h3>Las gaviotas son aves marinas?</h3>
<p>Si. Pertenecen a la familia Laridae y su relacion con el mar es directa.</p>
<h3>Todas las gaviotas viven solo en la costa?</h3>
<p>No. Muchas usan puertos, rios, lagos y zonas urbanas cercanas a la costa.</p>
<h3>Se parecen a las fardelas?</h3>
<p>Comparten habitat marino, pero las fardelas tienen un vuelo mas planeado y una anatomia distinta.</p>

<h2>Sigue explorando aves marinas chilenas</h2>
<p>Si este tema te interesa, sigue por la <a href="https://avesnativaschilenas.cl/fardela/">guia de fardelas</a> y vuelve al portal principal en <a href="https://avesnativaschilenas.cl/">Aves Nativas Chilenas</a>.</p>'''
    },
    {
        'id': 9393,
        'title': 'Como distinguir un pavo real macho de una hembra: diferencias reales y fotos',
        'excerpt': 'Aprende a diferenciar un pavo real macho de una hembra por plumaje, tamano, comportamiento y edad. Guia clara y visual.',
        'content': '''<p>El <strong>pavo real</strong> es una de las aves mas famosas por su dimorfismo sexual: el macho luce una cola espectacular, mientras que la hembra tiene un plumaje mucho mas discreto. Aunque no es una especie nativa de Chile, su presencia en parques, zoologicos y criaderos hace que muchas personas busquen como distinguirlo correctamente.</p>

<p>Si te interesan otras aves terrestres y sus diferencias, puedes seguir la <a href="https://avesnativaschilenas.cl/terrestres/">guia del cluster de aves terrestres</a> y revisar tambien el <a href="https://avesnativaschilenas.cl/terrestres/faisan/">faisan</a>, otra especie exotica que suele confundirse con el pavo real.</p>

<h2>Diferencias clave entre macho y hembra</h2>
<table>
<thead><tr><th>Caracteristica</th><th>Macho</th><th>Hembra</th></tr></thead>
<tbody>
<tr><td>Plumaje</td><td>Iridiscente y vistoso</td><td>Mas sobrio y pardo</td></tr>
<tr><td>Cola</td><td>Muy larga con ocelos</td><td>Corta y sin abanico</td></tr>
<tr><td>Tamano</td><td>Generalmente mayor y mas llamativo</td><td>Menor y mas discreto</td></tr>
<tr><td>Conducta</td><td>Hace despliegues de cortejo</td><td>Prioriza el camuflaje y el nido</td></tr>
</tbody>
</table>

<h2>Como identificarlo sin equivocarte</h2>
<p>La pista mas fiable es la cola. Si ves un abanico enorme con ocelos, estas frente a un macho adulto. Si el ave tiene cola corta, cuerpo mas apagado y postura discreta, casi seguro es una hembra. En juveniles la diferenciacion es menos obvia y conviene observar el desarrollo de las plumas iridiscentes.</p>

<h2>Por que las diferencias son tan marcadas</h2>
<p>El macho invierte energia en exhibicion para atraer pareja, mientras la hembra prioriza la supervivencia y la proteccion del nido. Es un ejemplo clasico de seleccion sexual.</p>

<h2>Preguntas frecuentes</h2>
<h3>El pavo real blanco es otra especie?</h3>
<p>No. Es una variacion genetica del pavo real azul.</p>
<h3>Las hembras hacen la rueda?</h3>
<p>No. El abanico largo y ornamental es caracteristico del macho.</p>
<h3>El pavo real vive en Chile de forma silvestre?</h3>
<p>No. En Chile solo aparece en cautiverio o colecciones privadas.</p>

<h2>Mas aves terrestres para comparar</h2>
<p>Si quieres seguir comparando especies exoticas y nativas, revisa tambien la <a href="https://avesnativaschilenas.cl/terrestres/">categoria de aves terrestres</a> y vuelve al portal principal en <a href="https://avesnativaschilenas.cl/">Aves Nativas Chilenas</a>.</p>'''
    },
    {
        'id': 9366,
        'title': 'Que significa ver una lechuza blanca volando de noche: simbolismo y explicacion natural',
        'excerpt': 'Ver una lechuza blanca de noche puede tener lectura simbolica, pero tambien una explicacion biologica. Aqui ambas.',
        'content': '''<p>Ver una <strong>lechuza blanca volando de noche</strong> suele impresionar porque mezcla misterio, silencio y contraste visual. En distintas tradiciones, este encuentro se asocia con intuicion, cambio o alerta. Pero ademas del simbolismo, hay una explicacion natural: las rapaces nocturnas vuelan de noche para cazar y desplazarse con eficiencia.</p>

<p>Si te interesa profundizar en el grupo, revisa la <a href="https://avesnativaschilenas.cl/rapaces/lechuza/">guia de lechuzas</a> y tambien otras rapaces nocturnas como los <a href="https://avesnativaschilenas.cl/rapaces/buhos/">buhos</a> y el <a href="https://avesnativaschilenas.cl/rapaces/chuncho/">chuncho</a>.</p>

<h2>Que suele simbolizar</h2>
<p>En muchas culturas, la lechuza se relaciona con sabiduria, percepcion y mensajes ocultos. Si aparece cerca de una casa, algunas personas lo interpretan como un llamado a observar mejor una situacion o a prepararse para un cambio.</p>

<h2>La explicacion natural</h2>
<p>Las lechuzas tienen vision y audicion adaptadas a la oscuridad. Su vuelo es silencioso gracias a la forma de sus plumas, y su color blanco puede verse mas intenso con la luz de la luna o focos cercanos.</p>

<h2>Que hacer si ves una</h2>
<p>Lo ideal es observarla a distancia, no usar flash y no intentar tocarla. Si esta en el suelo o parece herida, contacta a rescate de fauna local. Si solo sobrevuela la zona, probablemente esta cazando o desplazandose.</p>

<h2>Preguntas frecuentes</h2>
<h3>Ver una lechuza blanca es mala señal?</h3>
<p>No necesariamente. Es una interpretacion cultural, no una regla universal.</p>
<h3>Las lechuzas son aves nocturnas?</h3>
<p>Si. Su anatomia esta adaptada a cazar de noche.</p>
<h3>Pueden vivir cerca de zonas urbanas?</h3>
<p>Si, si hay alimento, refugio y poca perturbacion humana.</p>

<h2>Continua con rapaces nocturnas</h2>
<p>Si buscas mas informacion, sigue por la <a href="https://avesnativaschilenas.cl/rapaces/">categoria general de rapaces</a> y vuelve al portal principal en <a href="https://avesnativaschilenas.cl/">Aves Nativas Chilenas</a>.</p>'''
    },
    {
        'id': 218,
        'title': 'Tordo chileno: habitat, alimentacion, canto y curiosidades',
        'excerpt': 'Guia del tordo chileno: como reconocerlo, donde vive, que come y por que su canto lo distingue.',
        'content': '''<p>El <strong>tordo chileno</strong> (<em>Curaeus curaeus</em>) es un ave negra, de presencia fuerte y canto caracteristico, muy asociada a campos, jardines y zonas abiertas del centro y sur de Chile. Aunque a veces pasa desapercibido por su color oscuro, es una especie muy interesante por su comportamiento social y su adaptacion a diversos ambientes.</p>

<p>Si quieres seguir el cluster de especies terrestres, revisa la <a href="https://avesnativaschilenas.cl/terrestres/">guia de aves terrestres</a> y compara con el <a href="https://avesnativaschilenas.cl/terrestres/zorzal/">zorzal chileno</a> y el <a href="https://avesnativaschilenas.cl/terrestres/chucao/">chucao</a>.</p>

<h2>Ficha rapida</h2>
<table>
<thead><tr><th>Dato</th><th>Resumen</th></tr></thead>
<tbody>
<tr><td>Nombre cientifico</td><td><em>Curaeus curaeus</em></td></tr>
<tr><td>Color</td><td>Negro brillante</td></tr>
<tr><td>Alimentacion</td><td>Omnivora</td></tr>
<tr><td>Voz</td><td>Canto fuerte y variado</td></tr>
<tr><td>Distribucion</td><td>Centro y sur de Chile</td></tr>
</tbody>
</table>

<h2>Como reconocerlo</h2>
<p>Su plumaje es completamente negro con un brillo sutil en la luz. El pico es fino y curvo, ideal para aprovechar frutos, semillas e insectos. Suele moverse en pareja o pequenos grupos, y en ambientes abiertos se deja ver mientras forrajea en el suelo.</p>

<h2>Donde vive</h2>
<p>Frecuenta praderas, matorrales, bordes de bosque, campos cultivados y jardines. Es una especie bastante adaptable, por lo que puede aparecer en zonas periurbanas con vegetacion suficiente.</p>

<h2>Que come</h2>
<p>Su dieta mezcla insectos, frutos y pequenas semillas. Esa flexibilidad le permite mantenerse activo durante todo el ano, incluso cuando cambia la disponibilidad de alimento.</p>

<h2>Canto y comportamiento</h2>
<p>El canto del tordo es uno de sus rasgos mas recordables. No solo le sirve para defender territorio: tambien ayuda a mantener contacto entre individuos del grupo y a fortalecer el vinculo durante la temporada reproductiva.</p>

<h2>Preguntas frecuentes</h2>
<h3>El tordo es un cuervo?</h3>
<p>No. Es una especie distinta dentro de otra familia.</p>
<h3>Vive solo en Chile?</h3>
<p>No, tambien se distribuye en otras zonas de Sudamerica.</p>
<h3>Se alimenta solo de semillas?</h3>
<p>No. Es omnivoro y aprovecha frutos e insectos.</p>

<h2>Sigue explorando especies terrestres</h2>
<p>Si quieres continuar, mira la <a href="https://avesnativaschilenas.cl/terrestres/">categoria general de aves terrestres</a> y vuelve al portal principal en <a href="https://avesnativaschilenas.cl/">Aves Nativas Chilenas</a>.</p>'''
    },
    {
        'id': 9454,
        'title': 'Que significa que una golondrina entre a tu casa: presagio, biologia y que hacer',
        'excerpt': 'Descubre que significa que una golondrina entre a tu casa, como interpretarlo y cuando solo se trata de conducta natural.',
        'content': '''<p>Cuando una <strong>golondrina entra a tu casa</strong>, muchas personas lo leen como un mensaje de cambio, proteccion o buen augurio. En la tradicion popular, estas aves migratorias se asocian con el regreso al hogar, la buena suerte y los ciclos que se renuevan. Pero tambien hay una explicacion biologica simple: buscan refugio, alimento o un lugar tranquilo para descansar.</p>

<p>Si te interesa este grupo, sigue por la <a href="https://avesnativaschilenas.cl/migratorias/">categoria de aves migratorias</a> y revisa la propia <a href="https://avesnativaschilenas.cl/migratorias/golondrina/">guia de golondrinas</a>.</p>

<h2>Significado popular</h2>
<p>La golondrina suele verse como simbolo de retorno, hogar y buenos cambios. Si entra a una casa, la interpretacion mas repetida es que llega una etapa de movimiento positivo o que un ciclo se esta cerrando para dar paso a otro.</p>

<h2>La explicacion natural</h2>
<p>Las golondrinas vuelan rapido y a baja altura cuando hay insectos, y pueden entrar por ventanas o puertas abiertas. Tambien se acercan a estructuras humanas porque ofrecen refugio frente al viento o la lluvia. No siempre es una senal: muchas veces es solo comportamiento oportunista.</p>

<h2>Que hacer si entra una</h2>
<p>Abre una salida amplia, apaga luces internas si es de noche y evita perseguirla. Lo mejor es dejar que encuentre una ruta natural hacia el exterior. Si queda atrapada, puedes guiarla con calma hacia una ventana abierta.</p>

<h2>Cuando preocuparse</h2>
<p>Si la golondrina no puede volar, choca contra objetos o parece herida, contacta a rescate de fauna. Si solo entro por accidente y sale enseguida, no suele haber problema.</p>

<h2>Preguntas frecuentes</h2>
<h3>Es buena suerte?</h3>
<p>Tradicionalmente si, aunque eso depende de la cultura.</p>
<h3>Las golondrinas anidan en casas?</h3>
<p>A veces si, sobre todo en aleros, techos o estructuras protegidas.</p>
<h3>Son aves migratorias?</h3>
<p>Si, muchas especies se mueven segun la estacion.</p>

<h2>Sigue con aves migratorias</h2>
<p>Si el tema te intereso, sigue por la <a href="https://avesnativaschilenas.cl/migratorias/golondrina/">guia de golondrinas</a> y vuelve al portal principal en <a href="https://avesnativaschilenas.cl/">Aves Nativas Chilenas</a>.</p>'''
    },
]


def main():
    print('Reescritura GSC batch 1')
    ok = 0
    for item in REWRITES:
        resp = update_post(item['id'], item['title'], item['content'], item['excerpt'])
        if resp.status_code == 200:
            ok += 1
            print(f"OK {item['id']} -> {item['title']}")
        else:
            print(f"ERR {item['id']} -> {resp.status_code}: {resp.text[:200]}")
    print(f'Total actualizados: {ok}/{len(REWRITES)}')


if __name__ == '__main__':
    main()
