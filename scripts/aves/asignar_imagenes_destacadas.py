import requests, json, time, os, sys
from io import BytesIO
from PIL import Image

UA = 'AvesNativasChilenas/1.0 (https://avesnativaschilenas.cl)'
HEADERS = {'User-Agent': UA}
WP_AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
WP_BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'
SLEEP = 1.5
MAX_DIM = 1200

SEARCH_QUERIES = {
    14225: ["Phaethon rubricauda", "Sula dactylatra", "Rapa Nui bird"],
    14221: ["Sephanoides sephaniodes", "Colibri picaflor", "Green-backed firecrown"],
    11820: ["Bandurria Theristicus caudatus", "Buff-necked ibis", "Bandurria aliblanca"],
    762: ["Pinguino de Humboldt", "Spheniscus humboldti", "Humboldt penguin Chile"],
    14226: ["Gaviota dominicana Larus dominicanus", "Kelp gull Chile", "Gaviota chilena"],
    14167: ["Sulidae booby", "Piquero Sula variegata", "Peruvian booby"],
    14142: ["Piquero chileno Sula variegata", "Peruvian Booby", "Piquero Peruano"],
    14126: ["Saffron finch Sicalis flaveola", "Chirihue dorado"],
    14125: ["Hooded siskin Spinus magellanicus", "Cabecitanegra"],
    14124: ["Spinus barbatus", "Black-chinned siskin Chile", "Chirihue"],
    14123: ["Sephanoides fernandensis", "Juan Fernandez firecrown", "Picaflor Juan Fernandez"],
    14122: ["Long-tailed pheasant", "Faisan cola larga", "Lophura"],
    14121: ["Avestruz Struthio camelus", "Ostrich common ostrich"],
    14120: ["Silver pheasant Lophura nycthemera", "Faisan plateado", "Ring-necked pheasant"],
    11373: ["Great-tailed grackle Quiscalus mexicanus", "Zanate cola grande"],
    11222: ["Birds flying sky", "Flock of birds flying", "Bandada aves cielo"],
    11111: ["Aves Chile nativas", "Chilean native birds"],
}


def is_image_file(title):
    ext = title.lower().rsplit('.', 1)[-1] if '.' in title else ''
    return ext in ('jpg', 'jpeg', 'png', 'gif', 'webp', 'tif', 'tiff')


def search_wikimedia(query, limit=5):
    resp = requests.get(
        'https://commons.wikimedia.org/w/api.php',
        params={
            'action': 'query', 'format': 'json', 'list': 'search',
            'srsearch': query, 'srnamespace': 6, 'srlimit': limit, 'srprop': 'size'
        },
        headers=HEADERS, timeout=20
    )
    if resp.status_code != 200:
        return []
    data = resp.json()
    return [r['title'] for r in data.get('query', {}).get('search', []) if 'File:' in r['title'] and is_image_file(r['title'])]


def get_image_url(file_title):
    resp = requests.get(
        'https://commons.wikimedia.org/w/api.php',
        params={
            'action': 'query', 'format': 'json', 'titles': file_title,
            'prop': 'imageinfo', 'iiprop': 'url|size|extmetadata', 'iiurlwidth': 1920
        },
        headers=HEADERS, timeout=20
    )
    if resp.status_code != 200:
        return None, None
    data = resp.json()
    pages = data.get('query', {}).get('pages', {})
    for pid, page in pages.items():
        if 'imageinfo' in page:
            info = page['imageinfo'][0]
            url = info.get('url') or info.get('thumburl')
            license_ = ''
            if 'extmetadata' in info and 'LicenseShortName' in info['extmetadata']:
                license_ = info['extmetadata']['LicenseShortName']['value']
            return url, license_
    return None, None


def resize_image(img_data, max_dim=MAX_DIM):
    img = Image.open(img_data)
    w, h = img.size
    if w > max_dim or h > max_dim:
        ratio = min(max_dim / w, max_dim / h)
        img = img.resize((int(w * ratio), int(h * ratio)), Image.LANCZOS)
    buf = BytesIO()
    fmt = img.format or 'JPEG'
    if fmt.upper() == 'PNG':
        img.save(buf, format='PNG', optimize=True)
    elif fmt.upper() in ('GIF', 'WEBP'):
        img.save(buf, format=fmt, optimize=True)
    else:
        if img.mode == 'RGBA':
            img = img.convert('RGB')
        img.save(buf, format='JPEG', quality_=85, optimize=True)
    buf.seek(0)
    return buf, img.size


