import requests, json, time, os, sys, re
from io import BytesIO
from PIL import Image

UA = 'AvesNativasChilenas/1.0 (https://avesnativaschilenas.cl)'
HEADERS = {'User-Agent': UA}
WP_AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
WP_BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'
SLEEP_WM = 1.2
SLEEP_WP = 2.5

IMAGE_DIR = r'C:\Users\Usuario\Desktop\multiplicar-dinero\images_temp'
os.makedirs(IMAGE_DIR, exist_ok=True)

TOPIC_MAP = {
    14119: ["pulga de paloma", "flea pigeon"],
    11836: ["gallina criolla chilena", "chicken creole breed"],
    11359: ["tipos de alimentacion aves", "bird eating seed fruit"],
    11353: ["dieta de las aves", "bird feeding wild"],
    9458: ["pichon de paloma nido", "baby pigeon nest"],
    9457: ["heces pichones paloma", "pigeon chick nest"],
    9456: ["dar comer pichon paloma", "feeding pigeon chick"],
    9452: ["mejor pajaro casa mascota", "pet bird cage"],
    9437: ["canario herido", "canary bird"],
    9426: ["pavo real mostrando cola", "peacock displaying feathers"],
    9421: ["pajaro en rama", "bird on branch"],
    9419: ["torcaza bebe", "eared dove bird"],
    9411: ["edad pichon paloma", "pigeon squab"],
    9410: ["comer pichon paloma", "pigeon feeding crop"],
    9404: ["paloma herida gato", "pigeon bird"],
    9403: ["pavo real extincion", "green peacock bird"],
    9395: ["pichon paloma cria", "pigeon chick"],
    9393: ["pavo real macho hembra", "peacock peahen"],
    9392: ["dar comer pichon mano", "hand feeding bird"],
    9385: ["pavo real peligro extincion", "green peacock"],
    9381: ["comedero aves botella reciclada", "bird feeder bottle"],
    9379: ["proteger animales extincion", "endangered bird"],
    9368: ["excremento aves liquido", "bird droppings"],
    9364: ["huevos pavo real nido", "peacock eggs nest"],
    9348: ["pajaro herido rescate", "injured bird rescue"],
    8655: ["jilguero carduelis", "goldfinch bird european"],
    7943: ["pajaros exoticos jaula", "parrot bird exotic"],
    7908: ["muda canarios plumaje", "canary molting"],
    7383: ["diamante mandarin", "zebra finch"],
    7023: ["pajaro hogar mascota", "parrot cage"],
    7014: ["pajaro herido primeros auxilios", "bird rescue injured"],
    6978: ["muda plumaje aves", "bird feather molting"],
    6537: ["enfermedades aves sintomas", "sick bird ill"],
    6535: ["proteger pajaros frio invierno", "bird winter cold"],
    6534: ["criar canarios cria", "canary bird nest eggs"],
    2286: ["curar pajaro herido gato", "bird injured cat"],
}

CC_LICENSES = {
    'cc-by', 'cc-by-sa', 'cc0', 'cc by', 'cc by-sa',
    'creative commons attribution', 'creative commons cc0',
    'cc-by-2.0', 'cc-by-3.0', 'cc-by-4.0',
    'cc-by-sa-2.0', 'cc-by-sa-3.0', 'cc-by-sa-4.0',
}

MIN_WIDTH = 600
MAX_FILE_SIZE = 3 * 1024 * 1024  # 3MB max to avoid 503


def is_image_file(title):
    ext = title.lower().rsplit('.', 1)[-1] if '.' in title else ''
    return ext in ('jpg', 'jpeg', 'png', 'gif', 'webp')


def is_cc_license(license_str):
    if not license_str:
        return False
    ls = license_str.lower().strip()
    for cc in CC_LICENSES:
        if cc in ls:
            return True
    return False


def search_wikimedia(query, limit=8):
    resp = requests.get(
        'https://commons.wikimedia.org/w/api.php',
        params={
            'action': 'query', 'format': 'json',
            'generator': 'search',
            'gsrnamespace': 6,
            'gsrsearch': query,
            'gsrlimit': limit,
            'prop': 'imageinfo',
            'iiprop': 'url|size|extmetadata',
            'iiurlwidth': 1200,
        },
        headers=HEADERS, timeout=30
    )
    if resp.status_code != 200:
        return []
    data = resp.json()
    pages = data.get('query', {}).get('pages', {})
    results = []
    for pid, page in pages.items():
        title = page.get('title', '')
        if not is_image_file(title):
            continue
        url = None
        license_ = None
        width = 0
        if 'imageinfo' in page:
            info = page['imageinfo'][0]
            url = info.get('url') or info.get('thumburl')
            width = info.get('width', 0) or 0
            if 'extmetadata' in info:
                if 'LicenseShortName' in info['extmetadata']:
                    license_ = info['extmetadata']['LicenseShortName']['value']
                elif 'License' in info['extmetadata']:
                    license_ = info['extmetadata']['License']['value']
        results.append({
            'title': title,
            'url': url,
            'license': license_,
            'width': width,
        })
    return results


