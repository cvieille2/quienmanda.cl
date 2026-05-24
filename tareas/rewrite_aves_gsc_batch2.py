"""
rewrite_aves_gsc_batch2.py

Segundo lote de reescrituras para avesnativaschilenas.cl.
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
        'id': 9827,
        'title': 'Gorrion en Chile: caracteristicas, habitat, alimentacion y comportamiento',
        'excerpt': 'Guia completa del gorrion en Chile: como reconocerlo, donde vive, que come y por que domina las ciudades.',
        'content': '''<p>El <strong>gorrion domestico</strong> es uno de los pajaros urbanos mas conocidos del pais. Aunque no es una especie nativa de Chile, se adapto con exito a plazas, barrios, parques y zonas rurales cercanas a personas. Su presencia constante lo convierte en un clasico de la avifauna urbana.</p>

<p>Si quieres seguir explorando especies terrestres y urbanas, revisa la <a href="https://avesnativaschilenas.cl/terrestres/">categoria de aves terrestres</a> y compara este contenido con el <a href="https://avesnativaschilenas.cl/terrestres/chercan/">chercan</a> y el <a href="https://avesnativaschilenas.cl/terrestres/zorzal/">zorzal chileno</a>.</p>

<h2>Ficha rapida</h2>
<table>
<thead><tr><th>Dato</th><th>Resumen</th></tr></thead>
<tbody>
<tr><td>Nombre comun</td><td>Gorrion domestico</td></tr>
<tr><td>Habitat</td><td>Ciudades, pueblos y campos</td></tr>
<tr><td>Alimentacion</td><td>Semillas, granos e insectos</td></tr>
<tr><td>Comportamiento</td><td>Gregario y oportunista</td></tr>
</tbody>
</table>

<h2>Como reconocerlo</h2>
<p>Es pequeno, compacto y muy familiar. El macho suele mostrar tonos marrones y grisaceos con marcas mas definidas en la cabeza, mientras que la hembra es mas uniforme y discreta. Su vuelo es corto y rapido, y rara vez se aleja mucho de la actividad humana.</p>

<h2>Donde vive</h2>
<p>Se adapta muy bien a zonas urbanas, granjas, patios y bordes de caminos. Le favorecen los espacios con techos, cavidades y lugares donde pueda encontrar alimento facilmente.</p>

<h2>Que come</h2>
<p>Su dieta incluye semillas, granos y pequenos insectos. En ciudades, aprovecha restos de comida y alimentos caidos en plazas o terrazas, aunque lo ideal es no fomentarlo con pan o residuos procesados.</p>

<h2>Por que se relaciona tanto con humanos</h2>
<p>Porque aprendio a vivir cerca de nosotros. Esa adaptacion explica su exito biologico y su expansion en ambientes modificados por el ser humano.</p>

<h2>Preguntas frecuentes</h2>
<h3>El gorrion es nativo de Chile?</h3>
<p>No. Es una especie introducida que se establecio muy bien en ambientes urbanos.</p>
<h3>Se alimenta solo de semillas?</h3>
<p>No. Tambien consume insectos y restos de alimentos.</p>
<h3>Por que aparece tanto en ciudades?</h3>
<p>Porque encuentra comida, refugio y pocos depredadores en zonas urbanas.</p>

<h2>Sigue explorando aves urbanas</h2>
<p>Si quieres seguir comparando especies, revisa la <a href="https://avesnativaschilenas.cl/">portada del sitio</a> y la <a href="https://avesnativaschilenas.cl/terrestres/">categoria de aves terrestres</a>.</p>'''
    },
    {
        'id': 9370,
        'title': 'Donde viven los patos y de que se alimentan: guia completa de aves acuaticas en Chile',
        'excerpt': 'Descubre donde viven los patos y de que se alimentan en Chile, con habitat, dieta, diferencias y consejos de observacion.',
        'content': '''<p>Los <strong>patos</strong> son aves acuticas que se adaptan a lagunas, rios, humedales, esteros y zonas costeras. En Chile existen varias especies, cada una con preferencias distintas de habitat y alimentacion. Esta guia te ayuda a entender mejor el grupo y a distinguir sus rasgos principales.</p>

<p>Para ampliar el contexto, revisa tambien la <a href="https://avesnativaschilenas.cl/pato/">guia principal de patos en Chile</a> y la categoria general de <a href="https://avesnativaschilenas.cl/acuaticas/">aves acuaticas</a>.</p>

<h2>Ficha rapida</h2>
<table>
<thead><tr><th>Dato</th><th>Resumen</th></tr></thead>
<tbody>
<tr><td>Habitat</td><td>Lagunas, rios, humedales y costas</td></tr>
<tr><td>Alimentacion</td><td>Plantas acuaticas, semillas, invertebrados</td></tr>
<tr><td>Movimiento</td><td>Nado, zambullida o filtrado segun especie</td></tr>
</tbody>
</table>

<h2>Donde viven los patos</h2>
<p>La respuesta depende de la especie. Algunos prefieren lagunas de agua dulce, otros humedales salobres y otros ambientes altoandinos. En general, todos necesitan agua para alimentarse, descansar y escapar de depredadores.</p>

<h2>De que se alimentan</h2>
<p>La dieta puede incluir plantas acuaticas, algas, semillas, pequenos moluscos, larvas e insectos. Algunas especies pastan en la superficie del agua, mientras otras bucean o remueven el fondo para encontrar alimento.</p>

<h2>Como reconocerlos mejor</h2>
<p>Conviene mirar el tamano, el color del pico, el patron del cuello y la forma de nadar. Los patos de pico ancho suelen filtrar alimento, mientras que otros muestran picos mas estrechos o cuerpos mas compactos.</p>

<h2>Preguntas frecuentes</h2>
<h3>Los patos comen pan?</h3>
<p>No es recomendable. El pan no es un alimento adecuado para aves silvestres.</p>
<h3>Todos los patos viven en lagunas?</h3>
<p>No. Hay especies costeras, altoandinas y de humedal.</p>
<h3>Los patos migran?</h3>
<p>Algunas especies si, segun la estacion y el clima.</p>

<h2>Sigue con aves acuaticas chilenas</h2>
<p>Si quieres continuar, revisa la <a href="https://avesnativaschilenas.cl/acuaticas/">categoria de aves acuaticas</a> y vuelve al portal principal en <a href="https://avesnativaschilenas.cl/">Aves Nativas Chilenas</a>.</p>'''
    },
    {
        'id': 9458,
        'title': 'Como alimentar a un pichon de paloma: guia segura paso a paso',
        'excerpt': 'Si encontraste un pichon de paloma, aqui tienes una guia segura: que darle, cada cuanto y que errores evitar.',
        'content': '''<p>Alimentar a un <strong>pichon de paloma</strong> requiere tecnica, paciencia y alimentos seguros. Los polluelos dependen por completo de sus padres durante las primeras semanas, asi que antes de intervenir conviene evaluar si realmente necesita ayuda. Si si la necesita, esta guia te muestra como actuar sin ponerlo en riesgo.</p>

<p>Para comprender mejor el grupo, revisa la <a href="https://avesnativaschilenas.cl/migratorias/paloma/">guia de palomas en Chile</a> y tambien la informacion sobre <a href="https://avesnativaschilenas.cl/tortola/">tortolas</a>, que suelen confundirse con las palomas urbanas.</p>

<h2>Primer paso: evaluar la situacion</h2>
<p>Si el pichon esta caliente, activo y cerca de sus padres, puede que no necesite ayuda. Si esta frio, herido, debil o en un lugar peligroso, entonces si requiere intervencion.</p>

<h2>Que darle de comer</h2>
<table>
<thead><tr><th>Edad</th><th>Alimento</th><th>Observacion</th></tr></thead>
<tbody>
<tr><td>Recien nacido</td><td>Formula especial o alimento veterinario</td><td>No usar leche de vaca</td></tr>
<tr><td>Intermedio</td><td>Formula mas espesa</td><td>Pequenas porciones</td></tr>
<tr><td>Mas grande</td><td>Semillas blandas y transicion gradual</td><td>Preparar destete</td></tr>
</tbody>
</table>

<h2>Que no debes dar</h2>
<p>No uses pan, leche de vaca, arroz crudo, sal, ni comida condimentada. Esos alimentos pueden causar diarrea, deshidratacion o incluso asfixia.</p>

<h2>Cada cuanto alimentarlo</h2>
<p>La frecuencia depende de la edad. En polluelos muy pequenos puede requerir varias tomas al dia; en aves mas grandes, el intervalo se alarga. Lo importante es no sobrealimentar y asegurarse de que el buche vacie correctamente.</p>

<h2>Cuidados basicos</h2>
<p>Mantenlo en un lugar tibio, tranquilo y sin corrientes de aire. Usa una caja limpia con papel absorbente y evita manipularlo de mas.</p>

<h2>Preguntas frecuentes</h2>
<h3>Puedo darle pan?</h3>
<p>No. El pan no es adecuado para pichones de paloma.</p>
<h3>Puedo darle leche?</h3>
<p>No. La leche de vaca es peligrosa para las aves.</p>
<h3>Cuanto tiempo necesita alimentacion asistida?</h3>
<p>Hasta que pueda comer por si mismo y mantener un peso estable.</p>

<h2>Si el caso es urgente</h2>
<p>Si el pichon esta herido o muy debil, busca ayuda veterinaria cuanto antes. Y si quieres seguir aprendiendo, vuelve al portal principal en <a href="https://avesnativaschilenas.cl/">Aves Nativas Chilenas</a>.</p>'''
    },
    {
        'id': 6179,
        'title': 'Chercan en Chile: habitat, alimentacion, canto y comportamiento',
        'excerpt': 'Guia del chercan chileno: como reconocerlo, donde vive, que come y por que su canto es tan llamativo.',
        'content': '''<p>El <strong>chercan</strong> (<em>Troglodytes aedon</em>) es un pajaro pequeno y muy activo, famoso por su canto potente y su personalidad inquieta. En Chile se le ve en jardines, matorrales, bordes de bosque y sectores con vegetacion densa donde encuentra refugio y alimento.</p>

<p>Si quieres seguir el cluster de pajaros terrestres, revisa la <a href="https://avesnativaschilenas.cl/churrin/">categoria de churrines</a> y tambien la guia de <a href="https://avesnativaschilenas.cl/terrestres/">aves terrestres</a>.</p>

<h2>Ficha rapida</h2>
<table>
<thead><tr><th>Dato</th><th>Resumen</th></tr></thead>
<tbody>
<tr><td>Nombre cientifico</td><td><em>Troglodytes aedon</em></td></tr>
<tr><td>Tamano</td><td>Pequeno</td></tr>
<tr><td>Canto</td><td>Fuerte, rapido y territorial</td></tr>
<tr><td>Habitat</td><td>Jardines, matorrales y bordes de bosque</td></tr>
</tbody>
</table>

<h2>Como reconocerlo</h2>
<p>Es un ave diminuta, de cola corta y comportamiento nervioso. Sube y baja entre ramas con rapidez, se mueve por sectores densos y suele anunciar su presencia antes de verse gracias a su canto.</p>

<h2>Donde vive</h2>
<p>Se adapta a ambientes variados mientras haya refugio vegetal. Puede aparecer en zonas urbanas con jardines, huertos o cercos vivos, ademas de areas mas naturales.</p>

<h2>Que come</h2>
<p>Se alimenta de pequenos insectos y otros invertebrados. Eso lo convierte en un aliado del control biologico en jardines y espacios verdes.</p>

<h2>Comportamiento</h2>
<p>Defiende su territorio con energia, canta desde puntos visibles y aprovecha la cobertura vegetal para moverse sin exponerse demasiado.</p>

<h2>Preguntas frecuentes</h2>
<h3>El chercan es una especie comun?</h3>
<p>Si, es bastante comun en varias zonas de Chile.</p>
<h3>Vive en jardines?</h3>
<p>Si, siempre que haya cobertura vegetal y alimento.</p>
<h3>Se alimenta de frutas?</h3>
<p>Principalmente de insectos y pequenos invertebrados.</p>

<h2>Sigue con aves pequenas y activas</h2>
<p>Si te intereso, revisa la <a href="https://avesnativaschilenas.cl/terrestres/">categoria de aves terrestres</a> y vuelve al portal principal en <a href="https://avesnativaschilenas.cl/">Aves Nativas Chilenas</a>.</p>'''
    },
]


def main():
    print('Reescritura GSC batch 2')
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
