import json
import base64
import re
import urllib.request

CREDS = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
AUTH = 'Basic ' + base64.b64encode(f'{CREDS[0]}:{CREDS[1]}'.encode()).decode()
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2'
CAT_ID = 163
AFFILIATE_FALLBACK = 'https://www.amazon.es/?tag=avesnativas-21'

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

def get_posts():
    posts = []
    page = 1
    while True:
        batch = api('GET', f'/posts?categories={CAT_ID}&per_page=100&page={page}&_fields=id,slug,content&context=edit')
        if not batch:
            break
        posts.extend(batch)
        page += 1
        if len(batch) < 100:
            break
    return posts

posts = get_posts()
total_short = 0
updated = 0

amazon_full_re = re.compile(r'https?://(?:www\.)?amazon\.es/[^"\s>]+', re.I)
short_re = re.compile(r'https?://(?:www\.)?amzn\.to/[^"\s>]+', re.I)

for p in posts:
    raw = p.get('content', {}).get('raw', '')
    short_matches = list(short_re.finditer(raw))
    if not short_matches:
        continue

    total_short += len(short_matches)
    full_matches = list(amazon_full_re.finditer(raw))
    replacement = full_matches[0].group(0) if full_matches else AFFILIATE_FALLBACK

    new_raw = short_re.sub(replacement, raw)
    if new_raw != raw:
        api('PUT', f'/posts/{p["id"]}', {'content': new_raw})
        updated += 1
        print(f'Updated {p["slug"]}')

print(f'Short links found: {total_short}')
print(f'Posts updated: {updated}')
