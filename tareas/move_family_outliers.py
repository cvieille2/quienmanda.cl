"""
move_family_outliers.py

Mueve posts que quedaron mal dentro de la categoria `familia`
hacia su categoria correcta en avesnativaschilenas.cl.
"""

import requests

AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2'

TARGETS = {
    14118: 15,   # tordo -> mirlo
    11824: 58,   # chercan -> churrin
    11823: 111,  # pavo real -> terrestres
    6949: 58,    # troglodytes aedon -> churrin
    6939: 64,    # sonidos aves de chile -> blog
    6833: 344,   # phrygilus patagonicus -> jilguero
    6801: 64,    # pajaro pecho rojo chile -> blog
    6621: 64,    # cantos de aves chilenas -> blog
    6602: 64,    # aves de pecho rojo -> blog
    3551: 64,    # tipos de excrementos de aves -> blog
    559: 64,     # aves mas coloridas de chile -> blog
}


def main():
    print('Mueve posts fuera de familia')
    updated = 0
    for post_id, target_cat in TARGETS.items():
        resp = requests.get(f'{BASE}/posts/{post_id}', auth=AUTH, timeout=30)
        if resp.status_code != 200:
            print(f'ERROR GET {post_id}: {resp.status_code}')
            continue
        post = resp.json()
        current = post.get('categories', [])
        title = post.get('title', {}).get('rendered', '')
        if current == [target_cat]:
            print(f'SKIP {post_id}: ya esta en destino -> {title}')
            continue

        update = requests.post(
            f'{BASE}/posts/{post_id}',
            auth=AUTH,
            json={'categories': [target_cat]},
            timeout=30,
        )
        if update.status_code == 200:
            updated += 1
            print(f'OK {post_id}: {title} -> cat {target_cat}')
        else:
            print(f'ERROR {post_id}: {update.status_code} {update.text[:200]}')

    print(f'Actualizados: {updated}')


if __name__ == '__main__':
    main()
