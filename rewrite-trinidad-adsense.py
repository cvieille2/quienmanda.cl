import requests, json, re

auth = ("cvieille", "QCZg BNQs mcnf HMXE OvBm MXpz")
site = "https://infomoteles.cl"
post_id = 3031

new_title = "Motel Trinidad La Florida: Tel\u00e9fono, Precios, Habitaciones y C\u00f3mo Llegar"

new_content = """<p><strong>Motel Trinidad en La Florida</strong> es una opci\u00f3n pr\u00e1ctica para quienes buscan un motel c\u00f3modo, con horario 24 horas y estacionamiento en la zona suroriente de Santiago. En esta gu\u00eda encontrar\u00e1s toda la informaci\u00f3n actualizada: tel\u00e9fono de contacto, direcci\u00f3n exacta, servicios disponibles y precios de referencia. Tambi\u00e9n incluimos una secci\u00f3n con otros moteles en La Florida para que puedas comparar antes de decidir.</p>

<h2>Datos r\u00e1pidos del Motel Trinidad</h2>
<figure class=\"wp-block-table\"><table>
<thead><tr><th>Informaci\u00f3n</th><th>Detalle</th></tr></thead>
<tbody>
<tr><td><strong>Nombre</strong></td><td>Motel Trinidad</td></tr>
<tr><td><strong>Direcci\u00f3n</strong></td><td>Trinidad 0476, La Florida, Santiago</td></tr>
<tr><td><strong>Tel\u00e9fono</strong></td><td><a href=\"tel:+56222811939\">+56 2 2281 1939</a></td></tr>
<tr><td><strong>Horario</strong></td><td>24 horas, todos los d\u00edas</td></tr>
<tr><td><strong>Estacionamiento</strong></td><td>S\u00ed, privado</td></tr>
<tr><td><strong>Pago</strong></td><td>Efectivo y tarjetas de d\u00e9bito/cr\u00e9dito</td></tr>
</tbody></table></figure>

<h2>\u00bfD\u00f3nde est\u00e1 el Motel Trinidad y c\u00f3mo llegar?</h2>
<p>El Motel Trinidad se ubica en <strong>Trinidad 0476</strong>, en la comuna de <strong>La Florida</strong>, Regi\u00f3n Metropolitana. Es una zona residencial con buen acceso vehicular.</p>

<p><strong>C\u00f3mo llegar desde el centro de Santiago:</strong></p>
<ul>
<li>En auto: toma la Autopista Vespucio Sur hasta la salida La Florida. Son aproximadamente 20\u201325 minutos en horario normal.</li>
<li>En metro: b\u00e1jate en la estaci\u00f3n <strong>La Florida</strong> (L\u00ednea 4) y desde all\u00ed toma un taxi o micro hasta Trinidad 0476.</li>
</ul>

<h2>Servicios del Motel Trinidad</h2>
<p>El motel ofrece una variedad de servicios pensados para una estad\u00eda placentera en pareja. Esto es lo que puedes esperar:</p>

<figure class=\"wp-block-table\"><table>
<thead><tr><th>Servicio</th><th>Disponible</th></tr></thead>
<tbody>
<tr><td>Jacuzzi</td><td>\u2705</td></tr>
<tr><td>Hidromasaje</td><td>\u2705</td></tr>
<tr><td>Aire acondicionado</td><td>\u2705</td></tr>
<tr><td>Calefacci\u00f3n</td><td>\u2705</td></tr>
<tr><td>TV cable / Smart TV</td><td>\u2705</td></tr>
<tr><td>Wi-Fi</td><td>\u2705</td></tr>
<tr><td>Estacionamiento privado</td><td>\u2705</td></tr>
<tr><td>Room service 24h</td><td>\u2705</td></tr>
<tr><td>Equipo de m\u00fasica</td><td>\u2705</td></tr>
<tr><td>Habitaciones tem\u00e1ticas</td><td>\u2705</td></tr>
</tbody></table></figure>

<h2>Tipos de habitaciones</h2>
<p>Las habitaciones del Motel Trinidad destacan por ser <strong>amplias, modernas y bien equipadas</strong>. Cada una cuenta con:</p>
<ul>
<li>Cama c\u00f3moda con ropa de cama de calidad</li>
<li>Ba\u00f1o privado con ducha de hidromasaje</li>
<li>Aire acondicionado y calefacci\u00f3n individual</li>
<li>Smart TV con cable premium</li>
<li>Wi-Fi de alta velocidad</li>
<li>Toallas de lujo y amenities</li>
</ul>
<p>El motel ofrece <strong>habitaciones tem\u00e1ticas</strong> para quienes buscan una experiencia diferente en cada visita. Puedes consultar la disponibilidad llamando directamente al <a href=\"tel:+56222811939\">+56 2 2281 1939</a>.</p>

<h2>Precios del Motel Trinidad</h2>
<p>Los precios var\u00edan seg\u00fan la habitaci\u00f3n, el horario y la duraci\u00f3n de la estad\u00eda. A modo de referencia:</p>
<blockquote class=\"wp-block-quote\"><p><strong>Importante:</strong> Los precios cambian seg\u00fan demanda y temporada. Siempre confirma directamente por tel\u00e9fono antes de ir.</p></blockquote>
<p><strong>Rango estimado:</strong></p>
<ul>
<li>Estad\u00eda por horas: desde $15.000 \u2013 $30.000 aproximadamente</li>
<li>Estad\u00eda por noche: consultar tarifa actualizada llamando al <a href=\"tel:+56222811939\">+56 2 2281 1939</a></li>
</ul>

<h2>Horarios y reservas</h2>
<p>El Motel Trinidad <strong>atiende las 24 horas del d\u00eda, los 7 d\u00edas de la semana</strong>. Puedes llegar a cualquier hora sin necesidad de reserva previa, aunque siempre es recomendable llamar antes si buscas una habitaci\u00f3n espec\u00edfica o tem\u00e1tica.</p>

<p><strong>Para reservar:</strong></p>
<ul>
<li>\uD83D\uDCDE Tel\u00e9fono: <a href=\"tel:+56222811939\">+56 2 2281 1939</a></li>
<li>\u2705 Entrada directa: disponible 24/7</li>
</ul>

<h2>Preguntas frecuentes sobre el Motel Trinidad</h2>

<h3>\u00bfEl Motel Trinidad tiene jacuzzi?</h3>
<p>S\u00ed, cuenta con jacuzzi e hidromasaje en las habitaciones.</p>

<h3>\u00bfAceptan tarjetas de cr\u00e9dito?</h3>
<p>S\u00ed, aceptan efectivo y tarjetas de d\u00e9bito/cr\u00e9dito. Consulta al llegar las opciones disponibles.</p>

<h3>\u00bfHay estacionamiento?</h3>
<p>S\u00ed, tiene estacionamiento privado y seguro para los hu\u00e9spedes.</p>

<h3>\u00bfSe puede entrar a cualquier hora?</h3>
<p>S\u00ed, el motel est\u00e1 abierto 24/7. Puedes llegar a la hora que prefieras.</p>

<h3>\u00bfTienen habitaciones con hidromasaje?</h3>
<p>S\u00ed, todas las habitaciones cuentan con ducha de hidromasaje.</p>

<h3>\u00bfCu\u00e1nto cuesta la estad\u00eda?</h3>
<p>Los precios dependen de la habitaci\u00f3n y el horario. Te recomendamos llamar al <a href=\"tel:+56222811939\">+56 2 2281 1939</a> para obtener un precio exacto.</p>

<h2>Otros moteles en La Florida</h2>
<p>Si quieres explorar m\u00e1s opciones antes de decidir, estos son otros moteles en la misma comuna:</p>
<ul>
<li><a href=\"https://infomoteles.cl/la-florida/motel-los-grillos-la-florida/\">Motel Los Grillos, La Florida</a></li>
<li><a href=\"https://infomoteles.cl/la-florida/motel-ipanema-la-florida/\">Motel Ipanema, La Florida</a></li>
<li><a href=\"https://infomoteles.cl/la-florida/motel-niagara-la-florida/\">Motel Niagara, La Florida</a></li>
<li><a href=\"https://infomoteles.cl/la-florida/motel-la-giralda-florida/\">Motel La Giralda, La Florida</a></li>
<li><a href=\"https://infomoteles.cl/la-florida/motel-florida-la-florida/\">Motel Florida, La Florida</a></li>
<li><a href=\"https://infomoteles.cl/la-florida/motel-luciernagas-la-florida/\">Motel Luci\u00e9rnagas, La Florida</a></li>
</ul>
<p>Si prefieres ver el listado completo, visita la p\u00e1gina de <a href=\"https://infomoteles.cl/la-florida/\">Moteles en La Florida</a>.</p>

<h2>Moteles en otras comunas de Santiago</h2>
<p>Tambi\u00e9n puedes buscar opciones cercanas en otras zonas:</p>
<ul>
<li><a href=\"https://infomoteles.cl/santiago/\">Moteles en Santiago Centro</a></li>
<li><a href=\"https://infomoteles.cl/providencia/\">Moteles en Providencia</a></li>
<li><a href=\"https://infomoteles.cl/vitacura/\">Moteles en Vitacura</a></li>
<li><a href=\"https://infomoteles.cl/lo-espejo/\">Moteles en Lo Espejo</a></li>
<li><a href=\"https://infomoteles.cl/pudahuel/\">Moteles en Pudahuel</a></li>
</ul>

<h2>Consejos para elegir un motel en La Florida</h2>
<p>Antes de decidirte por un motel, ten en cuenta estos puntos:</p>
<ol>
<li><strong>Ubicaci\u00f3n:</strong> Elige un motel cerca de tu ruta. La Florida es una comuna grande y los tiempos de traslado pueden variar.</li>
<li><strong>Servicios:</strong> Si buscas algo espec\u00edfico (jacuzzi, hidromasaje, habitaci\u00f3n tem\u00e1tica), confirma antes de llegar.</li>
<li><strong>Precio:</strong> Siempre pregunta el valor por horas y por noche. Algunos moteles tienen tarifas especiales en horario de baja demanda.</li>
<li><strong>Horario:</strong> La mayor\u00eda de los moteles en La Florida abren 24 horas, pero no est\u00e1 de m\u00e1s confirmar.</li>
<li><strong>Estacionamiento:</strong> Si vas en auto, verifica que el motel tenga estacionamiento disponible.</li>
</ol>

<h2>Conclusi\u00f3n</h2>
<p>El <strong>Motel Trinidad en La Florida</strong> es una opci\u00f3n s\u00f3lida si buscas un lugar c\u00f3modo, limpio y con buena atenci\u00f3n. Su ubicaci\u00f3n en Trinidad 0476 es de f\u00e1cil acceso, el horario 24 horas te da flexibilidad total y los servicios como jacuzzi, hidromasaje y estacionamiento lo hacen pr\u00e1ctico para una escapada sin complicaciones.</p>
<p>Si te quedaron dudas, llama al <strong><a href=\"tel:+56222811939\">+56 2 2281 1939</a></strong> y consulta directamente con el motel. Y si quieres comparar, revisa los otros moteles de La Florida que dejamos m\u00e1s arriba.</p>
<p><em>\u00bfTe fue \u00fatil esta informaci\u00f3n? Comparte esta gu\u00eda con alguien que est\u00e9 buscando un motel en La Florida.</em></p>"""

new_excerpt = "Gu\u00eda completa del Motel Trinidad en La Florida: tel\u00e9fono +56 2 2281 1939, direcci\u00f3n Trinidad 0476, horario 24h, servicios con jacuzzi e hidromasaje, precios desde $15.000 y comparativa con otros moteles de la zona."

# First get current post
print("Leyendo post actual...")
r = requests.get(f"{site}/wp-json/wp/v2/posts/{post_id}", auth=auth)
print(f"Status: {r.status_code}")
if r.status_code != 200:
    print(f"Error: {r.text}")
    exit()

current = r.json()
print(f"T\u00edtulo actual: {current['title']['rendered']}")
print(f"Slug: {current['slug']}")

# Update the post
print("\nActualizando post...")
payload = {
    "title": new_title,
    "content": new_content,
    "excerpt": new_excerpt
}
r = requests.post(f"{site}/wp-json/wp/v2/posts/{post_id}", auth=auth, json=payload)
print(f"Status: {r.status_code}")
if r.status_code == 200:
    print("\u2705 Post actualizado exitosamente")
    print(f"Nuevo link: {r.json()['link']}")
    print(f"Nuevo t\u00edtulo: {r.json()['title']['rendered']}")
else:
    print(f"Error: {r.text}")
