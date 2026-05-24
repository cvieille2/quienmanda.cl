import requests
from datetime import datetime, timezone

AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'

# Get posts ordered by modified ascending
resp = requests.get(f'{BASE}posts?per_page=10&orderby=modified&order=asc', auth=AUTH, timeout=30)
if resp.status_code == 200:
    posts = resp.json()
    for p in posts:
        modified = p['modified']
        try:
            dt = datetime.fromisoformat(modified)
            if dt.tzinfo is None:
                dt = dt.replace(tzinfo=timezone.utc)
        except:
            dt = '?'
        content = p['content']['rendered'][:100]
        has_fresh = 'actualizacion-2026' in content
        has_aff = 'afiliado-recomendacion' in content
        print(f'ID:{p["id"]} modified:{modified} fresh:{has_fresh} aff:{has_aff} title:{p["title"]["rendered"][:40]}')

# Also check 5 random older posts
print('\n--- Specific posts ---')
for pid in [2065, 2071, 2107, 63, 76]:
    resp = requests.get(f'{BASE}posts/{pid}', auth=AUTH, timeout=30)
    if resp.status_code == 200:
        p = resp.json()
        modified = p['modified']
        content = p['content']['rendered']
        has_fresh = 'actualizacion-2026' in content
        has_aff = 'afiliado-recomendacion' in content
        print(f'ID:{pid} modified:{modified} fresh:{has_fresh} aff:{has_aff} title:{p["title"]["rendered"][:40]}')
