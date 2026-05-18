import json
import re
import subprocess
import tempfile
from pathlib import Path

POST_ID = 2808
URL = f"https://infomoteles.cl/wp-json/wp/v2/posts/{POST_ID}"

creds = Path("credenciales/infomoteles-cl.txt").read_text(encoding="utf-8")
user = re.search(r"Usuario:\s*(.+)", creds).group(1).strip()
app_password = re.search(r"App Password:\s*(.+)", creds).group(1).strip()
AUTH = f"{user}:{app_password}"

content = """
<p><strong>Motel Serrano en Antofagasta</strong> es una ficha local para revisar antes de ir: direccion centrica, reserva por Instagram, atencion 24 horas y una referencia publica de precio desde $25.000 por 2 horas.</p>

<p>Si buscas un motel en <strong>Antofagasta</strong> con habitaciones Jacuzzi, opciones Gold y Silver, y acceso directo en el centro de la ciudad, Hotel Serrano aparece como una alternativa practica para parejas que quieren confirmar datos antes de reservar.</p>
<p>La ficha publica consultada no muestra telefono directo ni sitio web oficial. Por eso, la forma mas segura de validar disponibilidad, precio vigente y tipo de habitacion es escribir al perfil de Instagram del negocio antes de salir.</p>

<h2 id="mapa">Datos de ubicacion y contacto</h2>
<p>Estos son los datos visibles y utiles para ubicar Hotel Serrano en Antofagasta. Si vas a reservar, confirma siempre por el canal oficial disponible.</p>
<table>
  <tbody>
    <tr><th>Nombre comercial</th><td>Hotel Serrano</td></tr>
    <tr><th>Ciudad</th><td>Antofagasta</td></tr>
    <tr><th>Comuna</th><td>Antofagasta</td></tr>
    <tr><th>Direccion</th><td>Teniente Ignacio Serrano 783, Antofagasta, Chile</td></tr>
    <tr><th>Telefono</th><td>Pendiente: no publicado en la ficha publica revisada</td></tr>
    <tr><th>WhatsApp</th><td>Pendiente: no publicado en la ficha publica revisada</td></tr>
    <tr><th>Reservas</th><td><a href="https://www.instagram.com/hotelserranocl/" target="_blank" rel="noopener">Instagram Hotel Serrano</a></td></tr>
    <tr><th>Horario</th><td>24 horas, segun referencia publica disponible</td></tr>
    <tr><th>Fecha de verificacion</th><td>17 de mayo de 2026</td></tr>
  </tbody>
</table>
<p><a href="https://www.instagram.com/hotelserranocl/" target="_blank" rel="noopener"><strong>Reservar por Instagram</strong></a></p>
<iframe loading="lazy" width="100%" height="320" style="border:0;border-radius:10px;" src="https://www.google.com/maps?q=Teniente%20Ignacio%20Serrano%20783%2C%20Antofagasta%2C%20Chile&amp;output=embed" allowfullscreen=""></iframe>

<h2 id="tarifas">Precios y tarifas</h2>
<p>La referencia publica encontrada indica precio desde $25.000 por 2 horas. Como las tarifas pueden cambiar por habitacion, horario o promocion, confirma el valor vigente antes de reservar.</p>
<table>
  <thead>
    <tr><th>Habitacion</th><th>Tiempo</th><th>Precio</th><th>Incluye</th></tr>
  </thead>
  <tbody>
    <tr><td>Referencia publica</td><td>2 horas</td><td>Desde $25.000</td><td>Opciones Jacuzzi, Gold y Silver segun publicaciones disponibles</td></tr>
  </tbody>
</table>

<h2>Servicios y amenidades</h2>
<table>
  <thead>
    <tr><th>Servicio</th><th>Estado</th><th>Comentario util</th></tr>
  </thead>
  <tbody>
    <tr><td>Habitaciones Jacuzzi</td><td>Publicado</td><td>Conviene confirmar disponibilidad antes de ir, porque suele depender del horario.</td></tr>
    <tr><td>Habitaciones Gold</td><td>Publicado</td><td>Alternativa para elegir mayor nivel de comodidad.</td></tr>
    <tr><td>Habitaciones Silver</td><td>Publicado</td><td>Opcion para una visita mas directa y economica.</td></tr>
    <tr><td>Atencion 24 horas</td><td>Publicado</td><td>Dato util si necesitas llegar tarde o coordinar fuera de horario comercial.</td></tr>
    <tr><td>Estacionamiento</td><td>Pendiente de confirmar</td><td>No aparece como dato verificado en la ficha publica revisada.</td></tr>
  </tbody>
</table>

<h2 id="opiniones">Opiniones reales y mi opinion</h2>
<p>La ficha mantiene votos de usuarios dentro de InfoMoteles, pero no se debe usar esa cifra como rating externo del negocio. Para tomar una decision mas segura, revisa comentarios recientes en redes o directorios antes de reservar.</p>
<p><strong>Mi opinion editorial:</strong> Hotel Serrano tiene una ventaja clara: direccion centrica en Antofagasta, referencia de precio visible y canal de reserva por Instagram. La principal debilidad es que no hay telefono ni WhatsApp publicados en la ficha, por lo que no conviene llegar sin confirmar disponibilidad.</p>
<table>
  <thead>
    <tr><th>Punto a revisar</th><th>Lectura rapida</th></tr>
  </thead>
  <tbody>
    <tr><td>Ubicacion centrica</td><td>Favorable si buscas acceso rapido dentro de Antofagasta.</td></tr>
    <tr><td>Precio de referencia</td><td>Existe referencia desde $25.000 por 2 horas, pero debe confirmarse.</td></tr>
    <tr><td>Reserva directa</td><td>El canal publico mas claro es Instagram.</td></tr>
    <tr><td>Telefono/WhatsApp</td><td>Pendiente; no inventar ni publicar numeros no verificados.</td></tr>
  </tbody>
</table>

<h2>Preguntas frecuentes</h2>
<h3>Cuanto cuesta Motel Serrano en Antofagasta?</h3>
<p>La referencia publica disponible indica desde $25.000 por 2 horas. Antes de ir, confirma el precio vigente por Instagram porque puede variar por habitacion u horario.</p>

<h3>Hotel Serrano tiene habitaciones con jacuzzi?</h3>
<p>Si. En referencias publicas aparecen habitaciones Jacuzzi, ademas de opciones Gold y Silver. Si el jacuzzi es importante para tu visita, valida disponibilidad antes de reservar.</p>

<h3>Donde queda Hotel Serrano en Antofagasta?</h3>
<p>La direccion indicada es Teniente Ignacio Serrano 783, Antofagasta, Chile. Usa el mapa de esta ficha y confirma la referencia de llegada al reservar.</p>

<h3>Como se reserva en Hotel Serrano?</h3>
<p>El canal publico mas claro es Instagram. Escribe al perfil del negocio, confirma habitacion, horario, precio y condiciones antes de salir.</p>
""".strip()

payload = {
    "title": "Motel Serrano en Antofagasta | Precios, direccion y reservas",
    "excerpt": "Motel Serrano en Antofagasta: direccion, horario 24 horas, referencia desde $25.000 por 2 horas y reserva por Instagram.",
    "content": content,
    "status": "publish",
    "meta": {
        "_yoast_wpseo_title": "Motel Serrano en Antofagasta | Precios y reservas",
        "_yoast_wpseo_metadesc": "Motel Serrano en Antofagasta: direccion, habitaciones Jacuzzi, opciones Gold y Silver, horario 24 horas, precio referencial y reserva por Instagram.",
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
    print(json.dumps({"id": data.get("id"), "link": data.get("link"), "title": data.get("title", {}).get("rendered")}, ensure_ascii=False))
finally:
    payload_path.unlink(missing_ok=True)
