import requests
AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'
cutoff = '2025-05-21T00:00:00'
page = 1
old_count = 0
while True:
    resp = requests.get(f'{BASE}posts?per_page=100&page={page}&modified_before={cutoff}', auth=AUTH, timeout=30)
    if resp.status_code != 200:
        break
    posts = resp.json()
    if not posts:
        break
    old_count += len(posts)
    page += 1
print(f'Posts modified before {cutoff}: {old_count}')
