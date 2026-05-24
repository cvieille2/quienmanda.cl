import json, urllib.request, urllib.error, base64

content = r"""
<p><strong>📅 Actualizado: Mayo 2026</strong> — Esta guía fue revisada y actualizada con información reciente para ayudarte a identificar y disfrutar las gaviotas nativas de Chile.</p>

<figure class="wp-block-image"><img decoding="async" loading="lazy" src="https://avesnativaschilenas.cl/wp-content/uploads/2026/05/descubre-todo-sobre-la-fascinante-vida-y-caracteristicas-de-la-gaviota-la-reina-de-los-cielos.webp" alt="caracteristicas, habitat y alimentacion de las gaviotas en Chile" class="wp-image-2337"/></figure>

<p>Las <strong>gaviotas</strong> son <a href="https://avesnativaschilenas.cl/marina/">aves marinas</a> pertenecientes a la <strong>familia Laridae</strong>, y su papel es crucial en el equilibrio de los ecosistemas costeros y marinos de Chile. Estas aves, distribuidas globalmente, presentan una notable adaptabilidad para vivir en diversos entornos, desde playas y acantilados hasta lagos y áreas urbanas a lo largo de todo el territorio chileno.</p>

<p>Entre las especies más comunes en Chile se encuentra la <strong>gaviota dominicana</strong> (<em>Larus dominicanus</em>), la más grande y extendida del país, presente desde Arica hasta Magallanes. Pero Chile alberga al menos <strong>11 especies de gaviotas y gaviotines</strong>, cada una con adaptaciones únicas para prosperar.</p>

<p><a href="#explorando_las_11_especies">Ver las 11 especies de gaviotas en Chile →</a></p>

<h2 class="wp-block-heading">Taxonomía y Clasificación de las Gaviotas en Chile</h2>

<p>Las gaviotas pertenecen al <strong>orden Charadriiformes</strong> y a la <strong>familia Laridae</strong>. Hasta el siglo XXI, la mayoría se clasificaba dentro del género <em>Larus</em>, pero estudios genéticos demostraron que este arreglo era polifilético, lo que llevó a la resurrección de múltiples géneros. En Chile encontramos especies de los siguientes géneros:</p>

<ul class="wp-block-list">
<li><strong>Larus</strong>: Gaviotas grandes de pico robusto. Incluye a la <em>gaviota dominicana</em> (<em>Larus dominicanus</em>), la <em>gaviota del Pacífico</em> (<em>Larus pacificus</em>) y la <em>gaviota peruana</em> (<em>Larus belcheri</em>).</li>
<li><strong>Leucophaeus</strong>: Gaviotas medianas de coloración gris. Incluye a la <em>gaviota de Franklin</em> (<em>Leucophaeus pipixcan</em>), la <em>gaviota garuma</em> (<em>Leucophaeus modestus</em>) y la <em>gaviota austral</em> (<em>Leucophaeus scoresbii</em>).</li>
<li><strong>Chroicocephalus</strong>: Gaviotas pequeñas con capuchón. Incluye a la <em>gaviota cáhuil</em> (<em>Chroicocephalus maculipennis</em>) y la <em>gaviota andina</em> (<em>Chroicocephalus serranus</em>).</li>
<li><strong>Larosterna</strong>: Género monotípico del <em>gaviotín monja</em> (<em>Larosterna inca</em>), endémico de la corriente de Humboldt.</li>
<li><strong>Sterna</strong> y <strong>Sternula</strong>: Gaviotines, como el <em>gaviotín sudamericano</em> (<em>Sterna hirundinacea</em>).</li>
</ul>

<h2 class="wp-block-heading" id="explorando_las_11_especies">Explorando las 11 Especies de Gaviotas que Habitan en Chile</h2>

<p>Chile alberga una gran variedad de gaviotas que se distribuyen desde el extremo norte hasta la Patagonia. Aquí presentamos las especies más representativas con sus características distintivas:</p>

<figure class="wp-block-table"><table class="has-fixed-layout"><thead><tr><th>Especie</th><th>Nombre Científico</th><th>Tamaño</th><th>Distribución en Chile</th><th>Estado UICN</th></tr></thead><tbody>
<tr><td><a href="https://avesnativaschilenas.cl/marina/gaviota/gaviota-dominicana/">Gaviota Dominicana</a></td><td><em>Larus dominicanus</em></td><td>54-65 cm</td><td>Arica a Magallanes</td><td>LC</td></tr>
<tr><td><a href="https://avesnativaschilenas.cl/marina/gaviota/gaviota-de-franklin/">Gaviota de Franklin</a></td><td><em>Leucophaeus pipixcan</em></td><td>32-38 cm</td><td>Toda la costa (migratoria)</td><td>LC</td></tr>
<tr><td><a href="https://avesnativaschilenas.cl/marina/gaviota/gaviota-garuma/">Gaviota Garuma</a></td><td><em>Leucophaeus modestus</em></td><td>40-45 cm</td><td>Antofagasta a Coquimbo</td><td>LC</td></tr>
<tr><td><a href="https://avesnativaschilenas.cl/marina/gaviota/gaviota-peruana/">Gaviota Peruana</a></td><td><em>Larus belcheri</em></td><td>48-53 cm</td><td>Arica a Antofagasta</td><td>LC</td></tr>
<tr><td><a href="https://avesnativaschilenas.cl/marina/gaviota/gaviota-pacifico/">Gaviota del Pacífico</a></td><td><em>Larus pacificus</em></td><td>55-65 cm</td><td>Costa centro-norte</td><td>LC</td></tr>
<tr><td><a href="https://avesnativaschilenas.cl/marina/gaviota/gaviota-cahuil/">Gaviota Cáhuil</a></td><td><em>Chroicocephalus maculipennis</em></td><td>35-40 cm</td><td>Valparaíso a Magallanes</td><td>LC</td></tr>
<tr><td><a href="https://avesnativaschilenas.cl/marina/gaviota/gaviota-andina/">Gaviota Andina</a></td><td><em>Chroicocephalus serranus</em></td><td>40-45 cm</td><td>Altiplano y cordillera</td><td>LC</td></tr>
<tr><td>Gaviota Austral</td><td><em>Leucophaeus scoresbii</em></td><td>44 cm</td><td>Magallanes a Chiloé</td><td>LC</td></tr>
<tr><td><a href="https://avesnativaschilenas.cl/marina/gaviota/gaviotin-monja/">Gaviotín Monja</a></td><td><em>Larosterna inca</em></td><td>39-42 cm</td><td>Costa norte (Humboldt)</td><td>NT</td></tr>
<tr><td><a href="https://avesnativaschilenas.cl/marina/gaviota/gaviotin-sudamericano/">Gaviotín Sudamericano</a></td><td><em>Sterna hirundinacea</em></td><td>35-43 cm</td><td>Toda la costa chilena</td><td>LC</td></tr>
<tr><td>Gaviota Reidora Americana</td><td><em>Leucophaeus atricilla</em></td><td>36-41 cm</td><td>Migratoria ocasional</td><td>LC</td></tr>
</tbody></table></figure>

<p><strong>Distribución</strong>: Aunque predominan en las costas, varias especies también se internan en zonas interiores, adaptándose a cuerpos de agua dulce y áreas urbanas donde encuentran alimentos alternativos.</p>

<p>👉 ¿Te interesan otras aves marinas? Conoce también <a href="https://avesnativaschilenas.cl/marina/">todas las aves marinas de Chile</a>.</p>

<h2 class="wp-block-heading">Características Generales de las Gaviotas</h2>

<p>La gaviota es un ave marina de la familia <strong>Laridae</strong>, famosa por su <strong>plumaje blanco y gris</strong> y su increíble capacidad de adaptación. Como animales sociales y gregarios, suelen formar grandes colonias en zonas protegidas.</p>

<ul class="wp-block-list">
<li><strong>Tamaño y Estructura</strong>: Con una longitud que puede llegar hasta los 75 cm, las gaviotas varían de pequeñas a grandes, siempre con un cuerpo aerodinámico y alas largas. Su envergadura alar puede superar los 150 cm en las especies más grandes.</li>
<li><strong>Coloración</strong>: Su plumaje es mayormente blanco y gris, con marcas negras en alas y cabeza. El plumaje cambia con la edad: los juveniles son pardos y moteados, y tardan entre 2 y 4 años en alcanzar el plumaje adulto.</li>
<li><strong>Pico y patas</strong>: Poseen un pico robusto y ligeramente ganchudo, ideal para desgarrar carne y romper conchas. Sus patas son palmeadas para nadar.</li>
<li><strong>Inteligencia</strong>: Las gaviotas son notablemente inteligentes. Usan herramientas, practican cleptoparasitismo y tienen complejos sistemas de comunicación social.</li>
</ul>

<h2 class="wp-block-heading">Hábitat y Distribución de las Gaviotas en Chile</h2>

<p>Las gaviotas están presentes en casi todos los continentes, pero en Chile tienen una distribución particularmente amplia: desde Arica hasta el Cabo de Hornos.</p>

<p><strong>Hábitats típicos:</strong></p>
<ol class="wp-block-list">
<li><strong>Costas y playas</strong>, donde se alimentan y anidan.</li>
<li><strong>Islas deshabitadas</strong>, ideales para colonias lejos de depredadores.</li>
<li><strong>Áreas urbanas y puertos</strong>, con restos de alimentos.</li>
<li><strong>Lagos y ríos interiores</strong>, en zona sur y patagónica.</li>
<li><strong>Altiplano andino</strong>, donde la gaviota andina habita sobre 3.000 msnm.</li>
</ol>

<h3 class="wp-block-heading">Distribución por región</h3>
<ul class="wp-block-list">
<li><strong>Zona Norte (Arica a Coquimbo)</strong>: Gaviota peruana, gaviota garuma, gaviotín monja, gaviota dominicana.</li>
<li><strong>Zona Centro (Valparaíso a Biobío)</strong>: Gaviota dominicana, gaviota de Franklin, gaviota cáhuil.</li>
<li><strong>Zona Sur y Austral</strong>: Gaviota dominicana, gaviota austral, gaviota cáhuil.</li>
<li><strong>Altiplano</strong>: Gaviota andina.</li>
</ul>

<h2 class="wp-block-heading">Alimentación de las Gaviotas</h2>

<p>Contrario a la creencia común, las gaviotas son <strong>omnívoras</strong>. Su dieta incluye peces, crustáceos, carroña y restos humanos.</p>

<figure class="wp-block-table"><table class="has-fixed-layout"><thead><tr><th>Tipo de Alimento</th><th>Detalle</th></tr></thead><tbody>
<tr><td><strong>Peces</strong></td><td>Anchovetas, sardinas y peces pequeños.</td></tr>
<tr><td><strong>Invertebrados</strong></td><td>Moluscos, crustáceos, gusanos, erizos.</td></tr>
<tr><td><strong>Aves y Polluelos</strong></td><td>Cazan aves más pequeñas y sus crías.</td></tr>
<tr><td><strong>Huevos</strong></td><td>Roban huevos de otras aves costeras.</td></tr>
<tr><td><strong>Carroña</strong></td><td>Animales muertos, lobos marinos, peces varados.</td></tr>
<tr><td><strong>Desechos</strong></td><td>Basura y desperdicios en áreas urbanas.</td></tr>
</tbody></table></figure>

<h3 class="wp-block-heading">Comportamiento Alimenticio</h3>
<ul class="wp-block-list">
<li><strong>Buceo y Natación</strong>: Atrapan peces desde la superficie.</li>
<li><strong>Cleptoparasitismo</strong>: Roban alimento de pelícanos y cormoranes.</li>
<li><strong>Uso de herramientas</strong>: Lanzan mejillones contra rocas para romper conchas.</li>
<li><strong>Recolección</strong>: Buscan invertebrados en playas y rocas.</li>
</ul>

<h2 class="wp-block-heading">Reproducción de las Gaviotas</h2>

<p>Las gaviotas forman parejas monógamas y anidan en colonias densas y ruidosas.</p>
<ul class="wp-block-list">
<li><strong>Temporada</strong>: Octubre a febrero en Chile.</li>
<li><strong>Nidos</strong>: En el suelo, acantilados o islas, con vegetación y algas.</li>
<li><strong>Huevos</strong>: 1-3 por puesta, oliváceos con manchas.</li>
<li><strong>Incubación</strong>: 22-28 días, ambos padres se turnan.</li>
<li><strong>Polluelos</strong>: Semiprecoces, cubiertos de plumón.</li>
</ul>

<h2 class="wp-block-heading">Migración de las Gaviotas</h2>

<p>La <strong>gaviota de Franklin</strong> (<em>Leucophaeus pipixcan</em>) es el caso más notable: se reproduce en Canadá y EE.UU. y migra a Chile durante el verano austral (noviembre a marzo). Otras especies, como la gaviota reidora americana, llegan ocasionalmente como visitantes. Las especies residentes realizan movimientos estacionales locales.</p>

<h2 class="wp-block-heading">Conservación y Amenazas</h2>

<p>La mayoría de las gaviotas chilenas están clasificadas como <strong>Preocupación Menor (LC)</strong> por la UICN, pero enfrentan amenazas como pérdida de hábitat, contaminación plástica, depredadores introducidos, sobrepesca y cambio climático. El <strong>gaviotín monja</strong> es la especie más amenazada (<strong>NT</strong>), por su distribución restringida a la corriente de Humboldt.</p>

<h2 class="wp-block-heading">Consejos para la Observación de Gaviotas en Chile</h2>

<ul class="wp-block-list">
<li><strong>Mejores lugares</strong>: Playas rocosas, puertos, muelles, desembocaduras de ríos.</li>
<li><strong>Mejor época</strong>: Noviembre a marzo (migración de la gaviota de Franklin).</li>
<li><strong>Equipo</strong>: Prismáticos impermeables y guía de aves.</li>
<li><strong>Identificación</strong>: Los juveniles tienen plumaje pardo moteado; los adultos varían según la especie.</li>
<li><strong>Confusiones comunes</strong>: Gaviota cáhuil con gaviota andina en invierno; gaviota austral con dominicana en verano.</li>
</ul>

<h2 class="wp-block-heading">Preguntas Frecuentes sobre las Gaviotas en Chile</h2>

<p><strong>¿Qué tipo de animal es la gaviota?</strong><br>Las gaviotas son aves marinas de la familia Laridae, caracterizadas por su adaptabilidad a entornos costeros y urbanos.</p>

<p><strong>¿Cuál es la dieta de las gaviotas?</strong><br>Son omnívoras: peces, crustáceos, huevos, carroña y restos humanos.</p>

<p><strong>¿Dónde se encuentran las gaviotas en Chile?</strong><br>Desde Arica hasta Magallanes, en costas, lagos, ríos, altiplano y zonas urbanas.</p>

<p><strong>¿Qué especies de gaviotas habitan en Chile?</strong><br>Al menos 11 especies: dominicana, de Franklin, garuma, cáhuil, andina, peruana, del Pacífico, austral, gaviotín monja, gaviotín sudamericano y reidora americana.</p>

<p><strong>¿Cuánto viven las gaviotas?</strong><br>Entre 10 y 20 años en promedio, hasta 30 años en condiciones óptimas.</p>

<p><strong>¿Cómo se reproducen las gaviotas?</strong><br>Forman parejas monógamas, anidan en colonias y ambos padres incuban 1-3 huevos.</p>

<p><strong>¿Las gaviotas migran?</strong><br>Sí, la gaviota de Franklin migra desde Canadá hasta Chile cada año.</p>

<p><strong>¿Son inteligentes las gaviotas?</strong><br>Sí, usan herramientas, roban alimento y tienen compleja comunicación social.</p>

<p><strong>¿Por qué son importantes ecológicamente?</strong><br>Controlan poblaciones menores, limpian restos orgánicos y dispersan nutrientes.</p>

<p><strong>¿Cuál es la gaviota más amenazada de Chile?</strong><br>El gaviotín monja (Larosterna inca), clasificado como Casi Amenazado (NT).</p>

<h2 class="wp-block-heading">Temas relacionados con gaviotas</h2>

<p><a href="https://avesnativaschilenas.cl/marina/gaviota/gaviotas-en-zonas-urbanas/">Gaviotas en Zonas Urbanas</a></p>
<p><a href="https://avesnativaschilenas.cl/marina/gaviota/como-saber-si-una-gaviota-es-macho-o-hembra/">Cómo saber si una gaviota es macho o hembra</a></p>
<p><a href="https://avesnativaschilenas.cl/marina/gaviota/depredadores-de-las-gaviotas-que-amenazas-enfrentan/">Depredadores de las Gaviotas</a></p>
<p><a href="https://avesnativaschilenas.cl/marina/gaviota/historia-cultural-de-las-gaviotas-simbolos-de-libertad-y-mar/">Historia Cultural de las Gaviotas</a></p>

<h2 class="wp-block-heading">Equípate para el avistamiento costero</h2>

<p>👉 <a href="https://amzn.to/3TdvvIt" target="_blank" rel="noreferrer noopener">Prismáticos impermeables en Amazon.es</a></p>
<p>👉 <a href="https://amzn.to/3XcK9pM" target="_blank" rel="noreferrer noopener">Guías de aves marinas de Chile</a></p>
<p><em>Como afiliado de Amazon, gano por compras calificadas.</em></p>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "¿Qué tipo de animal es la gaviota?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Las gaviotas son aves marinas de la familia Laridae, caracterizadas por su adaptabilidad a entornos costeros y urbanos."
      }
    },
    {
      "@type": "Question",
      "name": "¿Cuál es la dieta de las gaviotas?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Son omnívoras: peces, crustáceos, huevos, carroña y restos humanos."
      }
    },
    {
      "@type": "Question",
      "name": "¿Dónde se encuentran las gaviotas en Chile?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Desde Arica hasta Magallanes, en costas, lagos, ríos, altiplano y zonas urbanas."
      }
    },
    {
      "@type": "Question",
      "name": "¿Qué especies de gaviotas habitan en Chile?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Al menos 11 especies: dominicana, de Franklin, garuma, cáhuil, andina, peruana, del Pacífico, austral, gaviotín monja, gaviotín sudamericano y reidora americana."
      }
    },
    {
      "@type": "Question",
      "name": "¿Cuánto viven las gaviotas?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Entre 10 y 20 años en promedio, hasta 30 años en condiciones óptimas."
      }
    },
    {
      "@type": "Question",
      "name": "¿Son inteligentes las gaviotas?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Sí, usan herramientas, roban alimento y tienen compleja comunicación social."
      }
    },
    {
      "@type": "Question",
      "name": "¿Las gaviotas migran?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Sí, la gaviota de Franklin migra desde Canadá hasta Chile cada año."
      }
    },
    {
      "@type": "Question",
      "name": "¿Cuál es la gaviota más amenazada de Chile?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "El gaviotín monja (Larosterna inca), clasificado como Casi Amenazado (NT) por la UICN."
      }
    }
  ]
}
</script>
"""

content = '\n'.join(line for line in content.split('\n'))

payload = json.dumps({
    "content": content,
    "title": "Gaviotas en Chile: Especies, Tipos y Guia de Identificacion",
    "status": "publish"
}).encode('utf-8')

req = urllib.request.Request(
    "https://avesnativaschilenas.cl/wp-json/wp/v2/pages/14226",
    data=payload,
    method="PUT"
)
creds = ("cvieille", "u0wM 1VRi v9wL Z71R 7XCx mnEq")
encoded = base64.b64encode(f"{creds[0]}:{creds[1]}".encode()).decode()
req.add_header("Authorization", f"Basic {encoded}")
req.add_header("Content-Type", "application/json")
req.add_header("User-Agent", "opencode/1.0")

try:
    response = urllib.request.urlopen(req)
    result = json.loads(response.read())
    print("OK - Publicado correctamente")
    print("   ID: %s" % result.get('id'))
    print("   Slug: %s" % result.get('slug'))
    print("   Status: %s" % result.get('status'))
    print("   Link: %s" % result.get('link'))
except urllib.error.HTTPError as e:
    print("Error HTTP %d: %s" % (e.code, e.read().decode()[:600]))
except Exception as e:
    print("Error: %s" % e)
