"""
update_fardela_page.py

Refuerza la pagina /fardela/ con anchors mas semanticos y una
variedad editorial mas util para posicionar.
"""

import re
import requests

AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2'
PAGE_ID = 11399

HOME_LINK = '<a href="https://avesnativaschilenas.cl/">el portal principal de aves nativas chilenas</a>'

ANCHOR_MAP = {
    'https://avesnativaschilenas.cl/fardela/fardela-de-phoenix/': 'Fardela de Phoenix: petrel blanquinegro y conservación',
    'https://avesnativaschilenas.cl/fardela/fardela-heraldica/': 'Fardela heráldica: petrel tricolor de Rapa Nui',
    'https://avesnativaschilenas.cl/fardela/fardela-de-pascua/': 'Fardela de Pascua o Kima de Rapa Nui',
    'https://avesnativaschilenas.cl/fardela/fardela-negra-de-juan-fernandez/': 'Fardela negra de Juan Fernández: hábitat y conservación',
    'https://avesnativaschilenas.cl/fardela/fardela-blanca-de-juan-fernandez/': 'Fardela blanca de Juan Fernández: especie oceánica endémica',
    'https://avesnativaschilenas.cl/fardela/fardela-tropical/': 'Fardela tropical: características, distribución y curiosidades',
    'https://avesnativaschilenas.cl/fardela/fardela-negra-grande/': 'Fardela negra grande: guía de identificación',
    'https://avesnativaschilenas.cl/fardela/fardela-negra-de-patas-palidas/': 'Fardela negra de patas pálidas: rasgos clave de identificación',
    'https://avesnativaschilenas.cl/fardela/fardela-chica/': 'Fardela chica: hábitat, comportamiento y rasgos distintivos',
    'https://avesnativaschilenas.cl/fardela/fardela-capirotada/': 'Fardela capirotada: ficha completa de la especie',
}


def add_home_link(content):
    if re.search(r'href="https://avesnativaschilenas\.cl/?"', content):
        return content
    insert = f'<p>Si quieres volver al panorama general, visita {HOME_LINK}.</p>'
    marker = '<h2 class="wp-block-heading">Tipos de fardela en Chile</h2>'
    if marker in content:
        return content.replace(marker, insert + '\n\n' + marker, 1)
    return insert + '\n\n' + content


def replace_cluster_anchors(content):
    updated = content
    for url, anchor in ANCHOR_MAP.items():
        updated = re.sub(
            rf'(<a href="{re.escape(url)}"[^>]*>.*?<span class="entry-title">)(.*?)(</span></a>)',
            rf'\1{anchor}\3',
            updated,
            flags=re.DOTALL,
        )
    return updated


def main():
    resp = requests.get(f'{BASE}/pages/{PAGE_ID}', auth=AUTH, timeout=30)
    resp.raise_for_status()
    page = resp.json()
    title = page['title']['rendered']
    content = page['content']['rendered'] or ''

    new_content = add_home_link(content)
    new_content = replace_cluster_anchors(new_content)

    if new_content == content:
        print('No hay cambios para aplicar.')
        return

    update = requests.post(
        f'{BASE}/pages/{PAGE_ID}',
        auth=AUTH,
        json={'content': new_content},
        timeout=30,
    )
    print(f'Update status: {update.status_code}')
    if update.status_code == 200:
        data = update.json()
        print('OK:', data.get('link', ''))
        print('Titulo:', title)
        print('Anchors actualizados:', len(ANCHOR_MAP))
    else:
        print(update.text[:500])


if __name__ == '__main__':
    main()
