import json
import base64
import re
import urllib.request

CREDS = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
AUTH = 'Basic ' + base64.b64encode(f'{CREDS[0]}:{CREDS[1]}'.encode()).decode()
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2'
MARCAS_URL = 'https://avesnativaschilenas.cl/jaulas/marcas/'
MARCAS_CAT_ID = 372

def api(method, endpoint, data=None):
    last_exc = None
    for _ in range(3):
        try:
            req = urllib.request.Request(f'{BASE}{endpoint}', method=method)
            req.add_header('Authorization', AUTH)
            req.add_header('User-Agent', 'opencode/1.0')
            if data is not None:
                req.add_header('Content-Type', 'application/json')
                req.data = json.dumps(data).encode('utf-8')
            with urllib.request.urlopen(req, timeout=60) as resp:
                return json.loads(resp.read())
        except Exception as exc:
            last_exc = exc
    raise last_exc

posts = api('GET', f'/posts?categories={MARCAS_CAT_ID}&per_page=100&_fields=id,slug,title,content&context=edit')
print(f'Total marcas posts: {len(posts)}')

updated = 0
for p in posts:
    raw = p.get('content', {}).get('raw', '')
    if MARCAS_URL in raw:
        continue

    new_raw = re.sub(
        r'https://avesnativaschilenas\.cl/jaulas/(?:portatiles|para-loros|accesorios|para-pajaros-pequenos|decorativas|grandes|para-palomas|modelos|marcas)/',
        MARCAS_URL,
        raw,
        count=1,
    )
    if new_raw == raw:
        new_raw = raw.rstrip() + f'\n\n<p>👉 Si quieres ver más modelos de marca y comparar características, visita nuestra <a href="{MARCAS_URL}">guía de marcas de jaulas</a>.</p>'

    api('PUT', f'/posts/{p["id"]}', {'content': new_raw})
    updated += 1
    print(f'Updated {updated}: {p["slug"]}')

print(f'\nUpdated content on {updated} posts')
