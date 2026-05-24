import json, urllib.request, urllib.error, base64, re

CREDS = ("cvieille", "u0wM 1VRi v9wL Z71R 7XCx mnEq")
AUTH_HEADER = "Basic " + base64.b64encode(f"{CREDS[0]}:{CREDS[1]}".encode()).decode()
API_BASE = "https://avesnativaschilenas.cl/wp-json/wp/v2"
PILLAR_URL = "https://avesnativaschilenas.cl/gaviotas/"
PILLAR_SLUG = "/gaviotas/"
PILLAR_TITLE = "Gaviotas en Chile: Especies, Tipos y Guia de Identificacion"

PILLAR_LINK_HTML = """<p>👉 Si quieres conocer todas las especies, visita nuestra guía completa: <a href="https://avesnativaschilenas.cl/gaviotas/">Gaviotas en Chile: Especies, Tipos y Guía de Identificación</a>.</p>"""

PILLAR_LINK_TEMPLATES = {
    "gaviota-dominicana": """<p>¿Quieres conocer todas las gaviotas de Chile? No te pierdas nuestra <a href="https://avesnativaschilenas.cl/gaviotas/">guía completa de especies de gaviotas en Chile</a>.</p>""",
    "gaviota-de-franklin": """<p>Descubre también las otras <a href="https://avesnativaschilenas.cl/gaviotas/">especies de gaviotas que habitan en Chile</a> en nuestra guía completa.</p>""",
    "gaviota-garuma": """<p>Conoce todas las <a href="https://avesnativaschilenas.cl/gaviotas/">especies de gaviotas de Chile</a> en nuestra guía actualizada.</p>""",
    "gaviota-peruana": """<p>Explora la <a href="https://avesnativaschilenas.cl/gaviotas/">lista completa de gaviotas en Chile</a> para identificar cada especie.</p>""",
    "gaviota-pacifico": """<p>Revisa nuestra <a href="https://avesnativaschilenas.cl/gaviotas/">guía de identificación de gaviotas chilenas</a> para no confundir especies.</p>""",
    "gaviota-cahuil": """<p>Amplía tu conocimiento con la <a href="https://avesnativaschilenas.cl/gaviotas/">guía completa de gaviotas de Chile</a>.</p>""",
    "gaviota-andina": """<p>Descubre las otras <a href="https://avesnativaschilenas.cl/gaviotas/">especies de gaviotas que habitan en Chile</a> y sus características.</p>""",
    "gaviotin-monja": """<p>Conoce todas las <a href="https://avesnativaschilenas.cl/gaviotas/">gaviotas y gaviotines de Chile</a> en nuestra guía de identificación.</p>""",
    "gaviotin-sudamericano": """<p>No te pierdas la <a href="https://avesnativaschilenas.cl/gaviotas/">guía completa de gaviotas chilenas</a> para identificar cada especie.</p>""",
    "gaviotin-elegante": """<p>Descubre las otras <a href="https://avesnativaschilenas.cl/gaviotas/">especies de gaviotas que habitan en Chile</a> en nuestra guía completa.</p>""",
}

def api_get(endpoint):
    req = urllib.request.Request(f"{API_BASE}{endpoint}")
    req.add_header("Authorization", AUTH_HEADER)
    req.add_header("User-Agent", "opencode/1.0")
    try:
        with urllib.request.urlopen(req) as resp:
            return json.loads(resp.read())
    except Exception as e:
        return None

def api_put(endpoint, data):
    req = urllib.request.Request(
        f"{API_BASE}{endpoint}",
        data=json.dumps(data).encode('utf-8'),
        method="PUT"
    )
    req.add_header("Authorization", AUTH_HEADER)
    req.add_header("Content-Type", "application/json")
    req.add_header("User-Agent", "opencode/1.0")
    try:
        with urllib.request.urlopen(req) as resp:
            return json.loads(resp.read())
    except urllib.error.HTTPError as e:
        print("  ERROR HTTP %d: %s" % (e.code, e.read().decode()[:200]))
        return None
    except Exception as e:
        print("  ERROR: %s" % e)
        return None

# Step 1: Get all posts in Gaviotas category
posts = api_get("/posts?categories=24&per_page=30&_fields=id,title,slug,link")
if not posts:
    print("No posts found in Gaviotas category")
    exit()

print("Posts in Gaviotas category: %d" % len(posts))
print()

updated = 0
skipped = 0
errors = 0

for post in posts:
    pid = post.get('id')
    slug = post.get('slug')
    title = post.get('title', {}).get('rendered', slug)
    
    print("Processing: [%s] %s" % (slug, title))
    
    # Get post content with context=edit
    post_data = api_get("/posts/%d?context=edit" % pid)
    if not post_data:
        print("  ERROR: Could not fetch post data")
        errors += 1
        continue
    
    raw_content = post_data.get('content', {}).get('raw', '')
    
    # Check if already links to pillar
    if PILLAR_SLUG in raw_content or PILLAR_URL in raw_content:
        print("  SKIP - already has pillar link")
        skipped += 1
        continue
    
    # Get template for this slug, or use default
    link_html = PILLAR_LINK_TEMPLATES.get(slug, PILLAR_LINK_HTML)
    
    # Add link before the last </p> or at the end
    new_content = raw_content.rstrip() + "\n\n" + link_html
    
    # Update the post
    result = api_put("/posts/%d" % pid, {
        "content": new_content
    })
    
    if result:
        print("  UPDATED - added pillar link")
        updated += 1
    else:
        print("  ERROR updating")
        errors += 1

print()
print("=" * 40)
print("Resumen:")
print("  Actualizados: %d" % updated)
print("  Saltados (ya tienen link): %d" % skipped)
print("  Errores: %d" % errors)
print("  Total: %d" % len(posts))
