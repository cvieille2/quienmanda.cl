import json, re, subprocess, tempfile
from pathlib import Path

POST_ID = 3055
URL = f"https://infomoteles.cl/wp-json/wp/v2/posts/{POST_ID}"

creds = Path("credenciales/infomoteles-cl.txt").read_text(encoding="utf-8")
user = re.search(r"Usuario:\s*(.+)", creds).group(1).strip()
app_password = re.search(r"App Password:\s*(.+)", creds).group(1).strip()
AUTH = f"{user}:{app_password}"

content = """<p><strong>Motel El Oasis de los Niches en Curic\u00f3</strong>: tel\u00e9fono +569 9795 2502, direcci\u00f3n Camino a los Niches km 8.3, precios desde $15 USD por persona, horario 24 hrs. Habitaciones con jacuzzi, sauna y ambiente privado. Informaci\u00f3n verificada en mayo 2026.</p>

<p>Si buscas un motel en <strong>Curic\u00f3</strong> con entorno natural, habitaciones limpias y servicios completos, el Motel El Oasis de los Niches destaca por su ubicaci\u00f3n privilegiada. Est\u00e1 en el <strong>Camino a los Niches, kil\u00f3metro 8.3</strong>, combinando la tranquilidad del campo con la cercan\u00eda al centro urbano. Es frecuentado por parejas de <strong>Curic\u00f3, Rauco, Molina y toda la provincia</strong>.</p>

<h2 id="tarifas">Precios y tarifas</h2>
<p>Los precios var\u00edan seg\u00fan habitaci\u00f3n y horario. Los d\u00edas de semana (lunes a jueves) suelen ser m\u00e1s econ\u00f3micos.</p>
<figure class="wp-block-table">
<table>
<thead>
<tr><th>Habitaci\u00f3n</th><th>Tiempo</th><th>Precio</th><th>Incluye</th></tr>
</thead>
<tbody>
<tr><td>Habitaci\u00f3n doble est\u00e1ndar</td><td>12 horas</td><td>Desde $15 USD por persona</td><td>Habitaci\u00f3n privada, estacionamiento, WiFi</td></tr>
<tr><td>Habitaci\u00f3n con jacuzzi</td><td>12 horas</td><td>$25.000 \u2013 $40.000 CLP</td><td>Jacuzzi privado, TV cable, climatizaci\u00f3n</td></tr>
<tr><td>Habitaci\u00f3n con sauna</td><td>12 horas</td><td>Consultar</td><td>Sauna privado, jacuzzi, desayuno continental</td></tr>
</tbody>
</table>
</figure>
<p><em>Contacta directamente para tarifas actualizadas y promociones vigentes.</em></p>

<h2>Servicios y amenidades</h2>
<p>El motel est\u00e1 rodeado de hermosos huertos de c\u00edtricos y ofrece estos servicios:</p>
<ul>
<li>Jacuzzi privado en habitaciones seleccionadas</li>
<li>Sauna privado</li>
<li>Desayuno continental incluido</li>
<li>Wi-Fi gratuito de alta velocidad</li>
<li>TV por cable / streaming</li>
<li>Aire acondicionado y climatizaci\u00f3n</li>
<li>Estacionamiento privado junto a la habitaci\u00f3n</li>
<li>Ambiente privado y discreto</li>
<li>Traslado gratuito desde aeropuerto (hasta 10 personas)</li>
<li>Servicio de bar con bebidas y snacks</li>
<li>Piscina y jard\u00edn</li>
<li>Sala de juegos y gimnasio</li>
</ul>

<h2 id="mapa">Datos de ubicaci\u00f3n y contacto</h2>
<figure class="wp-block-table">
<table>
<tbody>
<tr><th>Nombre exacto</th><td>Motel Oasis de los Niches</td></tr>
<tr><th>Direcci\u00f3n</th><td>Camino a los Niches Kil\u00f3metro 8.3, Curic\u00f3, Regi\u00f3n del Maule, Chile</td></tr>
<tr><th>Tel\u00e9fono</th><td><a href="tel:+56997952502">+569 9795 2502</a></td></tr>
<tr><th>WhatsApp</th><td><a href="https://wa.me/56997952502">+569 9795 2502</a></td></tr>
<tr><th>Tel\u00e9fono alternativo</th><td><a href="tel:+56955853733">+569 5585 3733</a></td></tr>
<tr><th>Horario</th><td>Abierto 24 horas, todos los d\u00edas del a\u00f1o</td></tr>
<tr><th>Fecha de verificaci\u00f3n</th><td>Mayo 2026</td></tr>
</tbody>
</table>
</figure>
<p><a href="https://wa.me/56997952502?text=Te%20estoy%20contactando%20desde%20infomoteles.cl%20y%20quisiera%20mas%20informacion" class="button" target="_blank" rel="noopener"><strong>Contactar por WhatsApp</strong></a></p>

<iframe loading="lazy" width="100%" height="320" style="border:0;border-radius:10px;" src="https://www.google.com/maps?q=Camino+a+los+Niches+Kil%C3%B3metro+8.3+Curic%C3%B3+Chile&output=embed" allowfullscreen=""></iframe>

<h2>Opiniones reales y mi opini\u00f3n</h2>

<h3>Lo que dicen los hu\u00e9spedes</h3>
<p><strong>Puntos positivos:</strong> Limpieza impecable de las habitaciones, amabilidad del personal, rapidez en reservas de \u00faltimo minuto, privacidad y comodidad de las instalaciones.</p>
<p><strong>Puntos a mejorar:</strong> Aislamiento ac\u00fastico regular (posible ruido exterior), inconsistencias ocasionales en jacuzzi y aire acondicionado, falta de controles remotos y salidas de ba\u00f1o en algunas habitaciones.</p>

<h3>Mi opini\u00f3n editorial</h3>
<p><strong>3.7 / 5</strong></p>
<p>El Motel El Oasis de los Niches cumple bien como opci\u00f3n accesible y funcional en Curic\u00f3, especialmente si valoras la limpieza, la privacidad y un entorno natural. No es un motel de lujo, pero entrega lo esencial con un trato humano destacable. Si buscas una escapada pr\u00e1ctica sin pretensiones, es una opci\u00f3n s\u00f3lida. Con mejoras en el aislamiento ac\u00fastico y mantenimiento de detalles menores, podr\u00eda subir f\u00e1cilmente un punto.</p>

<figure class="wp-block-table">
<table>
<thead>
<tr><th>Aspecto</th><th>Estado</th></tr>
</thead>
<tbody>
<tr><td>Privacidad</td><td>✅ Excelente</td></tr>
<tr><td>Limpieza</td><td>✅ Impecable</td></tr>
<tr><td>Jacuzzi</td><td>⚠️ Verificar disponibilidad</td></tr>
<tr><td>Aislamiento ac\u00fastico</td><td>❌ Regular</td></tr>
<tr><td>Estacionamiento</td><td>✅ Privado e incluido</td></tr>
<tr><td>WiFi</td><td>✅ Gratuito</td></tr>
<tr><td>Desayuno incluido</td><td>✅ Continental</td></tr>
<tr><td>Atenci\u00f3n 24 hrs</td><td>✅ S\u00ed</td></tr>
</tbody>
</table>
</figure>

<h2>Preguntas frecuentes</h2>

<h3>\u00bfCu\u00e1nto cuesta una habitaci\u00f3n en el Motel El Oasis de los Niches?</h3>
<p>Los precios parten desde $15 USD por persona en habitaci\u00f3n doble est\u00e1ndar. Las habitaciones con jacuzzi van de $25.000 a $40.000 CLP aproximadamente, seg\u00fan el d\u00eda de la semana.</p>

<h3>\u00bfTiene jacuzzi el Motel Oasis de los Niches?</h3>
<p>S\u00ed, cuenta con habitaciones seleccionadas que incluyen jacuzzi privado y sistemas de hidroterapia. Se recomienda consultar disponibilidad al reservar.</p>

<h3>\u00bfEl motel est\u00e1 abierto 24 horas?</h3>
<p>S\u00ed, abre todos los d\u00edas del a\u00f1o, las 24 horas del d\u00eda.</p>

<h3>\u00bfC\u00f3mo llegar al Motel El Oasis de los Niches?</h3>
<p>Est\u00e1 ubicado en el Camino a los Niches, kil\u00f3metro 8.3, a pocos minutos en auto del centro de Curic\u00f3. Ideal si vienes desde Curic\u00f3, Rauco, Molina o la Ruta 5 Sur.</p>

<h3>\u00bfAceptan reservas por WhatsApp?</h3>
<p>S\u00ed, puedes contactar al <a href="https://wa.me/56997952502">+569 9795 2502</a> para hacer tu reserva o consultar tarifas actualizadas.</p>

<h3>\u00bfTiene estacionamiento privado?</h3>
<p>S\u00ed, todas las habitaciones cuentan con estacionamiento privado junto a la entrada.</p>

<p><a href="https://infomoteles.cl/curico/">Ver m\u00e1s moteles en Curic\u00f3</a></p>"""

