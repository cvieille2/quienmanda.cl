"""
create_comparativas_faq.py

Crea las paginas /comparativas/ y /faq/ para fortalecer el menu del sitio.
"""

import requests

AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2'


def create_page(title, slug, content, excerpt):
    payload = {
        'title': title,
        'slug': slug,
        'content': content,
        'excerpt': excerpt,
        'status': 'publish',
    }
    return requests.post(f'{BASE}/pages', auth=AUTH, json=payload, timeout=30)


def main():
    comparativas = '''<p>Esta pagina reúne comparaciones utiles para decidir mejor y avanzar mas rapido hacia contenido con potencial de monetizacion. Aqui no solo comparamos especies: tambien te ayudamos a elegir la mejor ruta de lectura segun la intencion de busqueda.</p>

<h2>Comparaciones mas utiles</h2>
<ul>
<li><a href="https://avesnativaschilenas.cl/picaflor/que-diferencia-hay-entre-un-colibri-y-un-picaflor/">Colibri vs picaflor</a></li>
<li><a href="https://avesnativaschilenas.cl/cuidados/como-distinguir-un-pavo-real-macho-de-una-hembra/">Pavo real macho vs hembra</a></li>
<li><a href="https://avesnativaschilenas.cl/blog/grupo-de-aves-marinas-al-que-pertenecen-las-gaviotas/">Gaviotas y aves marinas</a></li>
<li><a href="https://avesnativaschilenas.cl/rapaces/">Rapaces chilenas</a></li>
</ul>

<h2>Para quien sirve esta seccion</h2>
<p>Sirve para lectores que quieren comparar especies, aprender a distinguirlas o pasar desde una consulta general a una guia especifica. Tambien ayuda a distribuir autoridad hacia las paginas que posicionan mejor y pueden generar clicks adicionales.</p>

<h2>Que encontraras aqui</h2>
<p>Comparativas visuales, diferencias clave, respuestas rapidas y enlaces hacia guias mas completas. Si una comparacion tiene intencion de compra o de decision, tambien apunta a paginas con productos, cuidados o recursos utiles.</p>

<p>Si quieres seguir explorando, vuelve al <a href="https://avesnativaschilenas.cl/">inicio del sitio</a> o revisa la <a href="https://avesnativaschilenas.cl/terrestres/">categoria de aves terrestres</a>.</p>'''

    faq = '''<p>Respuestas rapidas a las dudas mas comunes sobre aves chilenas, observacion, cuidados y uso del sitio. Esta seccion mezcla ayuda al usuario con rutas internas hacia las paginas que mejor convierten.</p>

<h2>Preguntas frecuentes</h2>
<h3>Donde empiezo si quiero aprender aves de Chile?</h3>
<p>Empieza por la <a href="https://avesnativaschilenas.cl/">portada</a> y luego sigue por las categorias principales: acuaticas, marinas, migratorias, rapaces y terrestres.</p>
<h3>Que paginas son mejores para comparar especies?</h3>
<p>La seccion de <a href="https://avesnativaschilenas.cl/comparativas/">comparativas</a> y las guias especificas de cada grupo.</p>
<h3>Donde veo contenido mas practico o comercial?</h3>
<p>En <a href="https://avesnativaschilenas.cl/jaulas/">jaulas</a>, <a href="https://avesnativaschilenas.cl/nidos/">nidos</a>, <a href="https://avesnativaschilenas.cl/libros/">libros</a> y <a href="https://avesnativaschilenas.cl/tienda/comederos/">comederos</a>.</p>
<h3>Que hago si encuentro un ave herida?</h3>
<p>Busca ayuda profesional o de rescate local cuanto antes. Si el caso es de palomas o aves de patio, revisa la seccion de <a href="https://avesnativaschilenas.cl/ciudados/">cuidados</a>.</p>
<h3>El sitio vende productos?</h3>
<p>El sitio prioriza contenido informativo, pero usa paginas recomendadas para orientar a productos utiles y decisiones de compra.</p>
<h3>Como encuentro una especie concreta?</h3>
<p>Usa el buscador del sitio o entra directo a la categoria correspondiente.</p>'''

    r1 = create_page(
        'Comparativas de aves en Chile: diferencias utiles para decidir mejor',
        'comparativas',
        comparativas,
        'Comparaciones utiles entre especies, guias y rutas con mejor intencion de busqueda y monetizacion.'
    )
    print('comparativas', r1.status_code, r1.text[:300])

    r2 = create_page(
        'Preguntas frecuentes sobre aves nativas chilenas',
        'faq',
        faq,
        'Preguntas frecuentes sobre aves de Chile, comparativas, cuidados y paginas recomendadas.'
    )
    print('faq', r2.status_code, r2.text[:300])


if __name__ == '__main__':
    main()
