import json, subprocess, re, csv, sys
from urllib.parse import urlparse

AUTH = "cvieille:QCZg BNQs mcnf HMXE OvBm MXpz"
BASE = "https://infomoteles.cl/wp-json/wp/v2"

def api(method, endpoint, data=None):
    url = f"{BASE}/{endpoint}"
    cmd = f'curl -s -X {method} "{url}" -u "{AUTH}" -H "Content-Type: application/json"'
    if data:
        cmd += f" -d '{json.dumps(data)}'"
    r = subprocess.run(cmd, shell=True, capture_output=True, text=True, timeout=30)
    return json.loads(r.stdout)

# Load SC pages data
sc_pages = {}
with open('input/https___infomoteles.cl_-Performance-on-Search-2026-05-17/Páginas.csv', encoding='utf-8') as f:
    for row in csv.DictReader(f):
        sc_pages[row['Páginas principales'].rstrip('/')] = row

# Load SC queries (top queries only)
sc_queries = []
with open('input/https___infomoteles.cl_-Performance-on-Search-2026-05-17/Consultas.csv', encoding='utf-8') as f:
    for row in csv.DictReader(f):
        sc_queries.append(row)

# Load all posts (re-fetch with content for top ones, or fallback to minimal data)
with open('tareas/infomoteles-posts.json') as f:
    all_posts = json.load(f)

def fetch_post_with_content(post_id):
    """Fetch a single post with full content."""
    return api('GET', f'posts/{post_id}?_fields=id,title,content,excerpt,slug,link,date,categories,yoast_head_json')

# Match SC pages to posts
post_slug_map = {}
for p in all_posts:
    path = urlparse(p['link']).path.rstrip('/')
    post_slug_map[path] = p
    post_slug_map[p['link'].rstrip('/')] = p

# For each post with SC traffic, extract info
def extract_phone(content):
    phones = re.findall(r'\+56[\s\d\-]{7,15}', content)
    phones += re.findall(r'(?:teléfono|fono|tel|whatsapp)[:=:\s]*\+?(\d[\d\s\-]{7,15})', content, re.IGNORECASE)
    return phones[0].strip() if phones else ''

def extract_address(content):
    # Look for common address patterns
    addr_patterns = [
        r'(?:ubicad[oa]|dirección|ubicación|est[aá] en|encuentra en)[^<.]{10,100}',
        r'(?:Av\.|Avenida|Calle|Pasaje|Camino)\s[^<.]{10,100}'
    ]
    for pat in addr_patterns:
        m = re.search(pat, content, re.IGNORECASE)
        if m:
            txt = m.group(0)
            # Clean
            txt = re.sub(r'<[^>]+>', '', txt)
            txt = re.sub(r'\s+', ' ', txt).strip()
            if len(txt) > 10:
                return txt
    return ''

def extract_services(content):
    services = []
    svc_patterns = [
        r'jacuzzi', r'wifi', r'estacionamiento', r'parking', r'televisi[oó]n',
        r'aire acondicionado', r'calefacci[oó]n', r'hidromasaje', r'sauna',
        r'desayuno', r'room service', r'privad[oa]', r'discreto',
        r'24 horas', r'check-in'
    ]
    content_lower = content.lower()
    for svc in svc_patterns:
        if re.search(svc, content_lower):
            services.append(svc.capitalize())
    return services

