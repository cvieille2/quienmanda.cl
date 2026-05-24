import json
import base64
import urllib.request

CREDS = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
AUTH = 'Basic ' + base64.b64encode(f'{CREDS[0]}:{CREDS[1]}'.encode()).decode()
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2'
PAGE_IDS = [6492, 14661, 14663, 14664, 14665, 14667, 14668, 14670, 14671, 14860]
FALLBACK = 'https://www.amazon.es/?tag=avesnativas-21'

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

updated = 0
for pid in PAGE_IDS:
    page = api('GET', f'/pages/{pid}?context=edit')
    raw = page.get('content', {}).get('raw', '')
    if 'amzn.to' not in raw.lower():
        continue
    new_raw = raw.replace('https://amzn.to/3TdvvIt', FALLBACK)
    new_raw = new_raw.replace('https://amzn.to/3EsYJTm', FALLBACK)
    new_raw = new_raw.replace('https://amzn.to/3EwWQFL', FALLBACK)
    # catch any other variants
    import re
    new_raw = re.sub(r'https?://(?:www\.)?amzn\.to/[^"\s>]+', FALLBACK, new_raw, flags=re.I)
    api('PUT', f'/pages/{pid}', {'content': new_raw})
    updated += 1
    print(f'Updated page {pid}')

print(f'Updated pages: {updated}')