def upload_to_wordpress(image_data, filename, post_id, is_page=False):
    # Detect content type from filename
    ext = filename.rsplit('.', 1)[-1].lower()
    ctype_map = {'jpg': 'image/jpeg', 'jpeg': 'image/jpeg', 'png': 'image/png',
                 'gif': 'image/gif', 'webp': 'image/webp'}
    ctype = ctype_map.get(ext, 'image/jpeg')

    files = {'file': (filename, image_data, ctype)}
    headers = {'Content-Disposition': f'attachment; filename="{filename}"'}
    resp = requests.post(
        WP_BASE + 'media', auth=WP_AUTH, files=files,
        headers=headers, timeout=120
    )
    if resp.status_code == 201:
        media_id = resp.json()['id']
        ptype = 'pages' if is_page else 'posts'
        r2 = requests.post(
            f'{WP_BASE}{ptype}/{post_id}',
            auth=WP_AUTH, json={'featured_media': media_id}, timeout=30
        )
        if r2.status_code == 200:
            print(f'    OK -> Media {media_id} asignada a {ptype[:-1]} {post_id}')
            return True
        else:
            print(f'    ERROR asignando: {r2.status_code}')
            return False
    else:
        print(f'    ERROR subiendo: {resp.status_code}')
        if resp.text:
            print(f'    Detalle: {resp.text[:200]}')
        return False


def process_post(post_id, title, ptype, queries):
    print(f'\n{"="*60}')
    print(f'ID {post_id} [{ptype}]: {title[:60]}')
    print(f'{"="*60}')

    is_page = ptype == 'pages'

    for query in queries:
        print(f'  Buscando: "{query}"...')
        files = search_wikimedia(query)
        if not files:
            print(f'    Sin resultados')
            time.sleep(SLEEP)
            continue

        for file_title in files[:4]:
            print(f'    Evaluando: {file_title[:70]}')
            url, license_ = get_image_url(file_title)
            if not url:
                time.sleep(0.5)
                continue
            if license_:
                print(f'    Licencia: {license_}')

            raw = download_image(url)
            if not raw:
                print(f'    Error descargando')
                time.sleep(0.5)
                continue

            try:
                resized, (w, h) = resize_image(raw)
                print(f'    Dimensiones: {w}x{h}')
            except Exception as e:
                print(f'    Error redimensionando: {e}')
                continue

            ext = url.rsplit('.', 1)[-1].lower().split('?')[0]
            safe_q = query.replace(' ', '-')[:30]
            filename = f'aves-chilenas-{post_id}-{safe_q}.{ext}'

            if upload_to_wordpress(resized, filename, post_id, is_page):
                print(f'  LISTO ID {post_id}')
                return True
            time.sleep(SLEEP)

        time.sleep(SLEEP)

    print(f'  No se pudo asignar imagen para ID {post_id}')
    return False


def download_image(url):
    resp = requests.get(url, headers=HEADERS, timeout=60, stream=True)
    if resp.status_code == 200 and 'image' in resp.headers.get('content-type', ''):
        return BytesIO(resp.content)
    return None


if __name__ == '__main__':
    ids_order = [14225, 14221, 11820, 762, 14226, 14167, 14142,
                 14126, 14125, 14124, 14123, 14122, 14121, 14120,
                 11373, 11222, 11111]

    titles = {
        14225: "Aves de Isla de Pascua (Rapa Nui)",
        14221: "Significado espiritual del colibri",
        11820: "Bandurria", 762: "Pinguinos Chilenos",
        14226: "Gaviotas en Chile", 14167: "Familia Sulidae",
        14142: "Piqueros en Chile", 14126: "Chirihue Dorado",
        14125: "Chirihue Verdoso", 14124: "Chirihue",
        14123: "Picaflor de Juan Fernandez", 14122: "Ave Asiatica Galliforme Sin Cresta",
        14121: "El Ave Mas Grande del Mundo - Avestruz", 14120: "Ave Asiatica del Tamano de un Gallo",
        11373: "Zanates parvadas", 11222: "Aves en la Biblia",
        11111: "Familia de Aves en Chile",
    }
    ptypes = {
        14225: 'posts', 14221: 'posts', 11820: 'posts', 762: 'posts',
        14226: 'pages', 14167: 'pages', 14142: 'pages',
        14126: 'pages', 14125: 'pages', 14124: 'pages', 14123: 'pages',
        14122: 'pages', 14121: 'pages', 14120: 'pages',
        11373: 'pages', 11222: 'pages', 11111: 'pages',
    }

    results = []
    for pid in ids_order:
        try:
            ok = process_post(pid, titles[pid], ptypes[pid], SEARCH_QUERIES[pid])
            results.append((pid, titles[pid], 'OK' if ok else 'FAIL'))
        except Exception as e:
            print(f'  ERROR inesperado: {e}')
            results.append((pid, titles[pid], f'ERROR: {e}'))
        time.sleep(SLEEP * 2)

    print(f'\n\n{"="*60}')
    print('RESUMEN FINAL')
    print(f'{"="*60}')
    for pid, title, status in results:
        print(f'  ID {pid}: {title[:50]} -> {status}')