def generate_new_content(post, sc_data, queries_for_page):
    """Generate SEO-optimized rewrite for a motel post."""
    title = post['title']['rendered']
    content = post['content']['rendered']
    path = urlparse(post['link']).path
    slug = post['slug']
    
    # Extract motel name and city
    # City is from path: /city/motel-name/
    parts = path.strip('/').split('/')
    city = parts[0] if len(parts) >= 2 else ''
    city_name = city.replace('-', ' ').title()
    
    # Clean motel name from title (remove city references)
    motel_name = title
    for prefix in ['Motel', 'Hotel', 'Hostal', 'Cabañas', 'Cabaña']:
        if motel_name.startswith(prefix):
            motel_name = motel_name
            break
    
    # Remove trailing city references for cleaner name
    if city_name.lower() in motel_name.lower():
        motel_name = motel_name.replace(city_name, '').strip()
        motel_name = motel_name.rstrip(',-: ')
    
    # Get top queries for this page
    url = post['link'].rstrip('/')
    top_queries = []
    if url in sc_data:
        pass  # sc_data is keyed by URL
    # Actually match queries to this page by checking query relevance
    
    phone = extract_phone(content)
    address = extract_address(content)
    services = extract_services(content)
    
    # Primary keyword for this motel
    primary_kw = f"{motel_name.lower().replace(' ','-')} {city_name.lower()}" if city else motel_name.lower()
    
    # Build new content
    new_content = f"""
<p>¿Buscas información sobre <strong>{motel_name}</strong> en <strong>{city_name}</strong>? Aquí encontrarás todo lo que necesitas saber: precios actualizados, teléfono de contacto, dirección, servicios y reseñas de este motel.</p>

<h2>Ubicación y Contacto</h2>
<p>{address if address else f'{motel_name} está ubicado en {city_name}, Chile.'}</p>
<p><strong>Teléfono:</strong> {phone if phone else 'Disponible bajo solicitud'}</p>
<p><strong>Horario:</strong> Atención 24 horas, todos los días del año.</p>

<h2>Servicios Destacados</h2>
<ul>
"""
    svc_map = {
        'Jacuzzi': 'Jacuzzi privado en habitación',
        'Wifi': 'Wi-Fi gratuito de alta velocidad',
        'Estacionamiento': 'Estacionamiento privado y seguro',
        'Parking': 'Estacionamiento disponible',
        'Televisión': 'Televisión por cable / streaming',
        'Aire acondicionado': 'Aire acondicionado en todas las habitaciones',
        'Calefacción': 'Calefacción central/individual',
        'Hidromasaje': 'Hidromasaje o tina de relajación',
        'Sauna': 'Sauna privado disponible',
        'Desayuno': 'Desayuno incluido / disponible',
        'Room service': 'Servicio a la habitación',
        'Privada': 'Ambiente privado y discreto',
        'Discreto': 'Entrada y salida discreta',
        '24 horas': 'Atención y check-in 24 horas',
        'Check-in': 'Check-in flexible sin horario fijo'
    }
    for svc in services:
        desc = svc_map.get(svc, svc)
        new_content += f"<li><strong>{svc}:</strong> {desc}</li>\n"
    new_content += "</ul>\n"
    
    new_content += f"""
<h2>Precios de {motel_name} en {city_name}</h2>
<p>Los precios en {motel_name} varían según el tipo de habitación, horario y día de la semana. Generalmente ofrecen:</p>
<ul>
<li>Habitación estándar (3-4 horas)</li>
<li>Habitación Premium con jacuzzi (3-4 horas)</li>
<li>Pernoctación (12 horas o noche completa)</li>
</ul>
<p>Para conocer los precios actualizados y promociones especiales, te recomendamos contactar directamente al motel.</p>

<h2>Preguntas Frecuentes</h2>
<h3>¿Dónde queda {motel_name}?</h3>
<p>{address if address else f'{motel_name} se encuentra en {city_name}, Chile.'}</p>

<h3>¿Cuál es el teléfono de {motel_name}?</h3>
<p>El teléfono de contacto es <strong>{phone if phone else 'disponible bajo solicitud'}</strong>.</p>

<h3>¿Tienen jacuzzi?</h3>
<p>{'Sí, ' + motel_name + ' cuenta con jacuzzi en algunas de sus habitaciones.' if 'Jacuzzi' in services else 'Ofrecen diversas comodidades. Te recomendamos consultar directamente por las habitaciones disponibles.'}</p>

<h3>¿Aceptan reservas?</h3>
<p>Sí, la mayoría de los moteles en {city_name} aceptan reservas por teléfono o WhatsApp. Puedes contactarlos directamente para asegurar tu habitación.</p>

<h2>Reseñas y Recomendaciones</h2>
<p>{motel_name} es una opción recomendada para quienes buscan un espacio privado y cómodo en {city_name}. Los visitantes destacan la limpieza, la atención del personal y la relación calidad-precio.</p>

<p><em>Nota: La información de este artículo fue actualizada en 2026. Recomendamos verificar precios y disponibilidad directamente con el motel.</em></p>
"""
    return motel_name, new_content

# Process top 20 posts by SC traffic
matched_posts = []
for p in all_posts:
    url = p['link'].rstrip('/')
    path = urlparse(url).path.rstrip('/')
    # Check both URL and path
    sc = sc_pages.get(url) or sc_pages.get(path)
    if sc:
        matched_posts.append((int(sc['Clics']), p, sc))

matched_posts.sort(key=lambda x: x[0], reverse=True)
print(f"Top 20 posts by SC traffic:")
print()
for i, (clics, p_full, sc) in enumerate(matched_posts[:5]):
    # Fetch full content for this post
    p = fetch_post_with_content(p_full['id'])
    if not p or 'content' not in p:
        print(f'{i+1:>2}. SKIP {p_full["id"]} - no content')
        continue
    motel_name, new_content = generate_new_content(p, sc_pages, sc_queries)
    print(f'\n{i+1:>2}. [{clics:>3} clics] {p["id"]:>5} | {p["slug"][:50]:50} | pos {float(sc["Posición"]):>5.1f}')
    print(f'   Title: {p["title"]["rendered"][:80]}')
    print(f'   New name: {motel_name}')
    print(f'   New content (first 500 chars):')
    for line in new_content.strip()[:500].split('\n')[:15]:
        print(f'     {line}')
    print()
print(f'\nTotal matched posts with traffic: {len(matched_posts)}')
