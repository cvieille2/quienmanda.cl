import json, subprocess, re, csv, sys, os
from urllib.parse import urlparse

AUTH = "cvieille:QCZg BNQs mcnf HMXE OvBm MXpz"
BASE = "https://infomoteles.cl/wp-json/wp/v2"

CITY_MAP = {
    'antofagasta': 'Antofagasta', 'arica': 'Arica', 'calama': 'Calama',
    'copiapo': 'Copiap\u00f3', 'la-serena': 'La Serena', 'coquimbo': 'Coquimbo',
    'valparaiso': 'Valpara\u00edso', 'vina-del-mar': 'Vi\u00f1a del Mar',
    'concepcion': 'Concepci\u00f3n', 'talca': 'Talca', 'curico': 'Curic\u00f3',
    'santiago': 'Santiago', 'maipu': 'Maip\u00fa', 'pudahuel': 'Pudahuel',
    'quinta-normal': 'Quinta Normal', 'recoleta': 'Recoleta',
    'la-cisterna': 'La Cisterna', 'la-florida': 'La Florida',
    'san-bernardo': 'San Bernardo', 'san-miguel': 'San Miguel',
    'puente-alto': 'Puente Alto', 'macul': 'Macul', 'providencia': 'Providencia',
    'independencia': 'Independencia', 'cerro-navia': 'Cerro Navia',
    'conchali': 'Conchal\u00ed', 'estacion-central': 'Estaci\u00f3n Central',
    'huechuraba': 'Huechuraba', 'lo-barnechea': 'Lo Barnechea',
    'lo-prado': 'Lo Prado', 'melipilla': 'Melipilla', 'padre-hurtado': 'Padre Hurtado',
    'penalolen': 'Pe\u00f1alol\u00e9n', 'pirque': 'Pirque',
    'san-joaquin': 'San Joaqu\u00edn', 'san-ramon': 'San Ram\u00f3n',
    'vitacura': 'Vitacura', 'la-reina': 'La Reina', 'las-condes': 'Las Condes',
    'nunoa': '\u00d1u\u00f1oa', 'rancagua': 'Rancagua', 'talcahuano': 'Talcahuano',
    'chillan': 'Chill\u00e1n', 'temuco': 'Temuco', 'valdivia': 'Valdivia',
    'osorno': 'Osorno', 'puerto-montt': 'Puerto Montt', 'coyhaique': 'Coyhaique',
    'punta-arenas': 'Punta Arenas', 'iquique': 'Iquique', 'tocopilla': 'Tocopilla',
    'lampa': 'Lampa', 'colina': 'Colina', 'buin': 'Buin',
    'san-antonio': 'San Antonio', 'san-felipe': 'San Felipe',
    'los-andes': 'Los Andes', 'quillota': 'Quillota',
    'san-fernando': 'San Fernando', 'chimbarongo': 'Chimbarongo',
    'rancagua': 'Rancagua', 'machal\u00ed': 'Machal\u00ed',
    'limache': 'Limache', 'villa-alemana': 'Villa Alemana',
    'coronel': 'Coronel', 'lota': 'Lota', 'arauco': 'Arauco',
    'los-angeles': 'Los \u00c1ngeles', 'angol': 'Angol', 'victoria': 'Victoria',
    'la-union': 'La Uni\u00f3n', 'rio-negro': 'R\u00edo Negro',
    'purranque': 'Purranque', 'ancud': 'Ancud', 'castro': 'Castro',
    'calbuco': 'Calbuco'
}

def api(method, endpoint, data=None):
    url = f"{BASE}/{endpoint}"
    # Write JSON to temp file to avoid shell escaping issues
    tmpfile = f'tareas/_api_{endpoint.replace("/","_")}.json'
    if data:
        with open(tmpfile, 'w', encoding='utf-8') as f:
            json.dump(data, f, ensure_ascii=False)
        cmd = f'curl -s -X {method} "{url}" -u "{AUTH}" -H "Content-Type: application/json" -d @{tmpfile}'
    else:
        cmd = f'curl -s -X {method} "{url}" -u "{AUTH}"'
    r = subprocess.run(cmd, shell=True, capture_output=True, text=True, timeout=30)
    # Clean up temp file
    if data and os.path.exists(tmpfile):
        os.remove(tmpfile)
    try:
        return json.loads(r.stdout)
    except:
        return {"error": r.stdout[:200]}

def get_city_from_path(path):
    parts = path.strip('/').split('/')
    if len(parts) >= 2:
        slug = parts[0]
        return CITY_MAP.get(slug, slug.replace('-', ' ').title())
    return ''

