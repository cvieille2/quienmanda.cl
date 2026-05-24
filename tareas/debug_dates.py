import requests
from datetime import datetime, timezone

AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'

cutoff = datetime(2025, 5, 21, tzinfo=timezone.utc)

# Check a 2024-modified post
resp = requests.get(f'{BASE}posts/2065', auth=AUTH, timeout=30)
if resp.status_code == 200:
    p = resp.json()
    print('Keys:', list(p.keys()))
    print('modified:', p.get('modified'))
    print('modified_gmt:', p.get('modified_gmt'))
    print('date_gmt:', p.get('date_gmt'))
    mgmt = p.get('modified_gmt', '')
    print('modified_gmt repr:', repr(mgmt))
    try:
        dt = datetime.fromisoformat(mgmt.replace('Z', '+00:00'))
        print('Parsed dt:', dt)
        print('Is before cutoff:', dt < cutoff)
    except Exception as e:
        print('Parse error:', e)

# Try a different approach - use modified instead of modified_gmt
print('\n--- Testing with modified field ---')
try:
    dt2 = datetime.fromisoformat(p['modified'].replace('Z', '+00:00'))
    print('Parsed modified:', dt2)
    print('Is before cutoff:', dt2 < cutoff)
except Exception as e:
    print('Parse error modified:', e)

# Now test batch
print('\n--- First 5 posts ordered by modified asc ---')
resp = requests.get(f'{BASE}posts?per_page=5&orderby=modified&order=asc', auth=AUTH, timeout=30)
if resp.status_code == 200:
    for p in resp.json():
        mgmt = p.get('modified_gmt', p.get('modified', ''))
        modified = p.get('modified', '?')
        try:
            dt = datetime.fromisoformat(modified.replace('Z', '+00:00'))
            old = dt < cutoff
        except:
            old = '?'
        print(f'ID:{p["id"]} modified:{modified} old:{old}')
