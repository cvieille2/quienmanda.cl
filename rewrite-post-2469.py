import requests
import time

auth = ("cvieille", "QCZg BNQs mcnf HMXE OvBm MXpz")
site = "https://infomoteles.cl"
post_id = 2469

new_content = """<p>Motel La Casa Blanca Herrera esta ubicado en Herrera 733, comuna de Quinta Normal, Santiago. Es un motel con estacionamiento privado, habitaciones espaciosas con decoracion sencilla y sabanas de seda. Cada habitacion cuenta con termostato regulable y entrada independiente con plantas que aportan frescura.</p>

<p>El motel ofrece privacidad total desde su fachada, con instalaciones tipo departamento. Es una opcion para parejas que buscan tranquilidad en un sector centrico de Santiago. Segun opiniones en directorios, tiene una valoracion promedio de 3.8 sobre 5 basada en 141 comentarios, donde se destaca la limpieza y la atencion del personal.</p>

<h2>Servicios</h2>
<ul>
<li>Estacionamiento privado por habitacion</li>
<li>Habitaciones con sabanas de seda</li>
<li>Termostato regulable por habitacion</li>
<li>Entrada independiente con areas verdes</li>
<li>Atencion 24 horas</li>
</ul>

<h2>Precio</h2>
<p>Tarifas desde $14.800. Se recomienda confirmar el valor actual llamando directamente al establecimiento.</p>

<h2>Ubicacion</h2>
<p>Herrera 733, Quinta Normal, Santiago. Sector centrico con acceso a locomocion colectiva y cercano a servicios comerciales.</p>"""

new_excerpt = "Motel en Quinta Normal con estacionamiento privado, habitaciones tipo departamento con sabanas de seda y atencion 24 horas. Tarifas desde $14.800."

# First get current post to check
print("Leyendo post actual...")
r = requests.get(f"{site}/wp-json/wp/v2/posts/{post_id}", auth=auth)
print(f"Status: {r.status_code}")
if r.status_code != 200:
    print(f"Error: {r.text}")
    exit()

current = r.json()
print(f"Titulo actual: {current['title']['rendered']}")

# Update the post
print("\nActualizando post...")
payload = {
    "content": new_content,
    "excerpt": new_excerpt
}
r = requests.post(f"{site}/wp-json/wp/v2/posts/{post_id}", auth=auth, json=payload)
print(f"Status: {r.status_code}")
if r.status_code == 200:
    print("OK: post actualizado")
    print(f"Nuevo link: {r.json()['link']}")
else:
    print(f"Error: {r.text}")
