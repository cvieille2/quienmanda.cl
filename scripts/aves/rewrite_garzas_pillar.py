import json
import urllib.request
import urllib.error
import base64

creds = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
auth = 'Basic ' + base64.b64encode(f'{creds[0]}:{creds[1]}'.encode()).decode()
base = 'https://avesnativaschilenas.cl/wp-json/wp/v2'

content = r'''
<p><strong>📅 Actualizado: Mayo 2026</strong> — Esta guía reúne las garzas más representativas de Chile para ayudarte a identificarlas, entender su comportamiento y conocer su papel en los humedales.</p>

<p>Las <strong>garzas en Chile</strong> son aves acuáticas elegantes, silenciosas y muy ligadas a humedales, ríos, estuarios y lagunas. Dentro del sitio, esta página funciona como el <strong>pilar principal</strong> para comparar especies, entrar a sus fichas y fortalecer el cluster temático de garzas chilenas.</p>

<p>Si quieres ir directo a las fichas, aquí tienes las especies destacadas del sitio: <a href="https://avesnativaschilenas.cl/garzas/huairavillo/">Huairavillo</a>, <a href="https://avesnativaschilenas.cl/garzas/garza-grande/">Garza Grande</a> y <a href="https://avesnativaschilenas.cl/garzas/garza-cuca/">Garza Cuca</a>.</p>

<h2 class="wp-block-heading">Tipos de garzas en Chile</h2>

<figure class="wp-block-table"><table class="has-fixed-layout"><thead><tr><th>Especie</th><th>Hábitat principal</th><th>Rasgo clave</th><th>Ficha</th></tr></thead><tbody>
<tr><td>Huairavillo</td><td>Humedales, totorales y bordes de agua</td><td>Pequeño, discreto y muy ligado a la vegetación acuática</td><td><a href="https://avesnativaschilenas.cl/garzas/huairavillo/">Ver ficha</a></td></tr>
<tr><td>Garza Grande</td><td>Lagos, ríos, humedales y orillas tranquilas</td><td>Gran tamaño y plumaje blanco muy visible</td><td><a href="https://avesnativaschilenas.cl/garzas/garza-grande/">Ver ficha</a></td></tr>
<tr><td>Garza Cuca</td><td>Estuarios, lagunas, costas y cuerpos de agua con vegetación</td><td>Más compacta y muy eficiente cazando en la orilla</td><td><a href="https://avesnativaschilenas.cl/garzas/garza-cuca/">Ver ficha</a></td></tr>
</tbody></table></figure>

<h2 class="wp-block-heading">Qué son las garzas</h2>

<p>Las garzas son aves de patas largas, cuello fino y pico recto, especializadas en cazar en aguas poco profundas. Su silueta estilizada y su forma de caminar con paciencia las vuelven fáciles de reconocer en campo.</p>

<p>En Chile, la presencia de garzas es un buen indicador de la salud de humedales y cuerpos de agua. Cuando un sitio mantiene alimento, refugio y tranquilidad, estas aves tienden a aparecer con regularidad.</p>

<h2 class="wp-block-heading">Hábitats y distribución</h2>

<p>Las garzas chilenas se distribuyen en una gran variedad de ambientes acuáticos. Pueden verse en lagunas interiores, desembocaduras, marismas, canales, humedales urbanos y zonas costeras.</p>

<ul class="wp-block-list">
<li><strong>Humedales</strong>: zonas de totoras, juncos y aguas someras.</li>
<li><strong>Ríos y lagunas</strong>: sectores tranquilos con buena disponibilidad de peces y anfibios.</li>
<li><strong>Costas y estuarios</strong>: especialmente donde hay mezcla de agua dulce y salada.</li>
<li><strong>Áreas rurales</strong>: canales, tranques y bordes de cultivo con agua permanente.</li>
</ul>

<h2 class="wp-block-heading">Cómo se alimentan</h2>

<p>La dieta de las garzas es principalmente carnívora. Capturan presas pequeñas con un movimiento rápido del pico, después de permanecer quietas o desplazarse lentamente por la orilla.</p>

<ul class="wp-block-list">
<li>Peces pequeños</li>
<li>Ranas y renacuajos</li>
<li>Insectos y larvas</li>
<li>Crustáceos y otros invertebrados</li>
<li>Reptiles pequeños, según la especie y el hábitat</li>
</ul>

<h2 class="wp-block-heading">Reproducción de las garzas</h2>

<p>Las garzas suelen reproducirse en colonias o en zonas de nidificación bien protegidas. Construyen nidos con ramas, juncos o vegetación disponible, normalmente cerca del agua para reducir el esfuerzo de alimentación de los polluelos.</p>

<figure class="wp-block-table"><table class="has-fixed-layout"><thead><tr><th>Etapa</th><th>Qué ocurre</th></tr></thead><tbody>
<tr><td>Cortejo</td><td>La pareja muestra posturas y vocalizaciones de atracción.</td></tr>
<tr><td>Nido</td><td>Se arma con material vegetal en árboles, juncos o vegetación densa.</td></tr>
<tr><td>Puesta</td><td>Se ponen huevos y ambos padres participan en el cuidado.</td></tr>
<tr><td>Cría</td><td>Los polluelos dependen de los adultos hasta ganar autonomía.</td></tr>
</tbody></table></figure>

<h2 class="wp-block-heading">Cómo diferenciar las especies más visibles</h2>

<p>En el campo, la forma más simple de diferenciarlas es mirar tamaño, comportamiento y hábitat:</p>

<ul class="wp-block-list">
<li><strong>Huairavillo</strong>: más pequeño y reservado, suele ocultarse entre la vegetación.</li>
<li><strong>Garza Grande</strong>: la más llamativa por su tamaño y coloración clara.</li>
<li><strong>Garza Cuca</strong>: útil para reconocer en estuarios, bordes costeros y sitios con mucha vida pequeña.</li>
</ul>

<h2 class="wp-block-heading">Conservación y amenazas</h2>

<p>Las principales amenazas para las garzas en Chile son la pérdida de humedales, la contaminación del agua, la presión urbana y la alteración de zonas de anidación. Proteger estos espacios beneficia no solo a las garzas, sino a todo el ecosistema asociado.</p>

<p>La conservación efectiva pasa por mantener agua limpia, frenar el relleno de humedales y respetar las zonas de descanso y nidificación.</p>

<h2 class="wp-block-heading">Cuándo y dónde observar garzas en Chile</h2>

<ul class="wp-block-list">
<li><strong>Mejor hora</strong>: temprano en la mañana o al atardecer.</li>
<li><strong>Mejores sitios</strong>: bordes de lagunas, humedales urbanos, estuarios y riberas tranquilas.</li>
<li><strong>Consejo</strong>: mantén distancia para no espantar a las aves en alimentación o nidificación.</li>
</ul>

<h2 class="wp-block-heading">Preguntas frecuentes sobre las garzas en Chile</h2>

<p><strong>¿Qué comen las garzas?</strong><br>Principalmente peces, anfibios, insectos y otros animales pequeños que capturan en aguas someras.</p>

<p><strong>¿Dónde se ven más?</strong><br>En humedales, ríos, lagunas, estuarios y zonas costeras con vegetación acuática.</p>

<p><strong>¿La Garza Grande es la más fácil de reconocer?</strong><br>Sí, por su tamaño, su plumaje claro y su presencia llamativa en cuerpos de agua abiertos.</p>

<p><strong>¿El Huairavillo vive en el agua?</strong><br>No exactamente; se mueve en bordes de humedales y zonas con vegetación densa donde puede esconderse mejor.</p>

<p><strong>¿Las garzas son importantes para el ecosistema?</strong><br>Sí, porque ayudan a equilibrar poblaciones de presas pequeñas y funcionan como indicador de humedales sanos.</p>

<p><strong>¿Cómo puedo ayudar a conservarlas?</strong><br>Evita contaminar, respeta las zonas de nidificación y apoya la protección de humedales.</p>

<h2 class="wp-block-heading">Más lectura dentro del cluster</h2>

<p><a href="https://avesnativaschilenas.cl/garzas/huairavillo/">Huairavillo: Guardián de los Humedales de Chile</a></p>
<p><a href="https://avesnativaschilenas.cl/garzas/garza-grande/">Descubre todo sobre la majestuosa garza grande</a></p>
<p><a href="https://avesnativaschilenas.cl/garzas/garza-cuca/">Garza Cuca: Hábitat, características y curiosidades</a></p>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "¿Qué comen las garzas?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Principalmente peces, anfibios, insectos y otros animales pequeños que capturan en aguas someras."
      }
    },
    {
      "@type": "Question",
      "name": "¿Dónde se ven más las garzas en Chile?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "En humedales, ríos, lagunas, estuarios y zonas costeras con vegetación acuática."
      }
    },
    {
      "@type": "Question",
      "name": "¿La Garza Grande es la más fácil de reconocer?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Sí, por su tamaño, su plumaje claro y su presencia llamativa en cuerpos de agua abiertos."
      }
    },
    {
      "@type": "Question",
      "name": "¿El Huairavillo vive en el agua?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "No exactamente; se mueve en bordes de humedales y zonas con vegetación densa donde puede esconderse mejor."
      }
    },
    {
      "@type": "Question",
      "name": "¿Las garzas son importantes para el ecosistema?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Sí, porque ayudan a equilibrar poblaciones de presas pequeñas y funcionan como indicador de humedales sanos."
      }
    },
    {
      "@type": "Question",
      "name": "¿Cómo puedo ayudar a conservarlas?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Evita contaminar, respeta las zonas de nidificación y apoya la protección de humedales."
      }
    }
  ]
}
</script>
'''

payload = json.dumps({
    'content': content,
    'title': 'Descubre la fascinante vida de las garzas: todo lo que necesitas saber',
    'status': 'publish'
}).encode('utf-8')

req = urllib.request.Request(f'{base}/pages/14378', data=payload, method='PUT')
req.add_header('Authorization', auth)
req.add_header('Content-Type', 'application/json')
req.add_header('User-Agent', 'opencode/1.0')

try:
    with urllib.request.urlopen(req) as resp:
        data = json.loads(resp.read())
        print('OK', data.get('id'), data.get('status'), data.get('link'))
except urllib.error.HTTPError as e:
    print('HTTPERROR', e.code, e.read().decode()[:800])
