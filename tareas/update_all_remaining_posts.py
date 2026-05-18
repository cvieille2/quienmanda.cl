import json
import os
import re
import subprocess
import tempfile
import time
from urllib.parse import urlparse

AUTH = 'cvieille:QCZg BNQs mcnf HMXE OvBm MXpz'
BASE = 'https://infomoteles.cl/wp-json/wp/v2'
DELAY_SECONDS = 1.0


CITY_MAP = {
    'antofagasta': 'Antofagasta', 'arica': 'Arica', 'calama': 'Calama',
    'copiapo': 'Copiapó', 'coquimbo': 'Coquimbo', 'talca': 'Talca',
    'curico': 'Curicó', 'concepcion': 'Concepción',
    'valparaiso': 'Valparaíso', 'vina-del-mar': 'Viña del Mar',
    'santiago': 'Santiago', 'maipu': 'Maipú', 'valdivia': 'Valdivia',
    'osorno': 'Osorno', 'temuco': 'Temuco', 'iquique': 'Iquique',
    'punta-arenas': 'Punta Arenas', 'puerto-montt': 'Puerto Montt',
    'rancagua': 'Rancagua', 'quinta-normal': 'Quinta Normal',
    'recoleta': 'Recoleta', 'la-cisterna': 'La Cisterna',
    'la-florida': 'La Florida', 'san-bernardo': 'San Bernardo',
    'san-miguel': 'San Miguel', 'providencia': 'Providencia',
    'la-reina': 'La Reina', 'melipilla': 'Melipilla',
    'lo-barnechea': 'Lo Barnechea', 'san-joaquin': 'San Joaquín',
    'lampa': 'Lampa', 'colina': 'Colina', 'buin': 'Buin',
    'villa-alemana': 'Villa Alemana', 'coronel': 'Coronel',
    'los-angeles': 'Los Ángeles', 'nunoa': 'Ñuñoa',
    'penalolen': 'Peñalolén', 'pudahuel': 'Pudahuel',
    'san-vicente': 'San Vicente', 'talcahuano': 'Talcahuano',
    'tome': 'Tomé', 'maule': 'Maule', 'el-monte': 'El Monte',
    'conchali': 'Conchalí', 'huechuraba': 'Huechuraba',
    'san-ramon': 'San Ramón', 'la-serena': 'La Serena',
    'san-antonio': 'San Antonio', 'victoria': 'Victoria',
    'san-felipe': 'San Felipe', 'los-andes': 'Los Andes',
    'quillota': 'Quillota', 'limache': 'Limache', 'pirque': 'Pirque',
    'isla-de-maipo': 'Isla de Maipo', 'talagante': 'Talagante',
    'penaflor': 'Peñaflor', 'colbun': 'Colbún', 'linares': 'Linares',
    'parral': 'Parral', 'constitucion': 'Constitución', 'cauquenes': 'Cauquenes',
    'chanco': 'Chanco', 'san-fernando': 'San Fernando', 'chimbarongo': 'Chimbarongo',
    'santa-cruz': 'Santa Cruz', 'santo-domingo': 'Santo Domingo',
    'quilpue': 'Quilpué', 'macul': 'Macul', 'padre-hurtado': 'Padre Hurtado',
}


def run(cmd):
    return subprocess.run(cmd, shell=True, capture_output=True, text=True, timeout=45)


def get_post(pid):
    cmd = f'curl -s "{BASE}/posts/{pid}?_fields=id,title,content,slug,link,modified" -u "{AUTH}"'
    r = run(cmd)
    return json.loads(r.stdout)


def put_post(pid, data):
    tmp = tempfile.NamedTemporaryFile(mode='w', suffix='.json', delete=False, encoding='utf-8')
    json.dump(data, tmp, ensure_ascii=False)
    tmp.close()
    cmd = f'curl -s -X PUT "{BASE}/posts/{pid}" -u "{AUTH}" -H "Content-Type: application/json" -d @{tmp.name}'
    r = run(cmd)
    os.unlink(tmp.name)
    return json.loads(r.stdout)


def city_from_slug(slug):
    for key, value in CITY_MAP.items():
        if key in slug:
            return value
    return 'Chile'


def clean_name(title):
    name = title.replace('Motel ', '').replace('Hotel ', '').strip()
    name = name.split(',')[0].strip()
    name = re.sub(r'\s+', ' ', name)
    return name


def extract_phone(content):
    m = re.search(r'\+56[\s\d\-]{7,15}', content)
    return m.group(0) if m else ''


def build_top(name, city, phone):
    return (
        f'<p><strong>{name}</strong> es un motel en <strong>{city}</strong>. Información actualizada: teléfono, dirección, horarios y reseñas.</p>\n'
        f'<h2>Información de {name} en {city}</h2>\n'
        f'<p><strong>Teléfono:</strong> {phone if phone else "Disponible bajo solicitud"}</p>\n'
        '<p><strong>Horario:</strong> Abierto 24 horas.</p>\n'
        '<h2>Servicios</h2>\n'
        '<p>Habitaciones privadas con estacionamiento y opciones para parejas.</p>\n'
        '<h2>Precios</h2>\n'
        '<p>Los precios varían según habitación y horario. Contacta directamente para tarifas actualizadas.</p>\n'
        '<hr />\n'
        '<!-- OLD CONTENT PRESERVED BELOW -->\n'
    )


def needs_update(content):
    return 'Información de' not in content or 'Servicios' not in content or 'OLD CONTENT PRESERVED' not in content


with open('tareas/infomoteles-posts.json', encoding='utf-8') as f:
    posts = json.load(f)

targets = []
for p in posts:
    content = p.get('content', '')
    if not content:
        # minimal records from previous export require fetch later
        targets.append(p)
        continue
    if needs_update(content):
        targets.append(p)

print(f'Total posts in export: {len(posts)}')
print(f'Targets to update: {len(targets)}')

updated = 0
skipped = 0
failed = 0

for i, p in enumerate(targets, start=1):
    try:
        full = get_post(p['id'])
        content = full['content']['rendered']
        if not needs_update(content):
            skipped += 1
            continue
        city = city_from_slug(full['slug'])
        name = clean_name(full['title']['rendered'])
        phone = extract_phone(content)
        top = build_top(name, city, phone)
        payload = {
            'title': f'{name} en {city} | Teléfono y Precios',
            'content': top + content,
        }
        result = put_post(full['id'], payload)
        if result and 'id' in result:
            updated += 1
            print(f'[{i}/{len(targets)}] OK {full["id"]} {full["slug"]}')
        else:
            failed += 1
            print(f'[{i}/{len(targets)}] FAIL {full["id"]} {full["slug"]}')
        time.sleep(DELAY_SECONDS)
    except Exception as e:
        failed += 1
        print(f'[{i}/{len(targets)}] ERROR {p.get("id", "?")} {e}')
        time.sleep(DELAY_SECONDS)

print(f'Done. updated={updated}, skipped={skipped}, failed={failed}')
