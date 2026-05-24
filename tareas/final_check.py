import requests
from datetime import datetime, timezone, timedelta

AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'
CUTOFF = datetime.now(timezone.utc) - timedelta(days=365)

print(f'Today: {datetime.now(timezone.utc).date()}')
print(f'Cutoff (1 year ago): {CUTOFF.date()}')
print()

all_posts = []
page = 1
while True:
    resp = requests.get(f'{BASE}posts?per_page=100&page={page}', auth=AUTH, timeout=30)
    if resp.status_code != 200: break
    posts = resp.json()
    if not posts: break
    all_posts.extend(posts)
    page += 1

print(f'Total posts: {len(all_posts)}')

old = []
fresh = []
for p in all_posts:
    modified = p.get('modified_gmt') or p.get('modified', '')
    try:
        if modified.endswith('Z'):
            dt = datetime.fromisoformat(modified.replace('Z', '+00:00'))
        else:
            dt = datetime.fromisoformat(modified + '+00:00')
        if dt.tzinfo is None:
            dt = dt.replace(tzinfo=timezone.utc)
    except:
        dt = None

    content = p['content']['rendered']
    has_note = 'actualizacion-2026' in content
    has_aff = 'afiliado-recomendacion' in content or 'amzn.to' in content

    if dt and dt < CUTOFF:
        old.append((p['id'], modified, has_note, has_aff, p['title']['rendered'][:40]))
    else:
        fresh.append((p['id'], modified, has_note, has_aff))

print(f'Posts >1 year old: {len(old)}')
if old:
    print('  Oldest:')
    for pid, mod, note, aff, title in old[:5]:
        print(f'    ID:{pid} mod:{mod} note:{note} aff:{aff} "{title}"')

print()
updated_count = sum(1 for _, _, n, a in fresh if n and a)
print(f'Posts <1 year: {len(fresh)}')
print(f'Of which with freshness note + affiliate: {updated_count}')
