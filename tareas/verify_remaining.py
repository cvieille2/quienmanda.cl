import requests
from datetime import datetime, timezone

AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'
CUTOFF = datetime(2025, 5, 21, tzinfo=timezone.utc)

all_posts = []
page = 1
while True:
    resp = requests.get(f'{BASE}posts?per_page=100&page={page}&orderby=modified&order=asc', auth=AUTH, timeout=30)
    if resp.status_code != 200: break
    posts = resp.json()
    if not posts: break
    all_posts.extend(posts)
    page += 1

print(f'Total posts: {len(all_posts)}')

old_posts = []
for p in all_posts:
    modified = p.get('modified', '')
    try:
        dt = datetime.fromisoformat(modified)
        if dt.tzinfo is None:
            dt = dt.replace(tzinfo=timezone.utc)
        if dt < CUTOFF:
            old_posts.append(p)
    except:
        pass

print(f'Posts older than 1 year (before 2025-05-21): {len(old_posts)}')

already_fresh = 0
for p in old_posts:
    content = p.get('content', {}).get('rendered', '')
    if 'actualizacion-2026' in content:
        already_fresh += 1

print(f'Already have 2026 freshness note: {already_fresh}')
print(f'Still need update: {len(old_posts) - already_fresh}')