def download_image(url):
    resp = requests.get(url, headers=HEADERS, timeout=60, stream=True)
    if resp.status_code == 200 and 'image' in resp.headers.get('content-type', ''):
        data = resp.content
        if len(data) > MAX_FILE_SIZE:
            print(f'    -> Archivo muy grande ({len(data)//1024}KB), saltando')
            return None
        return data
    return None


def resize_image(img_data, max_dim=1000):
    img = Image.open(BytesIO(img_data))
    w, h = img.size
    if w > max_dim or h > max_dim:
        ratio = min(max_dim / w, max_dim / h)
        img = img.resize((int(w * ratio), int(h * ratio)), Image.LANCZOS)
    buf = BytesIO()
    fmt = img.format or 'JPEG'
    if fmt.upper() in ('PNG', 'GIF', 'WEBP'):
        img.save(buf, format=fmt, optimize=True)
    else:
        if img.mode == 'RGBA':
            img = img.convert('RGB')
        img.save(buf, format='JPEG', quality=85, optimize=True)
    buf.seek(0)
    return buf.getvalue()


def upload_to_wordpress(image_data, filename, mime_type, post_id):
    files = {'file': (filename, BytesIO(image_data), mime_type)}
    headers = {'Content-Disposition': f'attachment; filename="{filename}"'}
    resp = requests.post(
        WP_BASE + 'media', auth=WP_AUTH, files=files,
        headers=headers, timeout=120
    )
    if resp.status_code == 201:
        media_id = resp.json()['id']
        r2 = requests.post(
            f'{WP_BASE}posts/{post_id}',
            auth=WP_AUTH, json={'featured_media': media_id}, timeout=30
        )
        if r2.status_code == 200:
            print(f'    OK -> Media {media_id} asignada a post {post_id}')
            return True
        else:
            print(f'    ERROR asignando featured_media: {r2.status_code}')
            return False
    else:
        print(f'    ERROR subiendo media: {resp.status_code}')
        if resp.text:
            print(f'    Detalle: {resp.text[:200]}')
        return False


def process_post(post_id, queries):
    print(f'\n{"="*60}')
    print(f'ID {post_id}')
    print(f'{"="*60}')

    for query in queries:
        print(f'  Buscando: "{query}"...')
        results = search_wikimedia(query)
        if not results:
            print(f'    Sin resultados')
            time.sleep(SLEEP_WM)
            continue

        for r in results:
            title = r['title']
            url = r['url']
            license_ = r['license']
            width = r['width']

            print(f'    Evaluando: {title[:70]}')
            if license_:
                print(f'    Licencia: {license_}')
            if license_ and not is_cc_license(license_):
                print(f'    -> No es CC, saltando')
                time.sleep(0.3)
                continue
            if width < MIN_WIDTH:
                print(f'    -> Muy pequena ({width}px), saltando')
                time.sleep(0.3)
                continue
            if not url:
                time.sleep(0.3)
                continue

            raw = download_image(url)
            if not raw:
                time.sleep(0.3)
                continue

            try:
                resized = resize_image(raw)
            except Exception as e:
                print(f'    Error redimensionando: {e}')
                time.sleep(0.3)
                continue

            ext = url.rsplit('.', 1)[-1].lower().split('?')[0]
            if ext not in ('jpg', 'jpeg', 'png', 'gif', 'webp'):
                ext = 'jpg'
            mime_map = {'jpg': 'image/jpeg', 'jpeg': 'image/jpeg',
                        'png': 'image/png', 'gif': 'image/gif', 'webp': 'image/webp'}
            mime_type = mime_map.get(ext, 'image/jpeg')

            safe_q = re.sub(r'[^a-z0-9]+', '-', query.lower())[:30].strip('-')
            filename = f'cuidados-{post_id}-{safe_q}.{ext}'

            local_path = os.path.join(IMAGE_DIR, filename)
            with open(local_path, 'wb') as f:
                f.write(resized)
            print(f'    Descargado+redimensionado: {local_path} ({len(resized)//1024}KB)')

            if upload_to_wordpress(resized, filename, mime_type, post_id):
                print(f'  LISTO ID {post_id} -> {title}')
                return True
            time.sleep(SLEEP_WP)

        time.sleep(SLEEP_WM)

    print(f'  No se pudo asignar imagen para ID {post_id}')
    return False


if __name__ == '__main__':
    ids_order = sorted(TOPIC_MAP.keys())

    results = []
    for pid in ids_order:
        try:
            ok = process_post(pid, TOPIC_MAP[pid])
            results.append((pid, 'OK' if ok else 'FAIL'))
        except Exception as e:
            print(f'  ERROR inesperado: {e}')
            results.append((pid, f'ERROR: {e}'))
        time.sleep(SLEEP_WP)

    print(f'\n\n{"="*60}')
    print('RESUMEN FINAL')
    print(f'{"="*60}')
    for pid, status in results:
        print(f'  ID {pid}: {status}')