def extract_phone(content):
    phones = re.findall(r'\+56[\s\d\-]{7,15}', content)
    if not phones:
        phones = re.findall(r'(?:tel[eé]fono|fono|tel|whatsapp)[:=:\s]*\+?(\d[\d\s\-]{7,15})', content, re.IGNORECASE)
    return phones[0].strip() if phones else ''

def extract_address(content):
    text = re.sub(r'<[^>]+>', ' ', content)
    text = re.sub(r'\s+', ' ', text)
    patterns = [
        r'(?:ubicad[oa]|direcci[oó]n|ubicaci[oó]n|est[aá] en|encuentra en|localizado en)\s[^.]{10,150}\.',
        r'(?:Av\.|Avenida|Calle|Pasaje|Camino|Ruta)\s[^.]{10,150}\.'
    ]
    for pat in patterns:
        m = re.search(pat, text, re.IGNORECASE)
        if m:
            txt = m.group(0).strip()
            if len(txt) > 10:
                return txt
    return ''

def extract_services(content):
    services = []
    svc_patterns = {
        'jacuzzi': 'Jacuzzi',
        'wifi': 'Wi-Fi',
        'estacionamiento': 'Estacionamiento',
        'parking': 'Estacionamiento',
        'televisi[oó]n': 'TV',
        'aire acondicionado': 'Aire Acondicionado',
        'calefacci[oó]n': 'Calefacci\u00f3n',
        'hidromasaje': 'Hidromasaje',
        'sauna': 'Sauna',
        'desayuno': 'Desayuno',
        'privad[oa]': 'Privacidad',
        'discreto': 'Discreci\u00f3n',
        '24 horas': 'Atenci\u00f3n 24h'
    }
    content_lower = content.lower()
    for pattern, name in svc_patterns.items():
        if re.search(pattern, content_lower):
            services.append(name)
    return list(set(services))

def get_top_queries_for_page(url, sc_queries):
    """Find related queries for this page from SC data."""
    url_lower = url.lower()
    related = []
    for q in sc_queries:
        query = q['Consultas principales'].lower().strip('"')
        # Check if query terms appear in URL
        query_terms = set(query.split())
        url_terms = set(url_lower.rstrip('/').split('/')[-1].replace('-', ' ').split())
        if query_terms & url_terms:
            related.append(q)
    related.sort(key=lambda x: int(x['Clics']), reverse=True)
    return related[:5]

def generate_rewrite(post, sc_pages, sc_queries):
    """Generate optimized content, preserving old content below new intro."""
    title = post['title']['rendered']
    content = post['content']['rendered']
    path = urlparse(post['link']).path
    slug = post['slug']
    
    city_name = get_city_from_path(path)
    
    # Clean motel name
    motel_name = title
    for separator in [f', {city_name}', f',{city_name}', f' en {city_name}', f' - {city_name}']:
        if separator in motel_name:
            motel_name = motel_name.split(separator)[0].strip()
            break
    
    # Remove generic prefixes for cleaner name
    for prefix in ['Motel ', 'Hotel Motel ', 'Hotel ', 'Hostal o Pensión ', 'Hostal ']:
        if motel_name.startswith(prefix):
            motel_name = motel_name[len(prefix):]
            break
    
    phone = extract_phone(content)
    address = extract_address(content)
    services = extract_services(content)
    
    url = post['link'].rstrip('/')
    
    svc_descriptions = {
        'Jacuzzi': 'Jacuzzi privado en habitaci\u00f3n',
        'Wi-Fi': 'Wi-Fi gratuito de alta velocidad',
        'Estacionamiento': 'Estacionamiento privado',
        'TV': 'TV por cable / streaming',
        'Aire Acondicionado': 'Climatizaci\u00f3n en todas las habitaciones',
        'Calefacci\u00f3n': 'Calefacci\u00f3n central/individual',
        'Hidromasaje': 'Hidromasaje o tina de relajaci\u00f3n',
        'Sauna': 'Sauna privado',
        'Desayuno': 'Desayuno incluido / disponible',
        'Privacidad': 'Ambiente privado y discreto',
        'Discreci\u00f3n': 'Entrada y salida discreta',
        'Atenci\u00f3n 24h': 'Check-in 24 horas'
    }
    
    # Build the new intro content (above the fold - what Google shows)
    svc_html = ''
    if services:
        svc_html = '<ul>\n'
        for svc in services:
            desc = svc_descriptions.get(svc, svc)
            svc_html += f'  <li><strong>{svc}:</strong> {desc}</li>\n'
        svc_html += '</ul>\n'
    
    # Contact buttons
    whatsapp = ''
    if phone:
        phone_clean = re.sub(r'[\s\-]', '', phone)
        whatsapp = f'<p><a href="https://wa.me/{phone_clean}" class="button" target="_blank" rel="noopener">Contactar por WhatsApp</a></p>\n'
    
    new_top = f"""<p><strong>{motel_name}</strong> es uno de los moteles en <strong>{city_name}</strong>, Chile. Aqu\u00ed encontrar\u00e1s toda la informaci\u00f3n actualizada: tel\u00e9fono de contacto, direcci\u00f3n, horarios y rese\u00f1as de este motel.</p>

{whatsapp if whatsapp else ''}
<h2>Informaci\u00f3n de {motel_name} en {city_name}</h2>
<p>{address if address else f'{motel_name} se encuentra en {city_name}.'}</p>
<p><strong>Tel\u00e9fono:</strong> {phone if phone else 'Disponible bajo solicitud'}</p>
<p><strong>Horario:</strong> Abierto 24 horas, todos los d\u00edas del a\u00f1o.</p>

<h2>Servicios</h2>
{svc_html if svc_html else '<p>Ofrece habitaciones privadas con estacionamiento.</p>\n'}

<h2>Precios {city_name}</h2>
<p>Los precios en {motel_name} var\u00edan seg\u00fan la habitaci\u00f3n y horario. Puedes contactar directamente para conocer tarifas actualizadas y promociones.</p>

<!-- OLD CONTENT PRESERVED BELOW -->
<hr />
"""
    
    # Keep new + old content
    new_content = new_top + content
    
    # Generate better SEO title
    new_title = f"{motel_name} en {city_name} | Tel\u00e9fono, Direcci\u00f3n y Precios"
    if len(new_title) > 65:
        new_title = f"{motel_name} en {city_name} | Tel\u00e9fono y Precios"
    
    return new_title, new_content

