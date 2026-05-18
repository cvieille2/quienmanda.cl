import json
import os
import re
import subprocess
import tempfile
import time

AUTH = 'cvieille:QCZg BNQs mcnf HMXE OvBm MXpz'
BASE = 'https://infomoteles.cl/wp-json/wp/v2'

IDS = [2221, 2214, 2205, 2200, 2182, 2165, 2951, 2938, 2958, 3030]
CITY_MAP = {
    'la-florida': 'La Florida', 'santiago': 'Santiago', 'coquimbo': 'Coquimbo',
    'providencia': 'Providencia', 'macul': 'Macul', 'la-cisterna': 'La Cisterna',
    'osorno': 'Osorno', 'puerto-montt': 'Puerto Montt', 'valparaiso': 'Valparaíso',
    'calama': 'Calama', 'arica': 'Arica', 'curico': 'Curicó', 'concepcion': 'Concepción',
    'quinta-normal': 'Quinta Normal', 'los-angeles': 'Los Ángeles', 'valdivia': 'Valdivia',
    'lampa': 'Lampa', 'maule': 'Maule', 'copiapo': 'Copiapó', 'recoleta': 'Recoleta'
}


def run(cmd):
    return subprocess.run(cmd, shell=True, capture_output=True, text=True, timeout=40)


def get_post(pid):
    cmd = 'curl -s "{}/posts/{}?_fields=id,title,content,slug,link,modified" -u "{}"'.format(BASE, pid, AUTH)
    return json.loads(run(cmd).stdout)


def put_post(pid, data):
    tmp = tempfile.NamedTemporaryFile(mode='w', suffix='.json', delete=False, encoding='utf-8')
    json.dump(data, tmp, ensure_ascii=False)
    tmp.close()
    cmd = 'curl -s -X PUT "{}/posts/{}" -u "{}" -H "Content-Type: application/json" -d @{}'.format(BASE, pid, AUTH, tmp.name)
    out = run(cmd).stdout
    os.unlink(tmp.name)
    return json.loads(out)


for pid in IDS:
    post = get_post(pid)
    slug = post.get('slug', '')
    city = 'Chile'
    for key, value in CITY_MAP.items():
        if key in slug:
            city = value
            break
    title = post['title']['rendered']
    name = title.replace('Motel ', '').replace('Hotel ', '').strip().split(',')[0].strip()
    content = post['content']['rendered']
    match = re.search(r'\+56[\s\d\-]{7,15}', content)
    phone = match.group(0) if match else ''

    top = (
        '<p><strong>{}</strong> es un motel en <strong>{}</strong>. Información actualizada: teléfono, dirección, horarios y reseñas.</p>\n'
        '<h2>Información de {} en {}</h2>\n'
        '<p><strong>Teléfono:</strong> {}</p>\n'
        '<p><strong>Horario:</strong> Abierto 24 horas.</p>\n'
        '<h2>Servicios</h2>\n'
        '<p>Habitaciones privadas con estacionamiento y opciones para parejas.</p>\n'
        '<h2>Precios</h2>\n'
        '<p>Los precios varían según habitación y horario. Contacta directamente para tarifas actualizadas.</p>\n'
        '<hr />\n<!-- OLD CONTENT PRESERVED BELOW -->\n'
    ).format(name, city, name, city, phone if phone else 'Disponible bajo solicitud')

    updated = put_post(pid, {
        'title': '{} en {} | Teléfono y Precios'.format(name, city),
        'content': top + content,
    })
    print(pid, updated.get('modified', '')[:19], 'Información de' in updated.get('content', {}).get('rendered', ''))
    time.sleep(1.5)