excerpt = "Motel El Oasis de los Niches en Curic\u00f3: tel\u00e9fono +569 9795 2502, direcci\u00f3n Camino a los Niches km 8.3, precios desde $15 USD, horario 24 hrs. Habitaciones con jacuzzi, sauna y ambiente privado."

payload = {
    "title": "Motel El Oasis de los Niches en Curic\u00f3 | Tel\u00e9fono, Precios y Habitaciones con Jacuzzi",
    "excerpt": excerpt,
    "content": content,
    "status": "publish",
    "meta": {
        "_yoast_wpseo_title": "Motel El Oasis de los Niches en Curic\u00f3 | Tel\u00e9fono, Precios y Jacuzzi",
        "_yoast_wpseo_metadesc": "Motel El Oasis de los Niches en Curic\u00f3: tel\u00e9fono +569 9795 2502, direcci\u00f3n, precios desde $15 USD, horario 24 hrs. Habitaciones con jacuzzi, sauna y ambiente privado.",
    },
}

with tempfile.NamedTemporaryFile("w", encoding="utf-8", suffix=".json", delete=False) as fh:
    json.dump(payload, fh, ensure_ascii=False)
    payload_path = Path(fh.name)

try:
    result = subprocess.run(
        [
            "curl",
            "-s",
            "-X",
            "POST",
            URL,
            "-u",
            AUTH,
            "-H",
            "Content-Type: application/json",
            "--data-binary",
            f"@{payload_path}",
        ],
        capture_output=True,
        text=True,
        timeout=60,
        check=True,
    )
    data = json.loads(result.stdout)
    if "id" in data:
        print(json.dumps({"id": data.get("id"), "link": data.get("link"), "title": data.get("title", {}).get("rendered")}, ensure_ascii=False))
    else:
        print(f"ERROR: {data.get('message', str(data)[:300])}")
finally:
    payload_path.unlink(missing_ok=True)