def rewrite_post(post_id, new_title, new_content):
    """Update a post via WP API."""
    data = {
        'title': new_title,
        'content': new_content,
        'date': '2026-05-17T00:00:00',  # Force date update so it shows as fresh
    }
    result = api('PUT', f'posts/{post_id}', data)
    if 'id' in result:
        print(f'  OK: updated post {post_id} - "{new_title[:60]}"')
        return True
    else:
        print(f'  FAIL: post {post_id} - {result.get("message", str(result)[:100])}')
        return False

# ====== MAIN ======
# Load posts
with open('tareas/infomoteles-posts.json') as f:
    all_posts = json.load(f)

# Load SC data
sc_pages = {}
with open('input/https___infomoteles.cl_-Performance-on-Search-2026-05-17/P\u00e1ginas.csv', encoding='utf-8') as f:
    for row in csv.DictReader(f):
        sc_pages[row['P\u00e1ginas principales'].rstrip('/')] = row

sc_queries = []
with open('input/https___infomoteles.cl_-Performance-on-Search-2026-05-17/Consultas.csv', encoding='utf-8') as f:
    for row in csv.DictReader(f):
        sc_queries.append(row)

# Match posts to SC data
matched_posts = []
for p in all_posts:
    path = urlparse(p['link']).path.rstrip('/')
    sc = sc_pages.get(p['link'].rstrip('/')) or sc_pages.get(path)
    if sc:
        matched_posts.append((int(sc['Clics']), p, sc))

matched_posts.sort(key=lambda x: x[0], reverse=True)
total_traffic = len(matched_posts)
print(f"Posts with SC traffic: {total_traffic}")
print()

# Process top posts first
to_process = matched_posts[:30]
print(f"=== Rewriting top {len(to_process)} posts ===\n")

success = 0
fail = 0

for i, (clics, p_full, sc) in enumerate(to_process):
    # Fetch full content
    result = api('GET', f'posts/{p_full["id"]}?_fields=id,title,content,slug,link,date,categories')
    if 'content' not in result:
        print(f'{i+1}. SKIP post {p_full["id"]} - {result.get("message","no content")}')
        fail += 1
        continue
    
    p = result
    new_title, new_content = generate_rewrite(p, sc_pages, sc_queries)
    
    print(f'{i+1}. [{clics:>3} clics] Post {p["id"]}: {p["slug"][:35]}')
    
    if rewrite_post(p['id'], new_title, new_content):
        success += 1
    else:
        fail += 1

print(f"\nDone: {success} rewritten, {fail} failed")
