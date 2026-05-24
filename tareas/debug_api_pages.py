import requests
AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'

# Test different date formats
tests = [
    '2025-05-21T00:00:00',
    '2025-05-21',
    '2025-01-01T00:00:00',
    None,  # no filter
]

for cutoff in tests:
    url = f'{BASE}posts?per_page=5'
    if cutoff:
        url += f'&modified_before={cutoff}'
    resp = requests.get(url, auth=AUTH, timeout=30)
    if resp.status_code == 200:
        total = resp.headers.get('X-WP-Total', '?')
        total_pages = resp.headers.get('X-WP-TotalPages', '?')
        print(f'modified_before={cutoff}: {total} total posts, {total_pages} pages')
    else:
        print(f'modified_before={cutoff}: error {resp.status_code}')

# Also check the API response for the first page of old posts
print()
resp = requests.get(f'{BASE}posts?per_page=100&modified_before=2025-05-21T00:00:00', auth=AUTH, timeout=30)
if resp.status_code == 200:
    total = resp.headers.get('X-WP-Total', '?')
    print(f'Full query: {total} total posts')
    posts = resp.json()
    print(f'Posts in page 1: {len(posts)}')
    if posts:
        for p in posts[:3]:
            print(f'  ID:{p["id"]} modified:{p["modified"]} title:{p["title"]["rendered"][:40]}')
    # Check page 2
    resp2 = requests.get(f'{BASE}posts?per_page=100&page=2&modified_before=2025-05-21T00:00:00', auth=AUTH, timeout=30)
    if resp2.status_code == 200:
        posts2 = resp2.json()
        print(f'Page 2 posts: {len(posts2)}')
        if posts2:
            print(f'  First on page 2: ID:{posts2[0]["id"]} modified:{posts2[0]["modified"]}')
