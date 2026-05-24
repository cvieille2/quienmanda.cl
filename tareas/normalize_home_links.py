"""
normalize_home_links.py

Normaliza los enlaces internos a la homepage de avesnativaschilenas.cl
para que siempre terminen en slash.
"""

import re
import time

import requests

AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2'
HOME_CANONICAL = 'https://avesnativaschilenas.cl/'
HOME_RE = re.compile(r'https?://(?:www\.)?avesnativaschilenas\.cl(?=["\'\)\s#])', re.I)


def fetch_all(endpoint):
    items = []
    page = 1
    while True:
        r = requests.get(
            f'{BASE}/{endpoint}',
            params={'per_page': 100, 'page': page, 'status': 'publish', '_fields': 'id,content'},
            auth=AUTH,
            timeout=30,
        )
        if r.status_code != 200:
            print(f'ERR {endpoint} page {page}: {r.status_code} {r.text[:200]}')
            break
        batch = r.json()
        if not batch:
            break
        items.extend(batch)
        if len(batch) < 100:
            break
        page += 1
    return items


def normalize(text):
    return HOME_RE.sub(HOME_CANONICAL, text)


def update_item(endpoint, item_id, content):
    return requests.post(
        f'{BASE}/{endpoint}/{item_id}',
        auth=AUTH,
        json={'content': content},
        timeout=30,
    )


def main():
    total = 0
    updated = 0
    for endpoint in ('posts', 'pages'):
        items = fetch_all(endpoint)
        print(f'{endpoint}: {len(items)} items')
        for item in items:
            total += 1
            content = item.get('content', {}).get('rendered', '') or ''
            new_content = normalize(content)
            if new_content == content:
                continue
            resp = update_item(endpoint, item['id'], new_content)
            if resp.status_code == 200:
                updated += 1
                print(f'OK {endpoint}/{item["id"]}')
            else:
                print(f'UPDATE ERR {endpoint}/{item["id"]}: {resp.status_code} {resp.text[:200]}')
            time.sleep(0.1)
    print(f'Total revisados: {total}')
    print(f'Actualizados: {updated}')


if __name__ == '__main__':
    main()
