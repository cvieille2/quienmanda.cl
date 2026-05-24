"""
rewrite_menu_aves.py

Reestructura el menu principal de avesnativaschilenas.cl para mezclar
contenido informativo y comercial.
"""

import requests

AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2'
MENU_ID = 79


def create_item(title, url, parent=0, order=1):
    payload = {
        'title': title,
        'url': url,
        'status': 'publish',
        'menus': MENU_ID,
        'parent': parent,
        'menu_order': order,
    }
    return requests.post(f'{BASE}/menu-items', auth=AUTH, json=payload, timeout=30)


def update_item(item_id, **fields):
    payload = {'menus': MENU_ID}
    payload.update(fields)
    return requests.put(f'{BASE}/menu-items/{item_id}', auth=AUTH, json=payload, timeout=30)


def delete_item(item_id):
    return requests.delete(f'{BASE}/menu-items/{item_id}?force=true', auth=AUTH, timeout=30)


def main():
    print('Reestructurando menu de avesnativaschilenas.cl')

    parent_map = {}

    for title, url, order in [
        ('Recomendados', '#', 2),
        ('Comparativas', '#', 3),
        ('Nosotros', '#', 5),
    ]:
        r = create_item(title, url, parent=0, order=order)
        if r.status_code in (200, 201):
            parent_map[title] = r.json()['id']
            print(f'OK create parent {title}: {parent_map[title]}')
        else:
            print(f'ERR create parent {title}: {r.status_code} {r.text[:200]}')
            return

    updates = [
        (1681, {'title': 'Guias', 'url': '#', 'parent': 0, 'menu_order': 1}),
        (8309, {'title': 'Consejos', 'url': 'https://avesnativaschilenas.cl/ciudados/', 'parent': 0, 'menu_order': 4}),
        (1416, {'title': 'Contacto', 'url': 'https://avesnativaschilenas.cl/contacto/', 'parent': 0, 'menu_order': 6}),
        (1404, {'title': 'Politica de cookies', 'parent': parent_map['Nosotros'], 'menu_order': 1}),
        (1408, {'title': 'Politica de privacidad', 'parent': parent_map['Nosotros'], 'menu_order': 2}),
        (1412, {'title': 'Aviso legal', 'parent': parent_map['Nosotros'], 'menu_order': 3}),
    ]

    for item_id, fields in updates:
        r = update_item(item_id, **fields)
        if r.status_code not in (200, 201):
            print(f'ERR update {item_id}: {r.status_code} {r.text[:200]}')
            return
        print(f"OK update {item_id}: {fields.get('title', item_id)}")

    for item_id, parent, order in [
        (6492, parent_map['Recomendados'], 1),
        (7154, parent_map['Recomendados'], 2),
        (7126, parent_map['Recomendados'], 3),
        (2194, parent_map['Recomendados'], 4),
    ]:
        r = update_item(item_id, parent=parent, menu_order=order)
        if r.status_code not in (200, 201):
            print(f'ERR recommended child {item_id}: {r.status_code} {r.text[:200]}')
            return

    for item_id, parent, order in [
        (9420, parent_map['Comparativas'], 1),
        (14671, parent_map['Comparativas'], 2),
        (14663, parent_map['Comparativas'], 3),
        (14670, parent_map['Comparativas'], 4),
        (14661, parent_map['Comparativas'], 5),
        (9393, parent_map['Comparativas'], 6),
    ]:
        r = update_item(item_id, parent=parent, menu_order=order)
        if r.status_code not in (200, 201):
            print(f'ERR comparison child {item_id}: {r.status_code} {r.text[:200]}')
            return

    for item_id, parent, order in [
        (9458, 8309, 1),
        (9454, 8309, 2),
        (9366, 8309, 3),
        (9370, 8309, 4),
        (9393, 8309, 5),
    ]:
        r = update_item(item_id, parent=parent, menu_order=order)
        if r.status_code not in (200, 201):
            print(f'ERR advice child {item_id}: {r.status_code} {r.text[:200]}')
            return

    for item_id, parent, order in [
        (1771, 1681, 1),
        (2650, 1681, 2),
        (1743, 1681, 3),
        (1682, 1681, 4),
        (2657, 1681, 5),
        (6298, 1681, 6),
        (9830, 2657, 7),
    ]:
        r = update_item(item_id, parent=parent, menu_order=order)
        if r.status_code not in (200, 201):
            print(f'ERR guide child {item_id}: {r.status_code} {r.text[:200]}')
            return

    for item_id in [1401, 14832]:
        r = delete_item(item_id)
        if r.status_code not in (200, 201):
            print(f'ERR delete {item_id}: {r.status_code} {r.text[:200]}')
            return
        print(f'OK delete {item_id}')

    print('Menu reestructurado correctamente')


if __name__ == '__main__':
    main()
