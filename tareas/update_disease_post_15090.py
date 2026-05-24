import json
import requests

AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'


def tag_id(slug):
    r = requests.get(BASE + 'tags', auth=AUTH, params={'slug': slug}, timeout=30)
    if r.status_code == 200 and r.json():
        return r.json()[0]['id']
    r2 = requests.post(BASE + 'tags', auth=AUTH, json={'name': slug.replace('-', ' ').title(), 'slug': slug}, timeout=30)
    r2.raise_for_status()
    return r2.json()['id']


tag_ids = []
for slug in ['aves-nativas', 'cuidados', 'palomas', 'conservacion', 'control-palomas']:
    tid = tag_id(slug)
    if tid not in tag_ids:
        tag_ids.append(tid)

content = '''<p>Cuando alguien rescata una paloma, suele aparecer el mismo comentario: "esas aves son ratas con alas". La frase suena fuerte, pero simplifica demasiado un tema que en realidad tiene matices. Las palomas conviven con nosotros desde hace siglos y, aunque pueden generar problemas sanitarios en ciertos contextos, no representan una amenaza automatica por el solo hecho de estar cerca.</p>

<p>La clave esta en separar el mito del riesgo real. Una paloma en la plaza no te enferma por mirarla, tocarla a distancia o verla volar. El problema aparece cuando hay acumulacion de heces secas, polvo en espacios cerrados, mala limpieza o personas con defensas bajas. Si quieres entender mejor el contexto de esta ave, tambien puedes revisar la <a href="https://avesnativaschilenas.cl/migratorias/paloma/">guia de palomas en Chile</a> y la pagina sobre <a href="https://avesnativaschilenas.cl/ciudados/">cuidados</a>.</p>

<h2>Por que las palomas tienen tan mala fama</h2>
<p>Su presencia masiva en ciudades hizo que se las asocie con suciedad, plagas y enfermedad. Ademas, muchas personas las alimentan en plazas o balcones, lo que concentra grupos grandes en un mismo lugar y aumenta la cantidad de excremento. A eso se suma que sus nidos suelen ubicarse en techos, cornisas y bodegas, justo donde la gente no mira hasta que el problema ya esta avanzado.</p>

<p>No es raro que esa mezcla de contacto urbano, excrementos visibles y un aspecto poco simpatico termine en un apodo despectivo. Pero el apodo no cambia la biologia: las palomas no son ratas, ni transmiten enfermedades de la misma forma que un rumor en internet.</p>

<h2>Que enfermedades se asocian realmente</h2>
<p>El riesgo sanitario mas citado no viene del ave en si, sino de <strong>las heces secas</strong> y del polvo que pueden levantar al barrerse sin precaucion. Entre los problemas mas mencionados estan:</p>
<ul>
<li><strong>Criptococosis</strong>: infeccion por hongos que puede afectar sobre todo a personas inmunocomprometidas.</li>
<li><strong>Histoplasmosis</strong>: tambien relacionada con hongos presentes en materia organica acumulada.</li>
<li><strong>Salmonelosis</strong>: posible en ambientes contaminados y con mala higiene.</li>
<li><strong>Problemas alergicos o respiratorios</strong>: por polvo y suciedad acumulada en espacios cerrados.</li>
</ul>

<p>Lo importante es esto: <strong>la gran mayoria de las personas sanas no se enferma por una exposicion casual</strong>. El riesgo sube cuando hay acumulacion prolongada, contacto con polvo contaminado o ventilacion deficiente.</p>

<h2>Quien debe tener mas cuidado</h2>
<p>Las personas con defensas bajas, adultos mayores fragiles, pacientes con enfermedades pulmonares y quienes estan inmunosuprimidos deben extremar precauciones. En esos casos, no conviene limpiar guano seco sin proteccion. Si la acumulacion es importante, lo correcto es pedir ayuda profesional.</p>

<h2>Como limpiar heces de paloma sin levantar riesgo</h2>
<p>Si el problema es pequeno, una limpieza correcta reduce mucho el riesgo:</p>
<ul>
<li>Usa guantes y, si puedes, mascarilla N95 o superior.</li>
<li>Humedece primero las heces para que no se levante polvo.</li>
<li>No barras en seco ni sacudas restos viejos.</li>
<li>Retira el material con un pano humedo o espatula desechable.</li>
<li>Desinfecta la superficie al terminar.</li>
<li>Lava bien manos y ropa de trabajo.</li>
</ul>

<p>Si la acumulacion esta en un entretecho, ducto de ventilacion o zona alta de dificil acceso, lo prudente es no improvisar.</p>

<h2>Como reducir el problema sin danar a las aves</h2>
<p>La solucion mas efectiva no es el castigo, sino la prevencion. Retirar comida disponible, sellar grietas, bloquear repisas de posado y mantener limpias las superficies reduce la presencia de palomas de forma sostenida. Si quieres comparar alternativas, revisa la <a href="https://avesnativaschilenas.cl/comparativas/">seccion de comparativas</a> y los recursos de la <a href="https://avesnativaschilenas.cl/faq/">FAQ</a>.</p>

<div class="afiliado-recomendacion" style="background:#f0f0f0;padding:15px;border-radius:8px;margin:20px 0;border-left:4px solid #e74c3c;">
<p><strong>Palomas en balcones o techos? Soluciones responsables</strong></p>
<p>Si el problema vuelve una y otra vez, conviene usar barreras y disuasivos en lugar de improvisar con medidas agresivas:</p>
<p><a href="https://www.amazon.es/s?k=disuasivo+palomas&tag=avesnativas-21" target="_blank" rel="nofollow sponsored">Disuasivos de palomas en Amazon.es</a></p>
<p><a href="https://www.amazon.es/s?k=comedero+aves+selectivo&tag=avesnativas-21" target="_blank" rel="nofollow sponsored">Comederos selectivos para aves nativas</a></p>
<p><em>Como afiliado de Amazon, gano por compras calificadas.</em></p>
</div>

<h2>Lo que realmente deberia preocuparnos</h2>
<p>El riesgo no es la paloma aislada, sino el <strong>entorno sucio y acumulado</strong>. Eso aplica a palomas, a otras aves urbanas y a cualquier animal cuya suciedad se deje sin atender durante mucho tiempo. Por eso la respuesta mas inteligente es higiene, control del acceso a comida y manejo adecuado de los puntos de anidacion.</p>

<p>Si el caso es una paloma herida, o si quieres entender mejor por que estas aves siguen tan presentes en ciudades y plazas, puedes leer tambien <a href="https://avesnativaschilenas.cl/blog/que-piensas-que-sucede-con-las-aves-originarias-de-un-lugar-cuando-llegan-las-palomas/">el analisis sobre su impacto en aves nativas</a> y la guia de <a href="https://avesnativaschilenas.cl/ciudados/">cuidados</a>.</p>

<h2>Preguntas frecuentes</h2>
<h3>Las palomas transmiten enfermedades por tocarlas?</h3>
<p>No por el simple contacto. El riesgo serio aparece sobre todo por polvo de heces secas o superficies muy contaminadas.</p>
<h3>Es peligroso tener una paloma en el patio?</h3>
<p>No necesariamente. El problema es la acumulacion de restos y la falta de limpieza en lugares donde anidan.</p>
<h3>Que hago si encuentro muchas heces en mi balcon?</h3>
<p>Humedece, limpia con proteccion y desinfecta. Si es mucho material o esta en altura, llama a un profesional.</p>
<h3>Las palomas son mas peligrosas que otras aves?</h3>
<p>No especialmente. El riesgo depende de la suciedad acumulada y del manejo sanitario, no solo de la especie.</p>
<h3>Deberia dejar de alimentar palomas?</h3>
<p>Si quieres evitar que se concentren, si. Alimentarlas solo aumenta la presencia y empeora el problema.</p>
<h3>Donde encuentro opciones para controlarlas?</h3>
<p>Revisa la <a href="https://avesnativaschilenas.cl/comparativas/">seccion de comparativas</a> y la pagina de <a href="https://avesnativaschilenas.cl/faq/">preguntas frecuentes</a>.</p>

<script type="application/ld+json">''' + json.dumps({
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    'mainEntity': [
        {'@type': 'Question', 'name': 'Las palomas transmiten enfermedades por tocarlas?', 'acceptedAnswer': {'@type': 'Answer', 'text': 'No por el simple contacto. El riesgo serio aparece sobre todo por polvo de heces secas o superficies muy contaminadas.'}},
        {'@type': 'Question', 'name': 'Es peligroso tener una paloma en el patio?', 'acceptedAnswer': {'@type': 'Answer', 'text': 'No necesariamente. El problema es la acumulacion de restos y la falta de limpieza en lugares donde anidan.'}},
        {'@type': 'Question', 'name': 'Que hago si encuentro muchas heces en mi balcon?', 'acceptedAnswer': {'@type': 'Answer', 'text': 'Humedece, limpia con proteccion y desinfecta. Si es mucho material o esta en altura, llama a un profesional.'}},
        {'@type': 'Question', 'name': 'Las palomas son mas peligrosas que otras aves?', 'acceptedAnswer': {'@type': 'Answer', 'text': 'No especialmente. El riesgo depende de la suciedad acumulada y del manejo sanitario, no solo de la especie.'}},
        {'@type': 'Question', 'name': 'Deberia dejar de alimentar palomas?', 'acceptedAnswer': {'@type': 'Answer', 'text': 'Si quieres evitar que se concentren, si. Alimentarlas solo aumenta la presencia y empeora el problema.'}},
        {'@type': 'Question', 'name': 'Donde encuentro opciones para controlarlas?', 'acceptedAnswer': {'@type': 'Answer', 'text': 'Revisa la seccion de comparativas y la pagina de preguntas frecuentes.'}},
    ],
}, ensure_ascii=False) + '</script>'

payload = {
    'title': 'Las palomas transmiten enfermedades? Mitos, riesgos reales y limpieza segura',
    'excerpt': 'Descubre si las palomas transmiten enfermedades, que riesgos reales existen y como limpiar sus heces con seguridad.',
    'content': content,
    'tags': tag_ids,
}

resp = requests.post(BASE + 'posts/15090', auth=AUTH, json=payload, timeout=30)
print(resp.status_code)
