import requests
from datetime import datetime, timezone

AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'

print('--- 5 oldest by date ---')
resp = requests.get(f'{BASE}posts?per_page=5&orderby=date&order=asc', auth=AUTH, timeout=30)
if resp.status_code == 200:
    for p in resp.json():
        print(f'ID:{p["id"]} date:{p["date"]} modified:{p["modified"]} title:{p["title"]["rendered"][:50]}')

print('\n--- 5 newest by modified (oldest modifications) ---')
resp = requests.get(f'{BASE}posts?per_page=5&orderby=modified&order=asc', auth=AUTH, timeout=30)
if resp.status_code == 200:
    for p in resp.json():
        print(f'ID:{p["id"]} modified:{p["modified"]} title:{p["title"]["rendered"][:50]}')

print('\n--- 5 newest by modified (most recent) ---')
resp = requests.get(f'{BASE}posts?per_page=5&orderby=modified&order=desc', auth=AUTH, timeout=30)
if resp.status_code == 200:
    for p in resp.json():
        print(f'ID:{p["id"]} modified:{p["modified"]} title:{p["title"]["rendered"][:50]}')

print('\n--- Count by year of modified ---')
for year in [2023, 2024, 2025, 2026]:
    resp = requests.get(f'{BASE}posts?per_page=1&modified_after={year}-01-01T00:00:00&modified_before={year+1}-01-01T00:00:00', auth=AUTH, timeout=30)
    if resp.status_code == 200:
        headers = resp.headers
        total = headers.get('X-WP-Total', '?')
        print(f'  Modified in {year}: {total}')
    else:
        print(f'  Modified in {year}: error {resp.status_code}')
